<?php
// CORRECCIÓN 1: Iniciar sesión ANTES de cualquier output, aunque ya estaba.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CORRECCIÓN 2: Usar un archivo de configuración externo (config.php) para las credenciales de BD.
// Esto mejora la seguridad y la modularidad.
require_once __DIR__ . '/config.php'; 

// Incluir el middleware de verificación de permisos
require_once __DIR__ . '/verificar_permisos.php';

// CORRECCIÓN 3: Normalizar el nombre de la vista a 'crearUsuario.php' (o el que corresponda)
// y verificar que el usuario tenga permisos para acceder a esta vista (Gestión de Usuarios).
// Asumimos que el permiso requerido es 'manage_users' o similar.
verificarPermisoVista('manage_users'); // O el permiso real para crear/editar usuarios

// Variables de estado y datos iniciales para la vista
$isEdit = false;
// Inicialización defensiva de la estructura de datos del usuario
$user = [
    'id' => null, 
    'username' => '', 
    'nombre' => '', 
    'apellido' => '', 
    'email' => '', 
    'role' => 'usuario' // Rol por defecto al crear
]; 
$error_mensaje = null;
$tipo_mensaje_alerta = null;
// Rol del usuario actual para lógica de visibilidad (e.g., opción superuser)
$currentUserRole = $_SESSION['user_role'] ?? 'invitado'; 

try {
    // 1. Conexión a la base de datos (usando constantes de config.php)
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // CORRECCIÓN 4: Modo de extracción por defecto a FETCH_ASSOC para consistencia
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
        ]
    );

    // 2. Determinar modo y cargar datos (Lógica de Edición)
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // CORRECCIÓN 5: Consulta más estricta para asegurar que el ID no sea 0/negativo
        if ($id > 0) {
            $isEdit = true;
            
            $stmt = $db->prepare('SELECT id, username, nombre, apellido, email, role FROM usuarios WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $fetchedUser = $stmt->fetch();

            if (!$fetchedUser) {
                // CORRECCIÓN 6: Usar la función para mensajes de sesión
                setSessionMessage('Usuario no encontrado para editar.', 'warning');
                header('Location: listar_usuarios.php'); 
                exit;
            }

            // CORRECCIÓN 7: Verificación de permisos para editar el rol de OTROS usuarios
            if ($fetchedUser['role'] === 'superuser' && $currentUserRole !== 'superuser') {
                // Solo un superuser puede editar a otro superuser. Opcional: permitir editarse a sí mismo.
                if ($fetchedUser['id'] !== ($_SESSION['user_id'] ?? 0)) {
                     setSessionMessage('No tiene permisos para editar un Super Administrador.', 'danger');
                     header('Location: listar_usuarios.php'); 
                     exit;
                }
            }

            $user = $fetchedUser;
        }
    }
    
    // 3. Gestión de CSRF token
    if (empty($_SESSION['csrf_token'])) { 
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // CORRECCIÓN 8: Usar 32 bytes para un token más robusto
    }

} catch (PDOException $e) {
    // 4. Manejo de errores de conexión/base de datos
    // CORRECCIÓN 6: Usar la función para mensajes de sesión
    error_log("Error DB en crearUsuarioController: " . $e->getMessage()); // Registrar el error
    setSessionMessage('Error interno del servidor. Por favor, intente más tarde.', 'danger');
    // En un error grave de DB, lo mejor es redirigir a un lugar seguro o mostrar un error genérico
    header('Location: index.php'); // Redirigir a la página principal
    exit;
} catch (Exception $e) {
    error_log("Error General en crearUsuarioController: " . $e->getMessage());
    setSessionMessage('Ocurrió un error inesperado.', 'danger');
    header('Location: index.php');
    exit;
}

// --- Lógica de Mensaje de Sesión (Post-redirect Get) ---

// CORRECCIÓN 9: Se recomienda centralizar esta función si se usa en varios controladores.
// Si no existe, se define aquí para la funcionalidad.

if (!function_exists('setSessionMessage')) {
    function setSessionMessage($message, $type = 'info') {
        $_SESSION['mensaje'] = $message;
        $_SESSION['tipo_mensaje'] = $type;
    }
}


if (!empty($_SESSION['mensaje'])) {
    $error_mensaje = $_SESSION['mensaje'];
    $tipo_mensaje_alerta = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}

// Incluir la vista
// CORRECCIÓN 10: Asegurar que el nombre del archivo de vista coincida con la convención
require 'crearUsuarioVista.php';
?>