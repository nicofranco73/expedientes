<?php
// ====================================================================
// VISTA: Solo HTML, usa variables y función e() preparadas por el Controlador
// ====================================================================

// El controlador ya incluyó session_start() y la conexión.
// Solo incluimos los componentes de layout.
require 'header.php'; 
require 'sidebar.php'; 

// Asumimos que $superuser, $_SESSION['csrf_token'] y la función e() están disponibles.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña de Super Usuario - Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css">
    <link rel="stylesheet" href="/publico/css/superuser_security.css"> 
</head>

<body>
    
    <div class="container-fluid">
        <div class="row">
            <main class="col-12 col-md-10 ms-sm-auto px-4 py-4">
                <div class="page-header">
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

                <?php if (!empty($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= e($_SESSION['tipo_mensaje'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $_SESSION['tipo_mensaje'] === 'success' ? 'check-circle' : ($_SESSION['tipo_mensaje'] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                        <?= e($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
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
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">...</label>
                                        <input type="password" class="form-control form-control-lg" name="current_password" required autocomplete="current-password">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">...</label>
                                        <input type="password" class="form-control form-control-lg" name="new_password" id="newPassword" required autocomplete="new-password" minlength="8">
                                        <div class="password-strength" id="passwordStrength"></div>
                                        <small class="form-text text-muted">...</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold">...</label>
                                        <input type="password" class="form-control form-control-lg" name="confirm_password" id="confirmPassword" required autocomplete="new-password">
                                        <div class="invalid-feedback" id="passwordMatch"></div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            const form = document.getElementById('passwordForm');

            // Validador de fuerza de contraseña
            // ... (Código JavaScript sin cambios) ...
            newPassword.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                passwordStrength.className = 'password-strength strength-' + strength.level;
                passwordStrength.style.width = strength.percentage + '%';
            });

            // Validador de coincidencia de contraseñas
            // ... (Código JavaScript sin cambios) ...
            confirmPassword.addEventListener('input', function() {
                if (this.value !== newPassword.value) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    passwordMatch.textContent = 'Las contraseñas no coinciden';
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    passwordMatch.textContent = '';
                }
            });

            // Validación del formulario
            // ... (Código JavaScript sin cambios) ...
            form.addEventListener('submit', function(e) {
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    // Usar un método más amigable que alert() en producción, como SweetAlert2
                    alert('Las contraseñas no coinciden'); 
                    return false;
                }
                
                if (newPassword.value.length < 8) {
                    e.preventDefault();
                    alert('La nueva contraseña debe tener al menos 8 caracteres');
                    return false;
                }
                
                const strength = checkPasswordStrength(newPassword.value);
                if (strength.level === 'weak') {
                    // La validación de fuerza débil se puede dejar, ya que el controlador la verificará
                    const confirmed = confirm('La contraseña es débil. ¿Está seguro de que desea continuar?');
                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            function checkPasswordStrength(password) {
                let score = 0;
                
                if (password.length >= 8) score += 25;
                if (password.match(/[a-z]/)) score += 25;
                if (password.match(/[A-Z]/)) score += 25;
                if (password.match(/[0-9]/)) score += 15;
                if (password.match(/[^a-zA-Z0-9]/)) score += 10;
                
                if (score < 50) return { level: 'weak', percentage: score };
                if (score < 80) return { level: 'medium', percentage: score };
                return { level: 'strong', percentage: 100 };
            }
        });
    </script>
</body>
</html>