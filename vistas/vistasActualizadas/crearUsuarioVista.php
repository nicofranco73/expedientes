<?php
// CORRECCIÓN 1: Iniciar sesión ANTES de acceder a $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para escapar HTML - ÚNICAMENTE PARA LA VISTA
function e($string) {
    // CORRECCIÓN 2: Asegurar que el valor de $string sea un escalar antes de la comprobación
    $safe_string = is_scalar($string) ? (string)$string : '';
    if ($safe_string === '') return '';
    return htmlspecialchars($safe_string, ENT_QUOTES, 'UTF-8');
}

// Las variables $isEdit, $user, $error_mensaje, $tipo_mensaje_alerta, $currentUserRole 
// provienen de 'crear_usuario_controller.php'.
// CORRECCIÓN 3: Establecer un valor predeterminado seguro para el token CSRF, si no existe
$csrf_token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';

// CORRECCIÓN 4: Inicializar las variables para evitar errores de tipo 'Undefined array key' o 'Undefined variable' 
// si el controlador no las define correctamente, especialmente 'user' y las de alerta.
// Esto es defensivo. En un sistema MVC correcto, el controlador siempre debería proveerlas.
$isEdit = $isEdit ?? false;
$user = $user ?? ['id' => null, 'username' => '', 'email' => '', 'apellido' => '', 'nombre' => '', 'role' => ($isEdit ? '' : 'usuario')];
$error_mensaje = $error_mensaje ?? '';
$tipo_mensaje_alerta = $tipo_mensaje_alerta ?? 'info';
$currentUserRole = $currentUserRole ?? 'usuario'; // Suponemos 'usuario' como mínimo por defecto

// Se usa una lógica mejor para el rol pre-seleccionado en modo "Crear"
$default_role = $user['role'] ?: 'usuario';

