<?php 
// VISTA: carga_expediente_view.php
// Asume que las variables $personas_fisicas, $personas_juridicas, $concejales 
// y la lógica de SESIÓN ya fueron preparadas en el controlador (o están disponibles aquí).
?>
<!DOCTYPE html>
<html lang="es">
<head>
    </head>
<body>
    
    <div class="container-fluid">
        <div class="row">

            <?php require '../vistas/sidebar.php'; ?>
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <div class="main-box carga">
                    <h1 class="titulo-principal mb-4 text-center">Carga de Expediente</h1>
                    
                    <?php
                    // Lógica de presentación de mensajes (SweetAlert2)
                    // NOTA: Se evita el session_start() porque ya lo hizo el controlador.
                    if (isset($_SESSION['mensaje'])) {
                        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
                        $expediente_id = $_SESSION['expediente_id'] ?? null;
                        
                        // Convertir tipo de Bootstrap a SweetAlert2
                        $icon = match($tipo) {
                            'success' => 'success',
                            'danger' => 'error',
                            'warning' => 'warning',
                            default => 'info'
                        };
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                <?php if ($tipo === 'success' && $expediente_id): ?>
                                Swal.fire({
                                    title: '<?= htmlspecialchars($_SESSION['mensaje']) ?>',
                                    icon: '<?= $icon ?>',
                                    html: '<p class="mb-3">El expediente se ha guardado correctamente en el sistema.</p>' +
                                          '<div class="d-grid gap-2">' +
                                          '<button type="button" class="btn btn-primary btn-lg" onclick="generarPDF(<?= $expediente_id ?>)">' +
                                          '<i class="bi bi-file-earmark-pdf"></i> Descargar Comprobante PDF' +
                                          '</button>' +
                                          '</div>',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Continuar',
                                    confirmButtonColor: '#0d6efd',
                                    allowOutsideClick: false,
                                    width: '500px'
                                });
                                <?php else: ?>
                                Swal.fire({
                                    title: '<?= htmlspecialchars($_SESSION['mensaje']) ?>',
                                    icon: '<?= $icon ?>',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#0d6efd'
                                });
                                <?php endif; ?>
                            });
                            
                            function generarPDF(expedienteId) {
                                window.open('pdf_auto_descarga.php?id=' + expedienteId, '_blank');
                            }
                        </script>
                        <?php
                        // Limpieza de sesión: Aún es lógica, pero se hace después de usarla en la vista.
                        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje'], $_SESSION['expediente_id']);
                    }
                    ?>
                    
                    
                    <form action="procesar_carga_expedientes.php" method="post" autocomplete="off">
                        <div class="row g-7 mb-4">
                            <div class="col-md-4 mb-2">
                                <label for="numero" class="form-label">Número *</label>
                                <input type="text"
                                    id="numero"
                                    name="numero"
                                    class="form-control"
                                    placeholder="Ej: 0001, 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos (se permiten ceros a la izquierda)"
                                    required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="letra" class="form-label">Letra *</label>
                                <select id="letra"
                                    name="letra"
                                    class="form-select"
                                    required>
                                    <option value="">Elige una letra</option>
                                    <?php foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $l): ?>
                                        <option value="<?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="folio" class="form-label">Folio *</label>
                                <input type="text"
                                    id="folio"
                                    name="folio"
                                    class="form-control"
                                    placeholder="Ej: 0001, 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos (se permiten ceros a la izquierda)"
                                    required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="libro" class="form-label">Libro *</label>
                                <input type="text"
                                    id="libro"
                                    name="libro"
                                    class="form-control"
                                    placeholder="Ej: 0001, 1234"
                                    pattern="[0-9]{1,6}"
                                    maxlength="6"
                                    title="Solo números, máximo 6 dígitos (se permiten ceros a la izquierda)"
                                    required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="anio" class="form-label">Año *</label>
                                <select id="anio" name="anio" class="form-select" required>
                                    <option value="">Elige un año</option>
                                    <?php for ($y = 1973; $y <= 2030; $y++): ?>
                                        <option value="<?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($y, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="fecha_hora_ingreso" class="form-label">Fecha y Hora de Ingreso *</label>
                                <input type="datetime-local" id="fecha_hora_ingreso" name="fecha_hora_ingreso" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label for="lugar" class="form-label">Lugar *</label>
                                <select id="lugar" name="lugar" class="form-select" required>
                                    <option value="Mesa de Entrada">Mesa de Entrada</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un lugar</div>
                            </div>


                            <div class="col-12 mb-2">
                                <label for="extracto" class="form-label">Extracto *</label>
                                <textarea id="extracto" 
                                            name="extracto" 
                                            class="form-control" 
                                            rows="3" 
                                            placeholder="Ingrese un extracto"
                                            required></textarea>
                                <div class="form-text">Sin límite de caracteres.</div>
                                <div class="invalid-feedback">Por favor ingrese un extracto</div>
                            </div>

                            <div class="col-12 mb-4">
                                <div class="card border-success shadow-sm">
                                    <div class="card-header bg-success bg-opacity-10 border-bottom-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="mb-0 text-success fw-bold">
                                                <i class="bi bi-person-plus-fill me-2"></i>
                                                ¿Quién inicia este expediente?
                                            </h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-asterisk"></i> Obligatorio
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label for="buscar_iniciador" class="form-label fw-bold mb-3">
                                                <i class="bi bi-search me-2 text-primary"></i>
                                                Buscar Iniciador
                                            </label>
                                            <div class="input-group input-group-lg shadow-sm">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" 
                                                        id="buscar_iniciador" 
                                                        class="form-control fs-5" 
                                                        placeholder="Escriba el nombre, DNI, CUIT o bloque del iniciador..."
                                                        autocomplete="off">
                                                <button type="button" 
                                                        id="limpiar_busqueda" 
                                                        class="btn btn-outline-secondary"
                                                        title="Limpiar búsqueda">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                            
                                            <div id="resultados_busqueda" class="mt-3" style="display: none;">
                                                <div class="border rounded-3 bg-light p-3">
                                                    <h6 class="text-muted mb-2">
                                                        <i class="bi bi-list-ul me-1"></i>
                                                        Resultados encontrados:
                                                    </h6>
                                                    <div id="lista_resultados"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="iniciador_seleccionado" class="mb-3" style="display: none;">
                                            <label class="form-label fw-bold text-success">
                                                <i class="bi bi-check-circle-fill me-2"></i>
                                                Iniciador Seleccionado
                                            </label>
                                            <div class="alert alert-success border-success d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-person-check-fill me-2"></i>
                                                    <span id="nombre_seleccionado" class="fw-bold"></span>
                                                    <small id="tipo_seleccionado" class="text-muted ms-2"></small>
                                                </div>
                                                <button type="button" 
                                                        id="cambiar_iniciador" 
                                                        class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-pencil"></i> Cambiar
                                                </button>
                                            </div>
                                        </div>

                                        <select id="iniciador" name="iniciador" class="d-none" required>
                                            <option value="">Seleccione un iniciador...</option>
                                            
                                            <?php if (!empty($personas_fisicas)): ?>
                                                <optgroup label="👤 Personas Físicas">
                                                    <?php foreach ($personas_fisicas as $persona): ?>
                                                        <option value="PF-<?= $persona['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($persona['nombre_completo']) ?>"
                                                                data-tipo="Persona Física"
                                                                data-search="<?= strtolower(htmlspecialchars($persona['nombre_completo'])) ?>">
                                                            <?= htmlspecialchars($persona['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>

                                            <?php if (!empty($personas_juridicas)): ?>
                                                <optgroup label="🏢 Personas Jurídicas">
                                                    <?php foreach ($personas_juridicas as $entidad): ?>
                                                        <option value="PJ-<?= $entidad['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($entidad['nombre_completo']) ?>"
                                                                data-tipo="Persona Jurídica"
                                                                data-search="<?= strtolower(htmlspecialchars($entidad['nombre_completo'])) ?>">
                                                            <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>

                                            <?php if (!empty($concejales)): ?>
                                                <optgroup label="🏛️ Concejales">
                                                    <?php foreach ($concejales as $concejal): ?>
                                                        <option value="CO-<?= $concejal['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($concejal['nombre_completo']) ?>"
                                                                data-tipo="Concejal"
                                                                data-search="<?= strtolower(htmlspecialchars($concejal['nombre_completo'])) ?>">
                                                            <?= htmlspecialchars($concejal['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>
                                        </select>

                                        <div id="mensaje_ayuda" class="alert alert-info border-info">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-info-circle-fill me-3 mt-1 text-info"></i>
                                                <div>
                                                    <h6 class="mb-2">💡 ¿Cómo buscar?</h6>
                                                    <ul class="mb-0 small">
                                                        <li><strong>Por nombre:</strong> "Juan", "María", "González"</li>
                                                        <li><strong>Por documento:</strong> "12345678", "20-12345678-9"</li>
                                                        <li><strong>Por bloque:</strong> "Frente", "Partido", "Bloque"</li>
                                                    </ul>
                                                    <p class="mb-0 mt-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-lightbulb"></i>
                                                            Tip: Escriba solo unas pocas letras y aparecerán las coincidencias
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <h6 class="text-muted mb-3">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                ¿No encuentra al iniciador?
                                            </h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="carga_iniciador.php" 
                                                    class="btn btn-outline-primary btn-sm" 
                                                    target="_blank"
                                                    title="Se abrirá en una nueva ventana">
                                                    <i class="bi bi-person-plus"></i>
                                                    Agregar Persona Física
                                                </a>
                                                <a href="carga_persona_juri_entidad.php" 
                                                    class="btn btn-outline-info btn-sm" 
                                                    target="_blank"
                                                    title="Se abrirá en una nueva ventana">
                                                    <i class="bi bi-building-add"></i>
                                                    Agregar Persona Jurídica
                                                </a>
                                                <a href="carga_concejal.php" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    target="_blank"
                                                    title="Se abrirá en una nueva ventana">
                                                    <i class="bi bi-person-badge"></i>
                                                    Agregar Concejal
                                                </a>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="bi bi-info-circle"></i>
                                                Después de agregar un nuevo iniciador, actualice esta página para verlo en la lista
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="reset" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-eraser"></i> Limpiar Campos
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputBusqueda = document.getElementById('buscar_iniciador');
            const selectIniciador = document.getElementById('iniciador');
            const resultadosDiv = document.getElementById('resultados_busqueda');
            const listaResultados = document.getElementById('lista_resultados');
            const seleccionDiv = document.getElementById('iniciador_seleccionado');
            const nombreSeleccionado = document.getElementById('nombre_seleccionado');
            const tipoSeleccionado = document.getElementById('tipo_seleccionado');
            const btnLimpiar = document.getElementById('limpiar_busqueda');
            const btnCambiar = document.getElementById('cambiar_iniciador');
            const mensajeAyuda = document.getElementById('mensaje_ayuda');

            // Mapea todas las opciones a un array para búsqueda rápida
            const opciones = Array.from(selectIniciador.querySelectorAll('option:not([value=""])')).map(option => ({
                value: option.value,
                nombre: option.dataset.nombre,
                tipo: option.dataset.tipo,
                search: option.dataset.search 
            }));

            // Función de resaltado
            function highlight(text, search) {
                if (!search) return text;
                const regex = new RegExp(`(${search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi');
                return text.replace(regex, (match) => `<mark>${match}</mark>`);
            }

            // Función de filtrado y renderizado
            function buscarIniciador(e) {
                const termino = inputBusqueda.value.toLowerCase().trim();
                listaResultados.innerHTML = '';

                if (termino.length < 2) {
                    resultadosDiv.style.display = 'none';
                    mensajeAyuda.style.display = 'block';
                    return;
                }

                mensajeAyuda.style.display = 'none';

                const resultadosFiltrados = opciones.filter(op => op.search.includes(termino));

                if (resultadosFiltrados.length === 0) {
                    listaResultados.innerHTML = '<p class="text-center text-danger mb-0">No se encontraron coincidencias.</p>';
                } else {
                    resultadosFiltrados.forEach(opcion => {
                        const div = document.createElement('div');
                        div.className = 'resultado-item d-flex justify-content-between align-items-center'; // Quitamos 'shadow-sm' si no es de Bootstrap
                        div.setAttribute('data-value', opcion.value);
                        div.innerHTML = `
                            <div>
                                <i class="bi bi-person-circle me-2"></i>
                                ${highlight(opcion.nombre, termino)}
                            </div>
                            <span class="tipo-badge badge rounded-pill bg-primary-subtle text-primary">${opcion.tipo}</span>
                        `;
                        
                        div.addEventListener('click', () => seleccionarIniciador(opcion));
                        listaResultados.appendChild(div);
                    });
                }
                resultadosDiv.style.display = 'block';
            }
            
            // Función de selección
            function seleccionarIniciador(opcion) {
                // 1. Ocultar la búsqueda y mostrar la selección
                resultadosDiv.style.display = 'none';
                inputBusqueda.style.display = 'none';
                btnLimpiar.style.display = 'none';
                seleccionDiv.style.display = 'block';
                mensajeAyuda.style.display = 'none';

                // 2. Mostrar datos en la interfaz
                nombreSeleccionado.textContent = opcion.nombre;
                tipoSeleccionado.textContent = `(${opcion.tipo})`;

                // 3. Establecer el valor real en el select oculto para el envío del formulario
                selectIniciador.value = opcion.value;
                selectIniciador.dispatchEvent(new Event('change')); // Para forzar validación si es necesario
            }
            
            // Función para resetear
            function resetearIniciador() {
                // 1. Resetear el campo de búsqueda
                inputBusqueda.value = '';
                inputBusqueda.style.display = 'block';
                btnLimpiar.style.display = 'block';
                
                // 2. Ocultar resultados y selección
                resultadosDiv.style.display = 'none';
                seleccionDiv.style.display = 'none';
                mensajeAyuda.style.display = 'block';

                // 3. Resetear el valor del select oculto
                selectIniciador.value = '';
                selectIniciador.dispatchEvent(new Event('change'));
            }

            // Event Listeners
            inputBusqueda.addEventListener('input', buscarIniciador);
            btnLimpiar.addEventListener('click', resetearIniciador);
            btnCambiar.addEventListener('click', resetearIniciador);

            // Inicializar el estado (asegura que el select oculto esté vacío al inicio)
            selectIniciador.value = '';
        });
    </script>


</body>
</html>