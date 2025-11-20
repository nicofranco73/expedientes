<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Iniciador - ID: <?= htmlspecialchars($iniciador['id'] ?? 'N/A') ?></title>
    <!-- Bootstrap CSS y Icons (Asumo que head.php lo incluye) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Asumo la inclusión de head.php aquí -->
    <?php // require 'head.php'; ?>
</head>

<body>
    <!-- Asumo la inclusión de header.php aquí -->
    <?php // require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Asumo la inclusión de sidebar.php aquí -->
            <?php // require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Editar Iniciador (ID: <?= htmlspecialchars($iniciador['id'] ?? 'N/A') ?>)</h1>
                    <a href="listar_iniciadores.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>

                <!-- Mostrar errores -->
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulario de edición -->
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <!-- Datos personales -->
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-fill me-2"></i>Datos Personales
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="apellido" class="form-label">Apellido *</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" 
                                               value="<?= htmlspecialchars($_POST['apellido'] ?? $iniciador['apellido'] ?? '') ?>" required>
                                        <div class="invalid-feedback">El apellido es obligatorio.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?= htmlspecialchars($_POST['nombre'] ?? $iniciador['nombre'] ?? '') ?>" required>
                                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="dni" class="form-label">DNI *</label>
                                                <input type="text" class="form-control" id="dni" name="dni" 
                                                       value="<?= htmlspecialchars($_POST['dni'] ?? $iniciador['dni'] ?? '') ?>" required>
                                                <div class="invalid-feedback">El DNI es obligatorio.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cuil" class="form-label">CUIL</label>
                                                <input type="text" class="form-control" id="cuil" name="cuil" 
                                                       value="<?= htmlspecialchars($_POST['cuil'] ?? $iniciador['cuil'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                               value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? $iniciador['fecha_nacimiento'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                                <input type="text" class="form-control" id="nacionalidad" name="nacionalidad" 
                                                       value="<?= htmlspecialchars($_POST['nacionalidad'] ?? $iniciador['nacionalidad'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                                <select class="form-select" id="estado_civil" name="estado_civil">
                                                    <option value="">Seleccionar...</option>
                                                    <?php $ec = $_POST['estado_civil'] ?? $iniciador['estado_civil'] ?? ''; ?>
                                                    <?php foreach (['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a', 'Unión de hecho'] as $opcion): ?>
                                                        <option value="<?= $opcion ?>" <?= $ec === $opcion ? 'selected' : '' ?>>
                                                            <?= $opcion ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="profesion" class="form-label">Profesión</label>
                                        <input type="text" class="form-control" id="profesion" name="profesion" 
                                               value="<?= htmlspecialchars($_POST['profesion'] ?? $iniciador['profesion'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contacto y domicilio -->
                        <div class="col-md-6">
                            <!-- Contacto -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-telephone-fill me-2"></i>Contacto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($_POST['email'] ?? $iniciador['email'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="tel" name="tel" 
                                                       value="<?= htmlspecialchars($_POST['tel'] ?? $iniciador['tel'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cel" class="form-label">Celular</label>
                                                <input type="text" class="form-control" id="cel" name="cel" 
                                                       value="<?= htmlspecialchars($_POST['cel'] ?? $iniciador['cel'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Domicilio -->
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-house-fill me-2"></i>Domicilio
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="calle" class="form-label">Calle</label>
                                                <input type="text" class="form-control" id="calle" name="calle" 
                                                       value="<?= htmlspecialchars($_POST['calle'] ?? $iniciador['calle'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="numero" class="form-label">Número</label>
                                                <input type="text" class="form-control" id="numero" name="numero" 
                                                       value="<?= htmlspecialchars($_POST['numero'] ?? $iniciador['numero'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="piso" class="form-label">Piso</label>
                                                <input type="text" class="form-control" id="piso" name="piso" 
                                                       value="<?= htmlspecialchars($_POST['piso'] ?? $iniciador['piso'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="depto" class="form-label">Departamento</label>
                                                <input type="text" class="form-control" id="depto" name="depto" 
                                                       value="<?= htmlspecialchars($_POST['depto'] ?? $iniciador['depto'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="localidad" class="form-label">Localidad</label>
                                                <input type="text" class="form-control" id="localidad" name="localidad" 
                                                       value="<?= htmlspecialchars($_POST['localidad'] ?? $iniciador['localidad'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="cp" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="cp" name="cp" 
                                                       value="<?= htmlspecialchars($_POST['cp'] ?? $iniciador['cp'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-chat-text-fill me-2"></i>Observaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones adicionales</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars($_POST['observaciones'] ?? $iniciador['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between mb-4">
                        <a href="listar_iniciadores.php" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Actualizar Iniciador
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Scripts de Bootstrap y SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Validación de formulario de Bootstrap (Client-side)
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // SweetAlert de éxito post-actualización
        <?php if (isset($actualizado_exitosamente) && $actualizado_exitosamente): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Actualización exitosa!',
            text: 'Los datos del iniciador han sido actualizados correctamente.',
            showCancelButton: true,
            confirmButtonText: 'Ir al Listado',
            cancelButtonText: 'Quedarse Aquí',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'listar_iniciadores.php';
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>