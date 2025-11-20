<?php
// ====================================================================
// VISTA: vistas/cambiarPasswordSuperUser_view.php (Ola 4 - Corregido)
// Contiene solo el HTML del contenido principal.
// ====================================================================

/* * 📌 MARCA DE CENTRALIZACIÓN DE ASSETS:
 * * Todo el CSS (Bootstrap, bootstrap-icons, estilos.css, superuser_security.css)
 * * debe ser llamado en 'header.php' o un archivo similar.
 * * Todo el JS (Bootstrap Bundle JS) debe ser llamado en 'footer.php'.
 */
?>

<div class="container-fluid">
    <div class="row justify-content-center"> <main class="col-12 col-lg-10 px-4 py-4">
            <div class="page-header mb-4">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-1">
                                <i class="bi bi-shield-fill-exclamation me-2"></i>
                                Cambiar Contraseña de Super Usuario
                            </h1>
                            <p class="mb-0 opacity-75">
                                Configuración de seguridad del administrador principal del sistema
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="superuser-badge">
                                <i class="bi bi-shield-check me-1"></i>
                                Super Usuario
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($mensaje_sesion)): ?>
                <div class="alert alert-<?= e($tipo_mensaje_sesion) ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $tipo_mensaje_sesion === 'success' ? 'check-circle' : ($tipo_mensaje_sesion === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                    <?= e($mensaje_sesion) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="security-warning">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-2"></i>
                            <div>
                                <h5 class="mb-1 text-warning">⚠️ Área de Máxima Seguridad</h5>
                                <p class="mb-0 text-dark">
                                    Está accediendo a la configuración del Super Usuario. Esta es la única forma de cambiar 
                                    la contraseña del administrador principal del sistema. Mantenga esta información segura.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="security-container">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Usuario Actual</h6>
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-person-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0"><?= e($superuser['apellido'] . ', ' . $superuser['nombre']) ?></h5>
                                        <small class="text-muted">@<?= e($superuser['username']) ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Email</h6>
                                <p class="mb-0">
                                    <i class="bi bi-envelope me-2"></i>
                                    <?= e($superuser['email']) ?>
                                </p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <form method="post" action="procesar_cambio_password_superuser.php" id="passwordForm">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold" for="currentPassword">Contraseña Actual</label>
                                    <input type="password" class="form-control form-control-lg" name="current_password" id="currentPassword" required autocomplete="current-password">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold" for="newPassword">Nueva Contraseña</label>
                                    <input type="password" class="form-control form-control-lg" name="new_password" id="newPassword" required autocomplete="new-password" minlength="8">
                                    <div class="password-strength-container mt-1">
                                        <div class="password-strength" id="passwordStrength"></div>
                                    </div>
                                    <small class="form-text text-muted">Mínimo 8 caracteres. Recomendado usar mayúsculas, minúsculas y símbolos.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold" for="confirmPassword">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control form-control-lg" name="confirm_password" id="confirmPassword" required autocomplete="new-password">
                                    <div class="invalid-feedback" id="passwordMatch">Las contraseñas no coinciden</div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                                </a>
                                <button type="submit" class="btn btn-security">
                                    <i class="bi bi-shield-check me-2"></i>
                                    Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>