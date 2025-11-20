<?php
// ====================================================================
// VISTA: Solo muestra HTML, usa las variables definidas por el controlador
// ====================================================================

// Quitamos el session_start() y la lógica de base de datos que ya movimos.
// Ahora solo incluimos los archivos de cabecera.
require 'header.php';
require 'head.php'; 
// NOTA: Si su estructura requiere que estos archivos sean incluidos por el 
// controlador, puede moverlos allí y quitarlos de aquí.

// Aseguramos que la variable $expediente exista, aunque el controlador
// ya validó su existencia y es un array si llegó hasta aquí.
$expediente = $expediente ?? []; 
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Actualizar Expediente</title>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg header-dashboard shadow-sm py-3">
        <div class="container-fluid d-flex align-items-center justify-content-between px-0">
            <div class="d-flex align-items-center">
                <img src="/publico/imagen/LOGOCDE.png" alt="Logo" class="logo-header me-3" style="height:76px;">
                <span class="fs-4 fw-bold titulo-header">Expedientes</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-secondary">Usuario: <strong>Admin</strong></span>
                <a href="#" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">

            <?php require '../vistas/sidebar.php'; ?>
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <div class="main-box carga">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="titulo-principal text-center">Actualizar Expediente</h1>
                        <a href="listar_expedientes.php" class="btn btn-primary px-4">
                            <i class="bi bi-list-ul"></i> Listar Expedientes
                        </a>
                    </div>
                    <?php
                    // Muestra SweetAlerts con el mensaje de sesión
                    if (isset($_SESSION['mensaje'])) {
                        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
                        $icon = match($tipo) {
                            'success' => 'success',
                            'danger' => 'error',
                            'warning' => 'warning',
                            default => 'info'
                        };
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                Swal.fire({
                                    // LA PROTECCIÓN XSS SIGUE AQUÍ
                                    title: '<?= htmlspecialchars($_SESSION['mensaje'] ?? 'Error desconocido') ?>',
                                    icon: '<?= $icon ?>',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#0d6efd'
                                });
                            });
                        </script>
                        <?php
                        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
                    }
                    ?>
                    
                    <form action="procesar_actualizacion.php" method="post" autocomplete="off">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($expediente['id'] ?? '') ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" id="numero" name="numero" class="form-control" 
                                        value="<?= htmlspecialchars($expediente['numero'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="letra" class="form-label">Letra</label>
                                <input type="text" id="letra" name="letra" class="form-control" 
                                        value="<?= htmlspecialchars($expediente['letra'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="folio" class="form-label">Folio</label>
                                <input type="text" id="folio" name="folio" class="form-control" 
                                        value="<?= htmlspecialchars($expediente['folio'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="anio" class="form-label">Año</label>
                                <input type="text" id="anio" name="anio" class="form-control" 
                                        value="<?= htmlspecialchars($expediente['anio'] ?? '') ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="lugar" class="form-label">Lugar actual</label>
                                <input type="text" id="lugar" name="lugar" class="form-control" 
                                        value="<?= htmlspecialchars($expediente['lugar'] ?? '') ?>" readonly>
                            </div>

                            <div class="col-12">
                                <label for="extracto" class="form-label">Extracto</label>
                                <textarea id="extracto" name="extracto" class="form-control" 
                                            rows="3"><?= htmlspecialchars($expediente['extracto'] ?? '') ?></textarea>
                                <div class="form-text">Sin límite de caracteres (opcional)</div>
                            </div>

                            <div class="col-12 mb-2">
                                <label for="iniciador" class="form-label">Iniciador</label>
                                <?php if (!$iniciador_id): // Alerta si no se encontró en la BD de iniciadores ?>
                                    <div class="alert alert-warning mb-2">
                                        <small><strong>Iniciador actual:</strong> <?= htmlspecialchars($expediente['iniciador'] ?? 'N/A') ?></small><br>
                                        <small><em>No se pudo encontrar en la base de datos de iniciadores. Seleccione uno nuevo o verifique los datos.</em></small>
                                    </div>
                                <?php endif; ?>
                                <select id="iniciador" name="iniciador" class="form-select" required>
                                    <option value="">Seleccione un iniciador...</option>
                                    <?php if (!$iniciador_id): ?>
                                        <option value="<?= htmlspecialchars($expediente['iniciador'] ?? '') ?>" selected>
                                            <?= htmlspecialchars($expediente['iniciador'] ?? '') ?> (ACTUAL)
                                        </option>
                                    <?php endif; ?>
                                    <?php if (!empty($personas_fisicas)): ?>
                                        <optgroup label="Personas Físicas">
                                            <?php foreach ($personas_fisicas as $persona): ?>
                                                <option value="PF-<?= $persona['id'] ?>" <?= ($iniciador_id === 'PF-'.$persona['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($persona['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>

                                    <?php if (!empty($personas_juridicas)): ?>
                                        <optgroup label="Personas Jurídicas">
                                            <?php foreach ($personas_juridicas as $entidad): ?>
                                                <option value="PJ-<?= $entidad['id'] ?>" <?= ($iniciador_id === 'PJ-'.$entidad['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>

                                    <?php if (!empty($concejales)): ?>
                                        <optgroup label="Concejales">
                                            <?php foreach ($concejales as $concejal): ?>
                                                <option value="CO-<?= $concejal['id'] ?>" <?= ($iniciador_id === 'CO-'.$concejal['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($concejal['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un iniciador</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <a href="listar_expedientes.php" class="btn btn-secondary px-4">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                
                                <button type="button" class="btn btn-info text-white px-4" onclick="verHistorial(<?= htmlspecialchars($expediente['id'] ?? '0') ?>)">
                                    <i class="bi bi-clock-history"></i> Ver Historial
                                </button>
                            </div>
                            
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <script>
    // ... (El código JavaScript para Select2, Validación y Historial va aquí) ...
    // NOTA: Se ha corregido la sanitización en el script de Historial.

    document.addEventListener('DOMContentLoaded', function() {
        $('#iniciador').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Seleccione o busque un iniciador...', allowClear: true, language: 'es' });

        const form = document.querySelector('form');
        const extracto = document.getElementById('extracto');

        if (extracto) {
            extracto.addEventListener('input', function() {
                const caracteresActuales = this.value.length;
                const formText = this.nextElementSibling;
                if (formText) {
                    formText.textContent = `Caracteres: ${caracteresActuales} (sin límite)`;
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Desea guardar los cambios?',
                text: 'Verifique que los datos sean correctos',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        const params = new URLSearchParams(window.location.search);
        if (!params.has('id')) {
            Swal.fire({ title: 'Error', text: 'No se especificó un expediente para editar', icon: 'error', confirmButtonText: 'Volver' }).then(() => {
                window.location.href = 'listar_expedientes.php';
            });
        }
    });

    // Función verHistorial con sanitización del contenido JSON
    async function verHistorial(id) {
        try {
            Swal.fire({ title: 'Cargando historial...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
            const response = await fetch(`obtener_historial.php?id=${id}`);
            const resultado = await response.json();
            Swal.close();

            if (!resultado.success) { throw new Error(resultado.message); }
            if (!resultado.data || resultado.data.length === 0) {
                Swal.fire({ title: 'Sin cambios', text: 'Este expediente no tiene historial de cambios registrados', icon: 'info' });
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Ubicación Anterior</th>
                                <th>Nueva Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            resultado.data.forEach(cambio => {
                const fecha = new Date(cambio.fecha_cambio).toLocaleString('es-AR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
                
                // SANITIZACIÓN: Usar DOMParser para extraer el texto plano y prevenir XSS
                const parser = new DOMParser();
                const lugarAnterior = parser.parseFromString(cambio.lugar_anterior, 'text/html').body.textContent;
                const lugarNuevo = parser.parseFromString(cambio.lugar_nuevo, 'text/html').body.textContent;

                html += `
                    <tr>
                        <td>${fecha}</td>
                        <td>${lugarAnterior}</td>
                        <td>${lugarNuevo}</td>
                    </tr>
                `;
            });

            html += `</tbody></table></div>`;

            Swal.fire({
                title: 'Historial de Cambios', html: html, width: '800px', confirmButtonText: 'Cerrar', customClass: { container: 'historial-modal' }
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({ title: 'Error', text: 'No se pudo cargar el historial: ' + error.message, icon: 'error' });
        }
    }
    </script>

</body>
</html>