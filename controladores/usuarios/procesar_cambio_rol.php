<?php
/**
 * Procesar Cambio de Rol de Usuario
 * Script backend para que el superuser pueda cambiar roles de otros usuarios
 */

session_start();

// Verificar que el usuario esté logueado y sea superuser
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superuser') {
    $_SESSION['mensaje'] = 'Solo los super administradores pueden cambiar roles de usuarios';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = 'Método de solicitud no válido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_roles_usuarios.php');
    exit;
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $_SESSION['mensaje'] = 'Token de seguridad inválido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_roles_usuarios.php');
    exit;
}

require_once '../db/connection.php';

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Función para registrar actividad de cambio de rol
function registrarCambioRol($pdo, $superuserId, $usuarioAfectado, $rolAnterior, $rolNuevo, $motivo = '') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO historial_cambios_roles (
                superuser_id, 
                usuario_afectado_id, 
                rol_anterior, 
                rol_nuevo, 
                motivo, 
                fecha_cambio,
                ip_address
            ) VALUES (?, ?, ?, ?, ?, NOW(), ?)
        ");
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([
            $superuserId,
            $usuarioAfectado,
            $rolAnterior,
            $rolNuevo,
            $motivo,
            $ip
        ]);
        
        return true;
    } catch (Exception $e) {
        // Si la tabla no existe, crearla
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS historial_cambios_roles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    superuser_id INT NOT NULL,
                    usuario_afectado_id INT NOT NULL,
                    rol_anterior VARCHAR(50) NOT NULL,
                    rol_nuevo VARCHAR(50) NOT NULL,
                    motivo TEXT,
                    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    ip_address VARCHAR(45),
                    INDEX idx_usuario_afectado (usuario_afectado_id),
                    INDEX idx_superuser (superuser_id),
                    INDEX idx_fecha (fecha_cambio)
                )
            ");
            
            // Intentar registrar nuevamente
            $stmt = $pdo->prepare("
                INSERT INTO historial_cambios_roles (
                    superuser_id, 
                    usuario_afectado_id, 
                    rol_anterior, 
                    rol_nuevo, 
                    motivo, 
                    fecha_cambio,
                    ip_address
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?)
            ");
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->execute([
                $superuserId,
                $usuarioAfectado,
                $rolAnterior,
                $rolNuevo,
                $motivo,
                $ip
            ]);
            
            return true;
        } catch (Exception $e2) {
            error_log("Error al registrar cambio de rol: " . $e2->getMessage());
            return false;
        }
    }
}

// Obtener y validar datos del formulario
$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$nuevoRol = trim($_POST['nuevo_rol'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');

// Validaciones básicas
if (!$userId) {
    $_SESSION['mensaje'] = 'ID de usuario inválido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_roles_usuarios.php');
    exit;
}

if (empty($nuevoRol)) {
    $_SESSION['mensaje'] = 'Debe especificar el nuevo rol';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_roles_usuarios.php');
    exit;
}

// Roles válidos
$rolesValidos = ['superuser', 'admin', 'usuario', 'editor', 'viewer', 'consulta'];
if (!in_array($nuevoRol, $rolesValidos)) {
    $_SESSION['mensaje'] = 'Rol especificado no es válido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_roles_usuarios.php');
    exit;
}

try {
    // Verificar que el usuario a modificar existe y no es el mismo superuser
    $stmt = $pdo->prepare("SELECT id, username, email, nombre, apellido, role FROM usuarios WHERE id = ? AND id != ?");
    $stmt->execute([$userId, $_SESSION['usuario_id']]);
    $usuarioAfectado = $stmt->fetch();
    
    if (!$usuarioAfectado) {
        $_SESSION['mensaje'] = 'Usuario no encontrado o no se puede modificar a sí mismo';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: gestionar_roles_usuarios.php');
        exit;
    }
    
    $rolAnterior = $usuarioAfectado['role'];
    
    // Verificar que el rol sea diferente al actual
    if ($rolAnterior === $nuevoRol) {
        $_SESSION['mensaje'] = 'El usuario ya tiene el rol especificado';
        $_SESSION['tipo_mensaje'] = 'warning';
        header('Location: gestionar_roles_usuarios.php');
        exit;
    }
    
    // Validación especial: no permitir crear otro superuser sin confirmación explícita
    if ($nuevoRol === 'superuser') {
        $confirmacion = $_POST['confirmar_superuser'] ?? '';
        if ($confirmacion !== 'SI_CONFIRMO') {
            $_SESSION['mensaje'] = 'Para asignar el rol de Super Administrador se requiere confirmación explícita';
            $_SESSION['tipo_mensaje'] = 'warning';
            header('Location: gestionar_roles_usuarios.php');
            exit;
        }
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Actualizar el rol del usuario
    $stmt = $pdo->prepare("UPDATE usuarios SET role = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$nuevoRol, $userId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo actualizar el rol del usuario');
    }
    
    // Registrar el cambio en el historial
    $motivoCompleto = empty($motivo) 
        ? "Cambio de rol realizado por superuser" 
        : "Cambio de rol: " . $motivo;
    
    registrarCambioRol(
        $pdo, 
        $_SESSION['usuario_id'], 
        $userId, 
        $rolAnterior, 
        $nuevoRol, 
        $motivoCompleto
    );
    
    // Confirmar transacción
    $pdo->commit();
    
    // Obtener información del superuser para el mensaje
    $stmt = $pdo->prepare("SELECT username FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $superuser = $stmt->fetch();
    
    // Mensaje de éxito detallado
    $nombreCompleto = $usuarioAfectado['nombre'] . ' ' . $usuarioAfectado['apellido'];
    $mensaje = sprintf(
        'Rol actualizado exitosamente. Usuario: %s (@%s) cambió de "%s" a "%s"',
        $nombreCompleto,
        $usuarioAfectado['username'],
        ucfirst($rolAnterior),
        ucfirst($nuevoRol)
    );
    
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = 'success';
    
    // Log del sistema
    error_log(sprintf(
        "[CAMBIO_ROL] Superuser %s (@%s) cambió rol de usuario %s (@%s) de '%s' a '%s' - IP: %s",
        $_SESSION['usuario'] ?? 'unknown',
        $superuser['username'] ?? 'unknown',
        $nombreCompleto,
        $usuarioAfectado['username'],
        $rolAnterior,
        $nuevoRol,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ));
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['mensaje'] = 'Error al actualizar el rol: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    
    error_log("Error en cambio de rol: " . $e->getMessage());
}

// Redirigir de vuelta a la gestión de roles
header('Location: gestionar_roles_usuarios.php');
exit;
?>