<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar Concejal: ' . htmlspecialchars($concejal['nombre'] . ' ' . $concejal['apellido']) : 'Cargar Nuevo Concejal' ?></title>
    <!-- Asumiendo que se usa Bootstrap o un framework CSS similar para los estilos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .form-container { max-width: 600px; margin: 50px auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background-color: #ffffff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4 text-center"><?= $isEdit ? 'Editar Concejal' : 'Cargar Nuevo Concejal' ?></h2>

            <!-- Mostrar mensajes de error/éxito (Flash Messages) -->
            <?php if (!empty($error_mensaje)): ?>
                <div class="alert alert-<?= htmlspecialchars($tipo_mensaje_alerta) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form id="concejalForm" action="procesarConcejal.php" method="POST">
                
                <!-- Campo oculto para el token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <!-- Campo oculto para el ID si estamos en modo edición -->
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($concejal['id']) ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($concejal['nombre']) ?>" required>
                    <div class="invalid-feedback">Por favor ingrese el nombre.</div>
                </div>

                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido *</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?= htmlspecialchars($concejal['apellido']) ?>" required>
                    <div class="invalid-feedback">Por favor ingrese el apellido.</div>
                </div>

                <div class="mb-3">
                    <label for="dni" class="form-label">DNI / CUIT *</label>
                    <input type="text" class="form-control" id="dni" name="dni" value="<?= htmlspecialchars($concejal['dni']) ?>" required>
                    <div class="invalid-feedback">Por favor ingrese el DNI o CUIT.</div>
                </div>

                <div class="mb-3">
                    <label for="partido" class="form-label">Partido Político *</label>
                    <input type="text" class="form-control" id="partido" name="partido" value="<?= htmlspecialchars($concejal['partido']) ?>" required>
                    <div class="invalid-feedback">Por favor ingrese el partido político.</div>
                </div>

                <div class="mb-3">
                    <label for="circunscripcion" class="form-label">Circunscripción (Opcional)</label>
                    <input type="text" class="form-control" id="circunscripcion" name="circunscripcion" value="<?= htmlspecialchars($concejal['circunscripcion']) ?>">
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?= $isEdit ? 'Guardar Cambios' : 'Registrar Concejal' ?>
                    </button>
                    <a href="listar_concejales.php" class="btn btn-secondary mt-2">Cancelar y Volver</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts de Bootstrap para el manejo de alertas y componentes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Lógica de validación del cliente -->
    <script src="concejalForm.js"></script>
</body>
</html>