// El valor predeterminado del rol para la previsualización
$initial_role_display = '';
switch($default_role) {
    case 'superuser': $initial_role_display = 'Super Administrador'; break;
    case 'admin': $initial_role_display = 'Administrador'; break;
    case 'editor': $initial_role_display = 'Editor'; break;
    case 'usuario': $initial_role_display = 'Usuario Estándar'; break;
    default: $initial_role_display = 'Sin Rol';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Editar' : 'Crear' ?> Usuario - Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css">
    
    <style>
        .required-field::after {
            content: " *";
            color: red;
        }
        .form-section {
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 0.3rem;
            background-color: #f8f9fa;
        }
        .user-preview {
            padding: 20px;
            background-color: #343a40; /* Fondo oscuro para el preview */
            color: #fff;
            border-radius: 0.3rem;
            text-align: center;
        }
        .user-avatar-large i {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #0d6efd; /* Color primario */
        }
        .btn-action {
            min-width: 180px;
            margin: 5px;
        }
    </style>
</head>

<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4 py-4">
                <div class="page-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="mb-1">
                                    <i class="bi bi-<?= $isEdit ? 'person-gear' : 'person-plus' ?> me-2"></i>
                                    <?= $isEdit ? 'Editar' : 'Crear' ?> Usuario
                                </h1>
                                <p class="mb-0 opacity-75">
                                    <?= $isEdit ? 'Modifica la información del usuario' : 'Agrega un nuevo usuario al sistema' ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <a href="listar_usuarios.php" class="btn btn-light btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Volver a Usuarios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($error_mensaje): ?>
                    <?php 
                        // CORRECCIÓN 6: Lógica más limpia para el icono del mensaje de alerta
                        $icon = 'info-circle';
                        if ($tipo_mensaje_alerta === 'success') {
                            $icon = 'check-circle';
                        } elseif ($tipo_mensaje_alerta === 'danger' || $tipo_mensaje_alerta === 'warning') {
                            $icon = 'exclamation-triangle';
                        }
                    ?>
                    <div class="alert alert-<?= e($tipo_mensaje_alerta) ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $icon ?> me-2"></i>
                        <?= e($error_mensaje) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-container">
                            <form method="post" action="procesar_usuario.php" id="userForm"> 
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                                
                                <div class="form-section">
                                    <h5><i class="bi bi-person-circle me-2"></i>Información Básica</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field" for="username">Nombre de Usuario</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" 
                                                         class="form-control" 
                                                         name="username" 
                                                         id="username"
                                                         value="<?= e($user['username']) ?>"
                                                         placeholder="Ej: jperez"
                                                         required
                                                         autocomplete="username"
                                                         maxlength="50"> </div>
                                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>Solo letras, números y guiones bajos (máx. 50 caracteres)</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field" for="email">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                <input type="email" 
                                                         class="form-control" 
                                                         name="email" 
                                                         id="email"
                                                         value="<?= e($user['email']) ?>"
                                                         placeholder="usuario@ejemplo.com"
                                                         required
                                                         autocomplete="email"
                                                         maxlength="100"> </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field" for="apellido">Apellido</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                                <input type="text" 
                                                         class="form-control" 
                                                         name="apellido" 
                                                         id="apellido"
                                                         value="<?= e($user['apellido']) ?>"
                                                         placeholder="Apellido del usuario"
                                                         required
                                                         autocomplete="family-name"
                                                         maxlength="100"> </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field" for="nombre">Nombre</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                                                <input type="text" 
                                                         class="form-control" 
                                                         name="nombre" 
                                                         id="nombre"
                                                         value="<?= e($user['nombre']) ?>"
                                                         placeholder="Nombre del usuario"
                                                         required
                                                         autocomplete="given-name"
                                                         maxlength="100"> </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h5><i class="bi bi-shield-check me-2"></i>Permisos y Seguridad</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label required-field" for="role">Rol del Usuario</label>
                                            <?php $isRoleDisabled = ($isEdit && $user['role'] === 'superuser' && $currentUserRole !== 'superuser' && $user['id'] !== $_SESSION['user_id']); ?>
                                            
                                            <select name="role" id="role" class="form-select" required onchange="updateRoleInfo()" 
                                                <?= $isRoleDisabled ? 'disabled' : '' ?>>
                                                
                                                <option value="" disabled>Seleccionar rol...</option>
                                                <?php 
                                                // Mostrar la opción 'superuser' solo si el usuario actual tiene ese rol 
                                                $canSeeSuperuser = ($currentUserRole === 'superuser');
                                                $isSuperuserBeingEdited = ($user['role'] === 'superuser');

                                                if ($canSeeSuperuser || $isSuperuserBeingEdited): ?>
                                                    <option value="superuser" <?= ($default_role === 'superuser') ? 'selected' : '' ?> >
                                                        Super Administrador
                                                    </option>
                                                <?php endif; ?>
                                                
                                                <option value="admin" <?= ($default_role === 'admin') ? 'selected' : '' ?>>
                                                    Administrador
                                                </option>
                                                <option value="editor" <?= ($default_role === 'editor') ? 'selected' : '' ?>>
                                                    Editor
                                                </option>
                                                <option value="usuario" <?= ($default_role === 'usuario') ? 'selected' : '' ?>>
                                                    Usuario Estándar
                                                </option>
                                            </select>
                                            
                                            <?php if ($isRoleDisabled): ?>
                                                <input type="hidden" name="role" value="<?= e($user['role']) ?>">
                                                <div class="alert alert-warning mt-2 small p-2">
                                                    No tiene permisos para modificar el rol de un Super Administrador.
                                                </div>
                                            <?php endif; ?>

                                            <div class="form-text role-info mt-2" id="roleDescription">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label <?= $isEdit ? '' : 'required-field' ?>" for="password">
                                                Contraseña 
                                                <?= $isEdit ? ' (Dejar vacío para no cambiar)' : '' ?>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                <input type="password" 
                                                         class="form-control" 
                                                         name="password" 
                                                         id="password"
                                                         placeholder="<?= $isEdit ? '••••••••' : 'Ingrese contraseña' ?>"
                                                         <?= $isEdit ? '' : 'required' ?>
                                                         autocomplete="new-password"
                                                         minlength="<?= $isEdit ? '' : '8' ?>"> </div>
                                            <div class="form-text"><i class="bi bi-key me-1"></i><?= $isEdit ? 'Mínimo 8 caracteres (solo si se cambia).' : 'Mínimo 8 caracteres requerido.' ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-action">
                                        <i class="bi bi-<?= $isEdit ? 'save' : 'plus-circle' ?> me-2"></i>
                                        <?= $isEdit ? 'Guardar Cambios' : 'Crear Usuario' ?>
                                    </button>
                                    <a href="listar_usuarios.php" class="btn btn-outline-secondary btn-action">
                                        <i class="bi bi-x-lg me-2"></i>Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="user-preview">
                            <div class="user-avatar-large">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h4 class="mb-1" id="previewName">
                                <?= $isEdit ? e($user['nombre'] . ' ' . $user['apellido']) : 'Nombre Apellido' ?>
                            </h4>
                            <p class="opacity-75" id="previewUsername">@<?= $isEdit ? e($user['username']) : 'usuario_nuevo' ?></p>
                            <p class="small" id="previewRole">
                                <i class="bi bi-shield-fill me-1"></i>Rol: <?= $isEdit ? $initial_role_display : 'Usuario Estándar' ?>
                            </p>
                            <hr class="opacity-50">
                            <p class="small mb-0 text-white-50">
                                Los cambios son efectivos inmediatamente después de guardar.
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CORRECCIÓN 11: Mover las descripciones de los roles a una estructura de datos
        const roleDescriptions = {
            'superuser': { 
                desc: 'Acceso total sin restricciones, incluyendo la gestión de otros administradores. Usar con extrema precaución.',
                name: 'Super Administrador' 
            },
            'admin': { 
                desc: 'Acceso completo a la gestión de usuarios, expedientes y configuración del sistema.',
                name: 'Administrador' 
            },
            'editor': { 
                desc: 'Permisos para crear, modificar y eliminar expedientes, pero sin acceso a la administración de usuarios.',
                name: 'Editor' 
            },
            'usuario': { 
                desc: 'Permisos básicos para ver sus propios expedientes y realizar tareas limitadas. (Recomendado)',
                name: 'Usuario Estándar' 
            }
        };

        // Función para actualizar la información de previsualización y descripción de rol
        function updateRoleInfo() {
            const roleSelect = document.getElementById('role');
            // CORRECCIÓN 12: Usar el valor del select, o el valor del input oculto si el select está deshabilitado
            const roleValue = roleSelect ? roleSelect.value : document.querySelector('input[name="role"][type="hidden"]').value;
            
            const roleDescDiv = document.getElementById('roleDescription');
            const previewRole = document.getElementById('previewRole');
            
            const roleData = roleDescriptions[roleValue];
            
            let description = roleData ? roleData.desc : 'Seleccione un rol para ver la descripción de sus permisos.';
            let roleName = roleData ? roleData.name : 'Sin Rol';

            roleDescDiv.innerHTML = `<i class="bi bi-info-circle me-1"></i> ${description}`;
            previewRole.innerHTML = `<i class="bi bi-shield-fill me-1"></i> Rol: ${roleName}`;
        }
        
        // Función para previsualizar Nombre y Apellido
        function updatePreview() {
            const nombre = document.getElementById('nombre').value;
            const apellido = document.getElementById('apellido').value;
            const username = document.getElementById('username').value;
            
            // CORRECCIÓN 13: Uso de trim() para evitar espacios iniciales/finales
            const full_name = (nombre.trim() || 'Nombre') + ' ' + (apellido.trim() || 'Apellido');
            
            document.getElementById('previewName').innerText = full_name;
            document.getElementById('previewUsername').innerText = `@${username.trim() || 'usuario_nuevo'}`;
            // El rol se actualiza con updateRoleInfo()
        }
        
        // Asignación de eventos y llamada inicial
        document.getElementById('nombre').addEventListener('input', updatePreview);
        document.getElementById('apellido').addEventListener('input', updatePreview);
        document.getElementById('username').addEventListener('input', updatePreview);
        
        // CORRECCIÓN 14: Evitar error si el select está deshabilitado y no existe, o si existe.
        const roleSelectElement = document.getElementById('role');
        if(roleSelectElement) {
            roleSelectElement.addEventListener('change', updateRoleInfo);
        }

        // Llamar al inicio para establecer la descripción y previsualización iniciales
        updateRoleInfo();
        updatePreview();
    </script>
</body>
</html>