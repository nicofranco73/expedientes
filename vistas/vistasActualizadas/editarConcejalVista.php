<?php 
// Las variables $id, $concejal, $form_data, $form_data son provistas por el Controlador.

// Obtener y limpiar mensajes de sesión para pasarlos a JS
$mensaje_sesion = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);
?>

<!DOCTYPE html>
<html lang="es">

<?php 
// Asegúrate de que las rutas a head.php, header.php y sidebar.php sean correctas.
require 'header.php'; 
require 'head.php'; 
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Editar Concejal (ID: <?= $id ?>)</h1>
                    <div>
                        <a href="listar_concejales.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="carga_concejal.php" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> Nuevo Concejal
                        </a>
                    </div>
                </div>

                <!-- Formulario de edición -->
                <form action="procesar_editar_concejal.php" method="POST" id="form-concejal" class="needs-validation" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    
                    <div class="row">
                        <!-- Datos personales -->
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person me-2"></i>
                                        Datos Personales
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="apellido" class="form-label">Apellido *</label>
                                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                                        value="<?= htmlspecialchars($form_data['apellido'] ?? '') ?>" required>
                                                <div class="invalid-feedback">El apellido es obligatorio.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre *</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                                        value="<?= htmlspecialchars($form_data['nombre'] ?? '') ?>" required>
                                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="dni" class="form-label">DNI *</label>
                                        <input type="text" class="form-control" id="dni" name="dni" 
                                                placeholder="Ingrese DNI"
                                                value="<?= htmlspecialchars($form_data['dni'] ?? '') ?>" required>
                                        <div class="invalid-feedback">El DNI es obligatorio.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" 
                                                value="<?= htmlspecialchars($form_data['direccion'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contacto -->
                        <div class="col-md-6">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-telephone-fill me-2"></i>
                                        Contacto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                                value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                                        <div class="invalid-feedback">Por favor, ingrese un email válido.</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel" class="form-label">Teléfono Fijo</label>
                                                <input type="tel" class="form-control" id="tel" name="tel" 
                                                        value="<?= htmlspecialchars($form_data['tel'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cel" class="form-label">Teléfono Celular</label>
                                                <input type="tel" class="form-control" id="cel" name="cel" 
                                                        value="<?= htmlspecialchars($form_data['cel'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información política -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-building me-2"></i>
                                Información Política
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bloque" class="form-label">Bloque</label>
                                        <input type="text" class="form-control" id="bloque" name="bloque" 
                                                placeholder="Ingrese el nombre del bloque"
                                                value="<?= htmlspecialchars($form_data['bloque'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="observacion" class="form-label">Observaciones</label>
                                        <input type="text" class="form-control" id="observacion" name="observacion" 
                                                placeholder="Observaciones adicionales"
                                                value="<?= htmlspecialchars($form_data['observacion'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <button type="button" class="btn btn-outline-secondary px-4 me-2" id="btn-restaurar">
                                <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                            </button>
                            <a href="listar_concejales.php" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Actualizar Concejal
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- PASAR DATOS DEL SERVIDOR A JS -->
    <script>
        // Datos originales del concejal (para la función de restauración)
        const ORIGINAL_DATA = <?= json_encode($concejal) ?>;
        // Mensaje de sesión (para SweetAlert)
        const SESSION_MESSAGE = '<?= addslashes($mensaje_sesion) ?>';
        const MESSAGE_TYPE = '<?= addslashes($tipo_mensaje) ?>';
    </script>
    
    <!-- Incluir el archivo JS externo -->
    <script src="js/editarConcejal.js"></script>
</body>
</html>