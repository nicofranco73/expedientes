<?php
// Asegura que la sesión esté iniciada para poder usar $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Asegura que $base_url esté disponible.
// Si se llama directamente desde un controlador, ya debería estar definida, pero la aseguramos.
if (!isset($base_url)) {
    $base_url = dirname($_SERVER['SCRIPT_NAME']);
}

// INICIO CORRECCIÓN DE ERRORES

// ERROR #1 (Warning PHP): Soluciona el 'Undefined variable $Nombre_usuario'.
// Se usa el operador null coalesce (??) para asignar 'Desconocido' si $_SESSION['usuario'] no existe.
$Nombre_usuario = $_SESSION['usuario'] ?? 'Desconocido'; 

// FIN CORRECCIÓN DE ERRORES
?>

<nav class="navbar navbar-expand-lg header-dashboard shadow-sm py-3">
    <div class="container-fluid d-flex align-items-center justify-content-between px-4">
        
        <!-- Logo y Título -->
        <div class="d-flex align-items-center">
            <!-- 
                ERROR #2 (404 LOGOCDE.png): 
                Usamos $base_url para asegurar que la ruta sea absoluta desde la raíz del proyecto.
                Ejemplo: /expedientes/publico/imagen/LOGOCDE.png
            -->
            <img src="<?php echo $base_url; ?>/publico/imagen/LOGOCDE.png" alt="Logo CDE" class="logo-header me-3">
            <span class="fs-4 fw-bold titulo-header">Expedientes</span>
        </div>

        <!-- Usuario y Botón de Salir -->
        <div class="d-flex align-items-center">
            <span class="me-3 text-white">Usuario: <strong><?php echo htmlspecialchars($Nombre_usuario); ?></strong></span>
            <a href="<?php echo $base_url; ?>/controladores/logoutController.php" class="btn btn-outline-light btn">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>