<?php
// Aseguramos que $base_url esté definida (aunque ya lo hace head.php)
if (!isset($base_url)) {
    $base_url = dirname($_SERVER['SCRIPT_NAME']);
}

// $Vista_actual debe ser definida en el controlador que invoca este sidebar.
// Se usa para resaltar el elemento del menú activo.
$Vista_actual = $Vista_actual ?? 'dashboard'; 
?>

<nav id="sidebar" class="sidebar-dashboard col-12 col-md-2 d-md-block px-0 py-4">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column gap-1 menu-dashboard">
            
            <!-- 1. DASHBOARD -->
            <li class="nav-item">
                <!-- RUTA AHORA APUNTA AL CONTROLADOR -->
                <a class="nav-link 
                    <?php echo ($Vista_actual == 'dashboard') ? 'active' : ''; ?>" 
                    href="<?php echo $base_url; ?>/controladores/dashboardController.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            
            <!-- 2. EXPEDIENTES -->
            <li class="nav-item">
                <!-- RUTA AHORA APUNTA AL CONTROLADOR -->
                <a class="nav-link 
                    <?php echo ($Vista_actual == 'acciones_expedientes') ? 'active' : ''; ?>" 
                    href="<?php echo $base_url; ?>/controladores/accionesExpedientesController.php">
                    <i class="bi bi-file-earmark-plus me-2"></i> Expedientes
                </a>
            </li>
            
            <!-- 3. INICIADORES -->
            <li class="nav-item">
                <!-- RUTA AHORA APUNTA AL CONTROLADOR -->
                <a class="nav-link 
                    <?php echo ($Vista_actual == 'acciones_iniciadores') ? 'active' : ''; ?>" 
                    href="<?php echo $base_url; ?>/controladores/accionesIniciadoresController.php">
                    <i class="bi bi-person-plus-2 me-2"></i> Iniciadores
                </a>
            </li>
            
            <!-- 4. CONSULTA PÚBLICA -->
            <li class="nav-item">
                <!-- RUTA AHORA APUNTA AL CONTROLADOR -->
                <a class="nav-link 
                    <?php echo ($Vista_actual == 'consulta_publica') ? 'active' : ''; ?>" 
                    href="<?php echo $base_url; ?>/controladores/consultaPublicaController.php">
                    <i class="bi bi-search me-2"></i> Consulta de Expedientes
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- 
    IMPORTANTE: Cerramos la columna del sidebar (col-md-2) e INICIAMOS la columna del contenido principal (col-md-10).
    Esto es CRUCIAL para que el layout no se rompa y el contenido aparezca a la derecha.
-->
<main class="col-md-10 ms-sm-auto col-lg-10 px-md-4 main-dashboard">
    <!-- El contenido de la vista (ej: acciones_expedientes.php) se incluirá aquí por el controlador. -->