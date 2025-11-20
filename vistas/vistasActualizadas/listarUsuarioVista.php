<?php
// ====================================================================
// VISTA: vistas/listarUsuario_view.php (Ola 5 - CORREGIDO)
// Contiene SÓLO HTML y PHP de presentación, utilizando variables del Controlador.
// ====================================================================

// Función para escapar HTML - ÚNICAMENTE PARA LA VISTA
function e($string) {
    // Manejar el caso de null o tipos no string que puedan venir de la BD
    if (is_null($string)) return '';
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Nota: Las variables como $usuarios, $total_usuarios, $error_mensaje, etc.,
// son accesibles aquí porque fueron definidas en listarUsuarioController.php.
?>
<main class="col-12 col-md-10 ms-sm-auto px-4 py-4">
    <div class="page-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 0; margin-bottom: 2rem; border-radius: 0 0 15px 15px;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-1 text-white">
                        <i class="bi bi-people-fill me-2"></i>Administración de Usuarios
                    </h1>
                    <p class="mb-0 opacity-75">Gestiona los usuarios del sistema de expedientes</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="gestionar_permisos_usuario.php" class="btn btn-success me-2">
                        <i class="bi bi-shield-check me-2"></i>Gestionar Permisos
                    </a>
                    <a href="crear_usuario.php" class="btn btn-light btn-lg">
                        <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 1.5rem;">
                <i class="bi bi-people fs-1 mb-2"></i>
                <h3 class="mb-1"><?= e($total_usuarios ?? 0) ?></h3>
                <p class="mb-0 opacity-75">Total Usuarios</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 1.5rem;">
                <i class="bi bi-shield-check fs-1 mb-2"></i>
                <h3 class="mb-1"><?= e($admin_count ?? 0) ?></h3>
                <p class="mb-0 opacity-75">Administradores</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 1.5rem;">
                <i class="bi bi-person-workspace fs-1 mb-2"></i>
                <h3 class="mb-1"><?= e($user_count ?? 0) ?></h3>
                <p class="mb-0 opacity-75">Usuarios</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 1.5rem;">
                <i class="bi bi-calendar-plus fs-1 mb-2"></i>
                <h3 class="mb-1"><?= e($new_today_count ?? 0) ?></h3>
                <p class="mb-0 opacity-75">Nuevos Hoy</p>
            </div>
        </div>
    </div>

    <?php if (!empty($error_mensaje)): ?>
        <?php
            $icon = 'info-circle';
            if (($tipo_mensaje_alerta ?? 'info') === 'success') {
                $icon = 'check-circle';
            } elseif ($tipo_mensaje_alerta === 'danger') {
                $icon = 'exclamation-triangle';
            }
        ?>
        <div class="alert alert-<?= e($tipo_mensaje_alerta ?? 'info') ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= e($icon) ?> me-2"></i>
            <?= e($error_mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="table-container" style="background: white; border-radius: 15px; box-shadow: 0 4px 24px rgba(70, 89, 125, 0.08); overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tablaUsuarios">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 py-3"><i class="bi bi-person me-2"></i>Usuario</th>
                        <th class="border-0 py-3"><i class="bi bi-card-text me-2"></i>Nombre Completo</th>
                        <th class="border-0 py-3"><i class="bi bi-envelope me-2"></i>Email</th>
                        <th class="border-0 py-3"><i class="bi bi-shield me-2"></i>Rol</th>
                        <th class="border-0 py-3"><i class="bi bi-calendar me-2"></i>Fecha Creación</th>
                        <th class="border-0 py-3 text-center"><i class="bi bi-gear me-2"></i>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr data-user-id="<?= e($usuario['id'] ?? '') ?>">
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                                            <?= strtoupper(substr(e($usuario['nombre'] ?? '?'), 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= e($usuario['username'] ?? 'N/A') ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <?= e(($usuario['apellido'] ?? '') . ', ' . ($usuario['nombre'] ?? '')) ?>
                                </td>
                                <td class="py-3">
                                    <i class="bi bi-envelope-fill text-muted me-2"></i>
                                    <?= e($usuario['email'] ?? 'Sin Email') ?>
                                </td>
                                <td class="py-3">
                                    <?php
                                    // Lógica de presentación de rol
                                    $role = $usuario['role'] ?? 'viewer'; 
                                    $roleBadgeClass = 'bg-primary';
                                    $roleIcon = 'person-fill';
                                    $roleText = 'Básico';

                                    if (isset($usuario['is_superuser']) && $usuario['is_superuser']) {
                                        $roleBadgeClass = 'bg-danger';
                                        $roleIcon = 'shield-fill-exclamation';
                                        $roleText = 'Super Usuario';
                                    } elseif ($role === 'admin') {
                                        $roleBadgeClass = 'bg-danger';
                                        $roleIcon = 'shield-fill-check';
                                        $roleText = 'Administrador';
                                    } elseif ($role === 'editor') {
                                        $roleBadgeClass = 'bg-warning text-dark';
                                        $roleIcon = 'pencil-fill';
                                        $roleText = 'Editor';
                                    } elseif ($role === 'viewer') {
                                        $roleBadgeClass = 'bg-info text-dark';
                                        $roleIcon = 'eye-fill';
                                        $roleText = 'Solo Lectura';
                                    }
                                    ?>
                                    <span class="role-badge badge <?= e($roleBadgeClass) ?>" style="font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 20px;">
                                        <i class="bi bi-<?= e($roleIcon) ?> me-1"></i>
                                        <?= e($roleText) ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= e(isset($usuario['fecha_creacion']) ? date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])) : 'N/A') ?>
                                    </small>
                                </td>
                                <td class="py-3 text-center">
                                    <a href="crear_usuario.php?id=<?= e($usuario['id'] ?? '') ?>" 
                                        class="btn btn-outline-primary btn-action" 
                                        title="Editar usuario" style="padding: 0.25rem 0.75rem; font-size: 0.875rem; border-radius: 6px; margin: 0 2px;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <?php if (!isset($usuario['is_superuser']) || !$usuario['is_superuser']): ?>
                                        <a href="gestionar_permisos_usuario.php?user_id=<?= e($usuario['id'] ?? '') ?>" 
                                            class="btn btn-outline-success btn-action" 
                                            title="Gestionar permisos" style="padding: 0.25rem 0.75rem; font-size: 0.875rem; border-radius: 6px; margin: 0 2px;">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button type="button" 
                                        class="btn btn-outline-danger btn-action btn-eliminar-usuario" 
                                        data-id="<?= e($usuario['id'] ?? '') ?>"
                                        title="Eliminar usuario" style="padding: 0.25rem 0.75rem; font-size: 0.875rem; border-radius: 6px; margin: 0 2px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people fs-1 mb-3"></i>
                                    <h5>No hay usuarios registrados</h5>
                                    <p>Crea el primer usuario del sistema</p>
                                    <a href="crear_usuario.php" class="btn btn-primary">
                                        <i class="bi bi-person-plus me-2"></i>Crear Usuario
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Información</h6>
                            <p class="text-muted small mb-0">Total de usuarios activos en el sistema de expedientes</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-2"><i class="bi bi-shield-check me-2"></i>Permisos</h6>
                            <p class="text-muted small mb-0">Solo administradores pueden gestionar usuarios</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-2"><i class="bi bi-question-circle me-2"></i>Ayuda</h6>
                            <p class="text-muted small mb-0">¿Necesitas ayuda? Contacta al soporte técnico</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>