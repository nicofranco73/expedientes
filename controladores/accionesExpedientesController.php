<?php
session_start(); // Asegura que la sesión esté iniciada

if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión, redirigir al login
    header("Location: ../vistas/login.php");
    exit;
}

// Opcional: Aquí iría la lógica de permisos si fuera necesaria.
/*
// Usaremos la función simulada tienePermiso() por ahora.
if (!tienePermiso('ver_acciones_expedientes')) {
    $_SESSION['mensaje'] = "Acceso denegado.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../vistas/dashboard.php");
    exit;
}
*/

// Si todo está OK, incluir la vista que solo contiene HTML.
// Las variables de sesión ($_SESSION['usuario'], etc.) ahora están disponibles.
$Nombre_usuario = $_SESSION['usuario'] ?? 'Desconocido'; // Pasamos las variables que usará el header
$vista_actual = 'acciones_expedientes'; // Variable para activar el item en el sidebar

// Incluir el layout completo
include_once '../vistas/head.php'; // Incluye el inicio del HTML, el CSS, y abre el <div class="container-fluid">
include_once '../vistas/header.php'; // Incluye la barra superior (el nav fuera de la fila)
include_once '../vistas/sidebar.php'; // Incluye el menú lateral y abre el <main class="col-md-10...">
include_once '../vistas/acciones_expedientes.php'; // El contenido principal de la página
include_once '../vistas/footer.php'; // Cierra el <main> y los <div>s de la estructura.

?>