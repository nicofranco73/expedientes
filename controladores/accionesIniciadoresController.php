<?php
session_start(); // Asegura que la sesión esté iniciada

// Verifica la autenticación
if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión, redirigir al login
    header("Location: ../vistas/login.php");
    exit;
}

// Lógica de permisos (simulación)
// if (!tienePermiso('ver_iniciadores')) {
//     $_SESSION['mensaje'] = "Acceso denegado.";
//     $_SESSION['tipo_mensaje'] = "error";
//     header("Location: ../vistas/dashboard.php");
//     exit;
// }

// Si todo está OK, definimos las variables para la vista
$Nombre_usuario = $_SESSION['usuario'] ?? 'Desconocido';
$Vista_actual = 'acciones_iniciadores'; // Variable para activar el item del sidebar

// Inclusión del layout completo
include_once '../vistas/head.php';
include_once '../vistas/header.php';
include_once '../vistas/sidebar.php';

// Contenido principal de la vista de acciones de iniciadores
include_once '../vistas/acciones_iniciadores.php';

include_once '../vistas/footer.php';
?>