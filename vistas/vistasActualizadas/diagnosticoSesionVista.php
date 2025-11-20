<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-header {
            border-bottom: 3px solid #0dcaf0;
            background-color: #e0f7fa !important;
            color: #000 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-info-circle"></i> Diagnóstico de Sesión del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!$is_session_active): ?>
                            <div class="alert alert-warning border-warning">
                                <h5><i class="bi bi-exclamation-triangle"></i> No hay sesión activa</h5>
                                <p>No se encontraron variables en la sesión. Necesitas iniciar sesión primero para ver los datos.</p>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success border-success">
                                <h5><i class="bi bi-check-circle"></i> Sesión Activa</h5>
                                <p>El sistema ha detectado una sesión iniciada correctamente.</p>
                            </div>
                            
                            <!-- Información Clave del Usuario -->
                            <h6><i class="bi bi-person"></i> Información del Usuario:</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Variable</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>ID de Usuario (`usuario_id`):</strong></td>
                                            <td><?= $usuario_id ? htmlspecialchars($usuario_id) : '<span class="text-danger">No definido</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nombre de Usuario (`usuario`):</strong></td>
                                            <td><?= $username ? htmlspecialchars($username) : '<span class="text-danger">No definido</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Rol (`rol`):</strong></td>
                                            <td>
                                                <?php if ($rol): ?>
                                                    <span class="badge bg-<?= $rol === 'admin' ? 'danger' : ($rol === 'usuario' ? 'success' : 'info') ?>">
                                                        <?= htmlspecialchars($rol) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-danger">No definido</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Todas las variables de sesión -->
                            <h6><i class="bi bi-list-stars"></i> Todas las variables de sesión (`$_SESSION`):</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-hover table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Clave</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($session_data as $key => $value): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($key) ?></code></td>
                                            <td><?= htmlspecialchars(is_string($value) || is_numeric($value) ? $value : json_encode($value)) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Verificación de Permisos -->
                            <div class="mt-4">
                                <h6><i class="bi bi-shield-check"></i> Verificación de Permisos:</h6>
                                <div class="alert alert-<?= $puedeCrearUsuarios ? 'success' : 'danger' ?>">
                                    <?php if ($puedeCrearUsuarios): ?>
                                        <i class="bi bi-check-circle"></i> **Permiso Concedido:** Tienes permisos de Administrador (`admin`) para crear nuevos usuarios.
                                        <div class="mt-2">
                                            <a href="crear_usuario.php" class="btn btn-success btn-sm">
                                                <i class="bi bi-person-plus"></i> Ir a Crear Usuario
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle"></i> **Permiso Denegado:** No tienes permisos suficientes para crear usuarios. 
                                        <br><small>Tu rol actual es: **<?= htmlspecialchars($rol ?? 'No definido') ?>**</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Acciones -->
                        <div class="mt-4 pt-3 border-top">
                            <h6><i class="bi bi-gear"></i> Acciones Rápidas:</h6>
                            <div class="btn-group" role="group">
                                <a href="dashboard.php" class="btn btn-primary">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </a>
                                <a href="logout.php" class="btn btn-outline-danger">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>