<?php
// ====================================================================
// CONTROLADOR: Maneja Acceso, Seguridad, DB y prepara datos para la Vista
// ====================================================================

session_start();

// 1. REQUERIR CONEXIÓN PDO (Centralizada)
// Asumimos que esta es la ruta correcta para el objeto $db
require_once('../../db/connection.php'); 

// 2. FUNCIÓN DE ESCAPE HTML (Mover a un archivo de utilidades si es posible)
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// 3. CONTROL DE ACCESO (Lógica de Controlador)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superuser') {
    $_SESSION['mensaje'] = 'Acceso denegado. Solo el Super Usuario puede acceder a esta página.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

// 4. CSRF Token (Lógica de Seguridad)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Variables a pasar a la vista
$superuser = [];

try {
    // 5. OBTENER INFORMACIÓN DEL SUPER USUARIO (Lógica de DB)
    $stmt = $db->prepare('SELECT username, nombre, apellido, email FROM usuarios WHERE id = ? AND is_superuser = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $superuser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$superuser) {
        // Redirigir si el usuario no es válido, incluso si la sesión lo decía
        $_SESSION['mensaje'] = 'Error: Usuario no encontrado o no es super usuario.';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: dashboard.php');
        exit;
    }
    
} catch (Exception $e) {
    // Manejo de errores de base de datos
    $_SESSION['mensaje'] = 'Error de conexión o consulta a la base de datos.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: dashboard.php'); // Redirigir al dashboard en caso de error crítico
    exit;
}

// 6. INCLUIR LA VISTA (al final)
require 'vistas/cambiar_password_superuser_vista.php';