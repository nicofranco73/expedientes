<!DOCTYPE html>
<html lang="es">
    <!-- NOTA: Necesitas crear estos archivos e incluirlos correctamente en tu estructura de directorios -->
    <?php require 'head.php'; ?> 
    
<body>
    <?php require 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->
            <!-- Asumo que el sidebar.php está en el mismo nivel que el controlador o en vistas/ -->
            <?php require 'vistas/sidebar.php'; ?> 
            <!-- Fin Sidebar -->
            
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <h1 class="mt-4 pb-3 border-bottom">Dashboard <small class="text-muted fs-5">Bienvenido, <?= htmlspecialchars($username) ?></small></h1>

                <!-- Estadísticas (Usando datos del Modelo) -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card-dashboard card shadow-sm h-100 border-0">
                            <div class="card-body text-center">
                                <div class="mb-3"><i class="bi bi-files fs-2 text-primary"></i></div>
                                <h5 class="card-title">Expedientes Totales</h5>
                                <span class="display-6 fw-bold text-primary"><?= htmlspecialchars($stats['expedientes_totales']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-dashboard card shadow-sm h-100 border-0">
                            <div class="card-body text-center">
                                <div class="mb-3"><i class="bi bi-plus-circle fs-2 text-success"></i></div>
                                <h5 class="card-title">Expedientes Hoy</h5>
                                <span class="display-6 fw-bold text-success"><?= htmlspecialchars($stats['expedientes_hoy']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-dashboard card shadow-sm h-100 border-0">
                            <div class="card-body text-center">
                                <div class="mb-3"><i class="bi bi-exclamation-circle fs-2 text-warning"></i></div>
                                <h5 class="card-title">Pendientes</h5>
                                <span class="display-6 fw-bold text-warning"><?= htmlspecialchars($stats['pendientes']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <a href="listar_usuarios.php" class="btn btn-outline-dark btn-lg w-100">
                            <i class="bi bi-people"></i> Administrar usuarios
                        </a>
                    </div>
                    <?php if ($is_superuser): ?>
                    <div class="col-md-6">
                        <a href="cambiar_password_superuser.php" class="btn btn-danger btn-lg w-100">
                            <i class="bi bi-shield-fill-exclamation"></i> Configuración Super Usuario
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($is_superuser): ?>
                <!-- Panel especial para Super Usuario -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-danger border-danger">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-fill-exclamation text-danger me-3 fs-2"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Acceso de Super Usuario Activo</h5>
                                    <p class="mb-2">
                                        Tiene privilegios máximos en el sistema. Use estas herramientas con precaución.
                                    </p>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="d-block">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Control total de usuarios
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="d-block">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Configuración del sistema
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="d-block">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Cambio de contraseña seguro
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Herramientas de Desarrollo (solo si el usuario es 'admin') -->
                <?php if ($username === 'admin'): ?>
                <div class="row g-4 mt-4">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="mb-0">🛠️ Herramientas de Desarrollo y Pruebas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <a href="prueba_edicion_concejales.php" class="btn btn-outline-info btn-sm w-100">
                                            <i class="bi bi-bug"></i> Probar Edición Concejales
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="diagnostico_concejales.php" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="bi bi-wrench"></i> Diagnóstico Concejales
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="verificar_estructura_tablas.php" class="btn btn-outline-warning btn-sm w-100">
                                            <i class="bi bi-table"></i> Verificar Estructura
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <!-- Script de Bootstrap, si es necesario, puede ir aquí o en un footer.php -->
</body>
</html>