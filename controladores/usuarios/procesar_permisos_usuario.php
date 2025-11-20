<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Verificar permisos (solo admin y superuser pueden gestionar permisos)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superuser'])) {
    $_SESSION['mensaje'] = 'No tiene permisos para realizar esta acción';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gestionar_permisos_usuario.php');
    exit;
}

// CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['mensaje'] = 'Token CSRF inválido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_permisos_usuario.php');
    exit;
}

$user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$permisos = $_POST['permisos'] ?? [];

if (!$user_id) {
    $_SESSION['mensaje'] = 'Usuario no especificado';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: gestionar_permisos_usuario.php');
    exit;
}

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Verificar que el usuario existe y obtener su información
    $stmt = $db->prepare('SELECT username, nombre, apellido, is_superuser FROM usuarios WHERE id = ? AND is_active = 1');
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        $_SESSION['mensaje'] = 'Usuario no encontrado o inactivo';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: gestionar_permisos_usuario.php');
        exit;
    }
    
    // Verificar que no se trate de modificar permisos del super usuario
    if ($usuario['is_superuser'] == 1) {
        $_SESSION['mensaje'] = 'No se pueden modificar los permisos del Super Usuario';
        $_SESSION['tipo_mensaje'] = 'warning';
        header('Location: gestionar_permisos_usuario.php?user_id=' . $user_id);
        exit;
    }
    
    // Obtener todas las vistas disponibles
    $stmt = $db->query("SELECT nombre_vista FROM vistas_sistema WHERE activa = 1");
    $vistas_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $db->beginTransaction();
    
    // Eliminar todos los permisos existentes del usuario
    $stmt = $db->prepare('DELETE FROM permisos_usuario WHERE user_id = ?');
    $stmt->execute([$user_id]);
    
    // Insertar los nuevos permisos
    $stmt_insert = $db->prepare('INSERT INTO permisos_usuario (user_id, vista, puede_acceder) VALUES (?, ?, ?)');
    
    $permisos_otorgados = 0;
    $permisos_denegados = 0;
    
    foreach ($vistas_disponibles as $vista) {
        $puede_acceder = isset($permisos[$vista]) && $permisos[$vista] == 1;
        $stmt_insert->execute([$user_id, $vista, $puede_acceder ? 1 : 0]);
        
        if ($puede_acceder) {
            $permisos_otorgados++;
        } else {
            $permisos_denegados++;
        }
    }
    
    // Registrar la acción en logs de seguridad
    try {
        $descripcion = sprintf(
            'Permisos actualizados para %s (@%s): %d otorgados, %d denegados',
            $usuario['nombre'] . ' ' . $usuario['apellido'],
            $usuario['username'],
            $permisos_otorgados,
            $permisos_denegados
        );
        
        $log_stmt = $db->prepare('INSERT INTO logs_seguridad (user_id, accion, descripcion, ip_address, fecha) VALUES (?, ?, ?, ?, NOW())');
        $log_stmt->execute([
            $_SESSION['user_id'] ?? null,
            'ACTUALIZAR_PERMISOS_USUARIO',
            $descripcion,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Si falla el log, no es crítico
    }
    
    $db->commit();
    
    $_SESSION['mensaje'] = sprintf(
        'Permisos actualizados exitosamente para %s. %d vistas con acceso, %d vistas denegadas.',
        $usuario['nombre'] . ' ' . $usuario['apellido'],
        $permisos_otorgados,
        $permisos_denegados
    );
    $_SESSION['tipo_mensaje'] = 'success';
    
    // Advertencia si se quitaron todos los permisos
    if ($permisos_otorgados === 0) {
        $_SESSION['mensaje'] .= ' ⚠️ ATENCIÓN: El usuario no tiene acceso a ninguna vista.';
        $_SESSION['tipo_mensaje'] = 'warning';
    }
    
} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['mensaje'] = 'Error al actualizar permisos: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    
    // Log del error
    error_log("Error actualizando permisos usuario $user_id: " . $e->getMessage());
}

header('Location: gestionar_permisos_usuario.php?user_id=' . $user_id);
exit;
?>
