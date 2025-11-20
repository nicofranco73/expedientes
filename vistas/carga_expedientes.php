<<<<<<< HEAD
<?php 
// VISTA: carga_expediente_view.php
// Asume que las variables $personas_fisicas, $personas_juridicas, $concejales 
// y la lógica de SESIÓN ya fueron preparadas en el controlador (o están disponibles aquí).
=======
<?php
session_start();
require 'header.php';
require 'head.php';

try {
     // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consultar personas físicas - incluir DNI como campo separado
    $stmt = $db->query("SELECT id, 
                               dni, 
                               CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo 
                        FROM persona_fisica 
                        ORDER BY apellido, nombre");
    $personas_fisicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar personas jurídicas - incluir CUIT como campo separado
    $stmt = $db->query("SELECT id, 
                               cuit, 
                               CONCAT(razon_social, ' (', cuit, ')') as nombre_completo 
                        FROM persona_juri_entidad 
                        ORDER BY razon_social");
    $personas_juridicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar concejales con su historial de bloques
    $stmt = $db->query("
        SELECT c.id, 
               c.apellido, 
               c.nombre, 
               c.bloque as bloque_actual,
               CONCAT(c.apellido, ', ', c.nombre) as nombre_completo
        FROM concejales c 
        ORDER BY c.apellido, c.nombre
    ");
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener historial de bloques para cada concejal
    foreach ($concejales as &$concejal) {
        try {
            // Verificar si la tabla de historial existe
            $stmt = $db->prepare("SHOW TABLES LIKE 'concejal_bloques_historial'");
            $stmt->execute();
            $tabla_existe = $stmt->rowCount() > 0;
            
            if ($tabla_existe) {
                $stmt = $db->prepare("
                    SELECT nombre_bloque, es_actual, fecha_inicio, fecha_fin
                    FROM concejal_bloques_historial 
                    WHERE concejal_id = ? AND (eliminado IS NULL OR eliminado = FALSE)
                    ORDER BY es_actual DESC, fecha_inicio DESC
                ");
                $stmt->execute([$concejal['id']]);
                $bloques = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $bloques = [];
            }
            
            // Si no hay historial, usar el bloque actual
            if (empty($bloques) && $concejal['bloque_actual']) {
                $bloques = [[
                    'nombre_bloque' => $concejal['bloque_actual'],
                    'es_actual' => true,
                    'fecha_inicio' => null,
                    'fecha_fin' => null
                ]];
            }
            
            $concejal['bloques'] = $bloques;
        } catch (Exception $e) {
            // En caso de error, usar solo el bloque actual
            $concejal['bloques'] = [[
                'nombre_bloque' => $concejal['bloque_actual'],
                'es_actual' => true,
                'fecha_inicio' => null,
                'fecha_fin' => null
            ]];
        }
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar iniciadores: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Debug temporal para verificar datos
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<div style='background: #f8f9fa; padding: 20px; margin: 10px; border: 1px solid #dee2e6; border-radius: 5px;'>";
    echo "<h4>🔍 Debug - Estado de Concejales y Bloques</h4>";
    echo "<p><strong>Total concejales encontrados:</strong> " . count($concejales) . "</p>";
    
    if (!empty($concejales)) {
        echo "<details><summary>Ver detalles de concejales (clic para expandir)</summary>";
        foreach ($concejales as $index => $concejal) {
            echo "<div style='background: white; padding: 10px; margin: 5px 0; border-left: 4px solid #007bff;'>";
            echo "<strong>#{$index}: " . htmlspecialchars($concejal['nombre_completo']) . "</strong><br>";
            echo "ID: {$concejal['id']}, Bloque actual: " . htmlspecialchars($concejal['bloque_actual']) . "<br>";
            echo "Bloques disponibles: " . count($concejal['bloques']) . "<br>";
            
            if (!empty($concejal['bloques'])) {
                echo "<ul style='margin: 5px 0 0 20px;'>";
                foreach ($concejal['bloques'] as $bloque) {
                    echo "<li>" . htmlspecialchars($bloque['nombre_bloque']) . " - " . ($bloque['es_actual'] ? '🟢 ACTUAL' : '🔘 HISTÓRICO') . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<span style='color: orange;'>⚠️ Sin bloques</span>";
            }
            echo "</div>";
        }
        echo "</details>";
    }
    
    echo "<p><a href='setup_bloques.php' style='background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>🔧 Ejecutar Setup</a></p>";
    echo "<p><a href='?debug=0' style='background: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>❌ Ocultar Debug</a></p>";
    echo "</div>";
}
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
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
<<<<<<< HEAD
                                            name="extracto" 
                                            class="form-control" 
                                            rows="3" 
                                            placeholder="Ingrese un extracto"
                                            required></textarea>
                                <div class="form-text">Sin límite de caracteres.</div>
=======
                                          name="extracto" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Ingrese un extracto (puede escribir todo el texto que necesite, sin límites)"
                                          required></textarea>
                                <div class="form-text text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    ✅ Sin límite de caracteres - Puede escribir todo el texto que necesite
                                </div>
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
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

<<<<<<< HEAD
=======
                                        <!-- Campos ocultos para los valores -->
                                        <!-- Campo adicional para almacenar el bloque seleccionado del concejal -->
                                        <input type="hidden" id="bloque_concejal_seleccionado" name="bloque_concejal_seleccionado" value="">
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
                                        <select id="iniciador" name="iniciador" class="d-none" required>
                                            <option value="">Seleccione un iniciador...</option>
                                            
                                            <?php if (!empty($personas_fisicas)): ?>
                                                <optgroup label="👤 Personas Físicas">
                                                    <?php foreach ($personas_fisicas as $persona): ?>
                                                        <option value="PF-<?= $persona['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($persona['nombre_completo']) ?>"
                                                                data-tipo="Persona Física"
                                                                data-search="<?= strtolower(htmlspecialchars($persona['nombre_completo'] . ' ' . $persona['dni'])) ?>">
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
                                                                data-search="<?= strtolower(htmlspecialchars($entidad['nombre_completo'] . ' ' . $entidad['cuit'])) ?>">
                                                            <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endif; ?>

                                            <?php if (!empty($concejales)): ?>
                                                <optgroup label="🏛️ Concejales">
                                                    <?php foreach ($concejales as $concejal): ?>
                                                        <?php 
                                                            // Construir string de búsqueda con todos los bloques
                                                            $bloques_search = '';
                                                            if (!empty($concejal['bloques'])) {
                                                                $bloques_nombres = array_column($concejal['bloques'], 'nombre_bloque');
                                                                $bloques_search = ' ' . implode(' ', $bloques_nombres);
                                                            }
                                                            $search_completo = $concejal['nombre_completo'] . ' ' . $concejal['bloque_actual'] . $bloques_search;
                                                        ?>
                                                        <option value="CO-<?= $concejal['id'] ?>" 
                                                                data-nombre="<?= htmlspecialchars($concejal['nombre_completo']) ?>"
                                                                data-tipo="Concejal"
                                                                data-search="<?= strtolower(htmlspecialchars($search_completo)) ?>"
                                                                data-concejal-id="<?= $concejal['id'] ?>"
                                                                data-bloques='<?= htmlspecialchars(json_encode($concejal['bloques']), ENT_QUOTES) ?>'
                                                                data-bloque-actual="<?= htmlspecialchars($concejal['bloque_actual']) ?>">
                                                            <?= htmlspecialchars($concejal['nombre_completo']) ?> - <?= htmlspecialchars($concejal['bloque_actual']) ?>
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
                                                        <li><strong>Para concejales:</strong> Si tienen múltiples bloques, podrá elegir entre sus bloques actual e históricos</li>
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
    
<<<<<<< HEAD
=======
    <!-- Estilos adicionales para la búsqueda intuitiva -->
    <style>
        /* Estilos para el resaltado de búsqueda - MEJORADO */
        mark {
            background: linear-gradient(120deg, #ffeb3b 0%, #fff3cd 100%);
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: 700;
            color: #333;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 4px rgba(255, 235, 59, 0.3);
            transition: all 0.2s ease;
        }
        
        mark:hover {
            background: linear-gradient(120deg, #ffd54f 0%, #ffeb3b 100%);
            box-shadow: 0 4px 8px rgba(255, 235, 59, 0.5);
        }
        
        /* Card de iniciador mejorado */
        .card.border-success {
            box-shadow: 0 0.25rem 1rem rgba(25, 135, 84, 0.15);
            transition: all 0.3s ease;
            border-width: 2px;
        }
        
        .card.border-success:hover {
            box-shadow: 0 0.5rem 2rem rgba(25, 135, 84, 0.25);
            transform: translateY(-2px);
        }
        
        /* Header del card mejorado */
        .card-header.bg-success.bg-opacity-10 {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
            border-bottom: 2px solid rgba(25, 135, 84, 0.2);
        }
        
        /* Campo de búsqueda principal */
        .input-group-lg .form-control.fs-5 {
            font-size: 1.25rem !important;
            padding: 1rem 1.25rem;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .input-group-lg .form-control.fs-5:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            transform: scale(1.02);
        }
        
        .input-group-lg .input-group-text.bg-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            border: 2px solid #0d6efd;
            font-size: 1.25rem;
            padding: 1rem 1.25rem;
        }
        
        /* Resultados de búsqueda */
        #resultados_busqueda {
            animation: fadeInDown 0.3s ease-out;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .resultado-item {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }
        
        .resultado-item:hover {
            border-color: #0d6efd;
            background: #f8f9ff;
            transform: translateX(5px);
            box-shadow: 0 0.25rem 0.5rem rgba(13, 110, 253, 0.15);
        }
        
        .resultado-item:active {
            transform: translateX(3px) scale(0.98);
        }
        
        .tipo-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
        }
        
        /* Iniciador seleccionado */
        #iniciador_seleccionado {
            animation: slideInUp 0.4s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #iniciador_seleccionado .alert {
            border-width: 2px;
            box-shadow: 0 0.25rem 0.5rem rgba(25, 135, 84, 0.15);
        }
        
        /* Botones de acción */
        #limpiar_busqueda {
            border: 2px solid #6c757d;
            padding: 1rem 1.25rem;
            transition: all 0.3s ease;
        }
        
        #limpiar_busqueda:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            transform: scale(1.05);
        }
        
        #cambiar_iniciador {
            transition: all 0.3s ease;
        }
        
        #cambiar_iniciador:hover {
            transform: scale(1.05);
            box-shadow: 0 0.25rem 0.5rem rgba(25, 135, 84, 0.3);
        }
        
        /* Enlaces de acceso rápido */
        .btn-outline-primary, .btn-outline-info, .btn-outline-success {
            transition: all 0.3s ease;
            border-width: 2px;
        }
        
        .btn-outline-primary:hover, .btn-outline-info:hover, .btn-outline-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        /* Alerts mejorados */
        .alert {
            border-width: 2px;
            border-radius: 0.75rem;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #cff4fc 0%, #b6effb 100%);
            border-color: #0dcaf0;
            color: #055160;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1eddd 0%, #badbcc 100%);
            border-color: #198754;
            color: #0f5132;
        }
        
        /* Badges mejorados */
        .badge.bg-success-subtle {
            background: linear-gradient(135deg, #d1eddd 0%, #badbcc 100%) !important;
            color: #0f5132 !important;
            border: 1px solid #198754 !important;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 1rem;
        }
        
        /* Efectos de hover para elementos interactivos */
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: #86b7fe;
        }
        
        /* Animación de pulso para elementos importantes */
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }
        
        /* Responsividad mejorada */
        @media (max-width: 768px) {
            .input-group-lg .form-control.fs-5 {
                font-size: 1rem !important;
                padding: 0.75rem 1rem;
            }
            
            .input-group-lg .input-group-text {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .resultado-item {
                padding: 0.5rem 0.75rem;
            }
            
            .d-flex.flex-wrap.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }
            
            .btn-sm {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Estados de carga */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* ===== NUEVA INTERFAZ TIMELINE PARA BLOQUES DE CONCEJALES ===== */
        
        /* Header del concejal */
        .concejal-header {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
            border-radius: 15px;
            border: 2px solid rgba(25, 135, 84, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .concejal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(25, 135, 84, 0.05) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        .concejal-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .concejal-nombre {
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.4rem;
            position: relative;
            z-index: 2;
        }
        
        .concejal-subtitulo {
            font-size: 1rem;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Instrucciones del timeline */
        .timeline-instructions .alert {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
        }
        
        .bg-gradient {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(108, 117, 125, 0.05) 100%) !important;
        }
        
        /* Contenedor principal del timeline */
        .timeline-container {
            position: relative;
            padding: 1rem 0;
        }
        
        /* Items del timeline */
        .timeline-item {
            position: relative;
            display: flex;
            margin-bottom: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        /* Marcadores del timeline */
        .timeline-marker {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-right: 1rem;
            z-index: 2;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .marker-actual {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
        }
        
        .marker-historico {
            background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
            color: white;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.2);
        }
        
        /* Contenido del timeline */
        .timeline-content {
            flex-grow: 1;
            position: relative;
        }
        
        /* Tarjetas del timeline */
        .timeline-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .card-actual {
            background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
            border-color: rgba(25, 135, 84, 0.2);
        }
        
        .card-historico {
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
            border-color: rgba(108, 117, 125, 0.2);
        }
        
        /* Header personalizado de las tarjetas */
        .card-header-custom {
            padding: 1rem 1.25rem 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .bloque-titulo {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .bloque-estado {
            margin-bottom: 0.5rem;
        }
        
        /* Body personalizado de las tarjetas */
        .card-body-custom {
            padding: 0.75rem 1.25rem 1rem;
        }
        
        /* Badges personalizados */
        .badge-actual {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .badge-historico {
            background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Botones de selección */
        .btn-seleccionar {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-actual {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
        }
        
        .btn-actual:hover {
            background: linear-gradient(135deg, #146c43 0%, #1ba085 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4);
        }
        
        .btn-historico {
            background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }
        
        .btn-historico:hover {
            background: linear-gradient(135deg, #495057 0%, #868e96 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
        }
        
        /* Información del timeline */
        .timeline-info {
            margin-top: 0.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .periodo-texto {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .duracion-texto {
            font-weight: 500;
            color: #6c757d;
        }
        
        /* Alertas mini */
        .alert-mini {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            margin: 0;
            border: 1px solid transparent;
        }
        
        .alert-mini.alert-success {
            background: rgba(25, 135, 84, 0.1);
            color: #0f5132;
            border-color: rgba(25, 135, 84, 0.2);
        }
        
        .alert-mini.alert-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #41464b;
            border-color: rgba(108, 117, 125, 0.2);
        }
        
        /* Conectores del timeline */
        .timeline-connector {
            position: absolute;
            left: 19px;
            top: 40px;
            width: 2px;
            height: calc(100% - 20px);
            background: linear-gradient(180deg, rgba(108, 117, 125, 0.3) 0%, rgba(108, 117, 125, 0.1) 100%);
            z-index: 1;
        }
        
        /* Estados hover */
        .timeline-item.timeline-hover .timeline-card {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .timeline-item.timeline-hover .marker-actual {
            transform: scale(1.1);
            box-shadow: 0 0 0 5px rgba(25, 135, 84, 0.3);
        }
        
        .timeline-item.timeline-hover .marker-historico {
            transform: scale(1.1);
            box-shadow: 0 0 0 5px rgba(108, 117, 125, 0.3);
        }
        
        .timeline-item.timeline-hover .card-actual {
            border-color: rgba(25, 135, 84, 0.4);
            background: linear-gradient(135deg, #f0fff4 0%, #e8f8ed 100%);
        }
        
        .timeline-item.timeline-hover .card-historico {
            border-color: rgba(108, 117, 125, 0.4);
            background: linear-gradient(135deg, #f1f3f4 0%, #e9ecef 100%);
        }
        
        /* Efectos de selección activa */
        .timeline-item:active .timeline-card {
            transform: scale(0.98);
        }
        
        /* Animaciones de entrada */
        .timeline-item {
            animation: timelineSlideIn 0.5s ease-out forwards;
            opacity: 0;
            transform: translateX(-20px);
        }
        
        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }
        .timeline-item:nth-child(4) { animation-delay: 0.4s; }
        .timeline-item:nth-child(5) { animation-delay: 0.5s; }
        
        @keyframes timelineSlideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Responsividad */
        @media (max-width: 768px) {
            .timeline-marker {
                width: 35px;
                height: 35px;
                font-size: 1rem;
                margin-right: 0.75rem;
            }
            
            .card-header-custom, .card-body-custom {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .bloque-titulo {
                font-size: 1rem;
            }
            
            .btn-seleccionar {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .timeline-connector {
                left: 17px;
            }
        }
        
        .concejal-item {
            border-left: 4px solid #198754;
        }
        
        .concejal-item:hover {
            border-left-color: #146c43;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mejoras visuales adicionales */
        .fw-bold {
            font-weight: 600 !important;
        }
        
        .text-success {
            color: #198754 !important;
        }
        
        .border-success {
            border-color: #198754 !important;
        }
        
        /* Iconos con animación */
        .bi {
            transition: transform 0.2s ease;
        }
        
        .btn:hover .bi {
            transform: scale(1.1);
        }
        
        /* Shadow personalizado */
        .shadow-sm {
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.075) !important;
        }
        
        /* ===== ESTILOS PARA SWEETALERT DE BLOQUES ===== */
        
        /* Contenedor del SweetAlert */
        .swal-bloques-container {
            z-index: 10000;
        }
        
        /* Popup del SweetAlert */
        .swal-bloques-popup {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        /* Contenido HTML del SweetAlert */
        .swal-bloques-content {
            padding: 0;
        }
        
        /* Badges personalizados para el SweetAlert */
        .badge-success {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Opciones de bloques en el SweetAlert */
        .bloque-option {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .bloque-option:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
        }
        
        .bloque-option:active {
            transform: translateY(-1px) scale(0.98) !important;
        }
        
        /* Efectos de selección en SweetAlert */
        .bloque-option h6 {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .bloque-option .text-muted {
            color: #6c757d !important;
        }
        
        .bloque-option .text-muted strong {
            color: #495057;
        }
        
        /* Scrollbar personalizado para las opciones */
        .swal-bloques-content div::-webkit-scrollbar {
            width: 6px;
        }
        
        .swal-bloques-content div::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .swal-bloques-content div::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            border-radius: 10px;
        }
        
        .swal-bloques-content div::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #146c43 0%, #1ba085 100%);
        }

        /* Indicador visual del sistema listo */
        .sistema-listo {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
            animation: fadeInBounce 1s ease-out;
        }
        
        @keyframes fadeInBounce {
            0% { opacity: 0; transform: translateY(-20px) scale(0.8); }
            60% { opacity: 1; transform: translateY(5px) scale(1.05); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .concejal-item-debug {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border-left: 5px solid #ffc107 !important;
        }
        
        .concejal-item-debug:hover {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%) !important;
            transform: translateX(8px) !important;
        }
    </style>
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
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

<<<<<<< HEAD
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
=======
        console.log('Iniciadores cargados:', todasLasOpciones.length);

        // Verificar que SweetAlert esté disponible
        console.log('🍭 SweetAlert disponible:', typeof Swal !== 'undefined');
        if (typeof Swal !== 'undefined') {
            console.log('✅ SweetAlert2 cargado correctamente');
        } else {
            console.error('❌ SweetAlert2 NO está disponible');
        }

        // Función para buscar iniciadores - MEJORADA
        function buscarIniciadores(termino) {
            if (!termino || termino.length < 2) {
                ocultarResultados();
                return;
            }

            const terminoLimpio = termino.toLowerCase().trim();
            
            // Dividir el término en palabras para búsqueda más flexible
            const palabras = terminoLimpio.split(/\s+/);
            
            // Buscar coincidencias usando todas las palabras
            const resultados = todasLasOpciones.filter(opcion => {
                const textoCompleto = opcion.search;
                
                // Al menos una palabra debe coincidir
                return palabras.some(palabra => textoCompleto.includes(palabra));
            })
            // Ordenar por relevancia - prioritar coincidencias tempranas
            .sort((a, b) => {
                const posA = a.search.indexOf(terminoLimpio);
                const posB = b.search.indexOf(terminoLimpio);
                
                // Priorizar búsqueda exacta al inicio
                if (posA === 0 && posB !== 0) return -1;
                if (posA !== 0 && posB === 0) return 1;
                if (posA !== -1 && posB === -1) return -1;
                if (posA === -1 && posB !== -1) return 1;
                
                return posA - posB;
            });
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec

                if (termino.length < 2) {
                    resultadosDiv.style.display = 'none';
                    mensajeAyuda.style.display = 'block';
                    return;
                }

<<<<<<< HEAD
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
=======
        // Función para mostrar resultados
        function mostrarResultados(resultados, termino) {
            if (resultados.length === 0) {
                listaResultados.innerHTML = `
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-search"></i>
                        <p class="mb-0">No se encontraron iniciadores con: "<strong>${termino}</strong>"</p>
                        <small>Pruebe con otros términos de búsqueda</small>
                    </div>
                `;
            } else {
                listaResultados.innerHTML = resultados.map(resultado => {
                    const nombreResaltado = resaltarTermino(resultado.nombre, termino);
                    const iconoTipo = obtenerIconoTipo(resultado.tipo);
                    const colorTipo = obtenerColorTipo(resultado.tipo);
                    
                    // Si es un concejal, agregar información de bloques
                    if (resultado.tipo === 'Concejal' && resultado.element.dataset.bloques) {
                        const bloques = JSON.parse(resultado.element.dataset.bloques);
                        const concejalId = resultado.element.dataset.concejalId;
                        
                        // Debug: verificar datos del concejal
                        console.log('🏛️ DEBUG Concejal encontrado:', {
                            nombre: resultado.nombre,
                            concejalId: concejalId,
                            totalBloques: bloques.length,
                            bloques: bloques.map(b => `${b.nombre_bloque} (${b.es_actual ? 'ACTUAL' : 'HISTÓRICO'})`)
                        });
                        
                        return `
                            <div class="resultado-item concejal-item ${bloques.length > 1 ? 'concejal-item-debug' : ''}" data-value="${resultado.value}" data-nombre="${resultado.nombre}" data-tipo="${resultado.tipo}" data-concejal-id="${concejalId}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${nombreResaltado}</div>
                                        <small class="text-muted">
                                            <i class="${iconoTipo} me-1"></i>
                                            ${resultado.tipo} - ${bloques.length} bloque${bloques.length !== 1 ? 's' : ''} disponible${bloques.length !== 1 ? 's' : ''}
                                            ${bloques.length > 1 ? ' - 🎯 CLIC PARA ELEGIR' : ''}
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="tipo-badge bg-${colorTipo} text-white me-2">
                                            ${resultado.tipo}
                                        </span>
                                        ${bloques.length > 1 ? '<span class="badge bg-warning text-dark me-2">⚡ Multi-Bloque</span>' : ''}
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        return `
                            <div class="resultado-item" data-value="${resultado.value}" data-nombre="${resultado.nombre}" data-tipo="${resultado.tipo}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${nombreResaltado}</div>
                                        <small class="text-muted">
                                            <i class="${iconoTipo} me-1"></i>
                                            ${resultado.tipo}
                                        </small>
                                    </div>
                                    <span class="tipo-badge bg-${colorTipo} text-white">
                                        ${resultado.tipo}
                                    </span>
                                </div>
                            </div>
                        `;
                    }
                }).join('');

                // Agregar eventos de clic a los resultados
                document.querySelectorAll('.resultado-item').forEach(item => {
                    if (item.classList.contains('concejal-item')) {
                        item.addEventListener('click', function() {
                            console.log('🖱️ CLICK EN CONCEJAL DETECTADO:', this.dataset.nombre);
                            console.log('📊 Datos del elemento:', this.dataset);
                            mostrarBloquesDelConcejal(this);
                        });
                    } else {
                        item.addEventListener('click', function() {
                            seleccionarIniciador(
                                this.dataset.value,
                                this.dataset.nombre,
                                this.dataset.tipo
                            );
                        });
                    }
                });
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
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

<<<<<<< HEAD
                // 3. Establecer el valor real en el select oculto para el envío del formulario
                selectIniciador.value = opcion.value;
                selectIniciador.dispatchEvent(new Event('change')); // Para forzar validación si es necesario
            }
=======
        // Función para resaltar términos de búsqueda - MEJORADA
        function resaltarTermino(texto, termino) {
            if (!termino) return texto;
            
            // Dividir en palabras y resaltar cada una
            const palabras = termino.split(/\s+/);
            let textoResaltado = texto;
            
            palabras.forEach(palabra => {
                if (palabra.length > 0) {
                    const regex = new RegExp(`(${palabra})`, 'gi');
                    textoResaltado = textoResaltado.replace(regex, '<mark>$1</mark>');
                }
            });
            
            return textoResaltado;
        }

        // Función para obtener icono según tipo
        function obtenerIconoTipo(tipo) {
            switch(tipo) {
                case 'Persona Física': return 'bi bi-person-fill';
                case 'Persona Jurídica': return 'bi bi-building-fill';
                case 'Concejal': return 'bi bi-person-badge-fill';
                case 'Concejal (Bloque Actual)': return 'bi bi-star-fill';
                case 'Concejal (Bloque Histórico)': return 'bi bi-clock-history';
                default: return 'bi bi-person';
            }
        }

        // Función para obtener color según tipo
        function obtenerColorTipo(tipo) {
            switch(tipo) {
                case 'Persona Física': return 'primary';
                case 'Persona Jurídica': return 'info';
                case 'Concejal': return 'success';
                default: return 'secondary';
            }
        }

        // Función para mostrar bloques del concejal
        function mostrarBloquesDelConcejal(concejalElement) {
            console.log('🚀 INICIANDO mostrarBloquesDelConcejal');
            
            const concejalId = concejalElement.dataset.concejalId;
            const nombreConcejal = concejalElement.dataset.nombre;
            const opcionConcejal = Array.from(selectIniciador.options).find(opt => 
                opt.dataset.concejalId === concejalId
            );
            
            // Debug: mostrar información detallada
            console.log('🔍 DEBUG mostrarBloquesDelConcejal:', {
                concejalId: concejalId,
                nombreConcejal: nombreConcejal,
                opcionEncontrada: !!opcionConcejal,
                tieneDataBloques: opcionConcejal ? !!opcionConcejal.dataset.bloques : false,
                dataBloques: opcionConcejal ? opcionConcejal.dataset.bloques : 'No disponible'
            });
            
            // Continuar directamente con el procesamiento
            procesarBloquesDelConcejal(concejalId, nombreConcejal, opcionConcejal);
        }
        
        // Función separada para procesar bloques después de la confirmación
        function procesarBloquesDelConcejal(concejalId, nombreConcejal, opcionConcejal) {
            if (!opcionConcejal) {
                console.error('❌ No se encontró la opción del concejal con ID:', concejalId);
                Swal.fire({
                    title: 'Error',
                    text: `No se pudo encontrar la información del concejal con ID: ${concejalId}`,
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            if (!opcionConcejal.dataset.bloques) {
                console.error('❌ No hay datos de bloques para el concejal:', nombreConcejal);
                Swal.fire({
                    title: 'Sin bloques disponibles',
                    text: `El concejal ${nombreConcejal} no tiene bloques configurados.`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }
            
            let bloques;
            try {
                bloques = JSON.parse(opcionConcejal.dataset.bloques);
                console.log('✅ Bloques parseados correctamente:', bloques);
            } catch (error) {
                console.error('❌ Error al parsear bloques:', error, opcionConcejal.dataset.bloques);
                Swal.fire({
                    title: 'Error en datos de bloques',
                    text: `Error al procesar los bloques del concejal ${nombreConcejal}`,
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            // Si solo tiene un bloque, seleccionarlo directamente
            if (bloques.length === 1) {
                const bloque = bloques[0];
                seleccionarConcejalConBloque(concejalId, nombreConcejal, bloque.nombre_bloque, bloque.es_actual);
                return;
            }
            
            // Si tiene múltiples bloques, mostrar SweetAlert con opciones
            mostrarSweetAlertBloques(concejalId, nombreConcejal, bloques);
        }
        
        // Función para mostrar SweetAlert con selección de bloques
        function mostrarSweetAlertBloques(concejalId, nombreConcejal, bloques) {
            console.log('🎯 EJECUTANDO mostrarSweetAlertBloques:', {
                concejalId, nombreConcejal, totalBloques: bloques.length
            });
            // Ordenar bloques: actuales primero, luego históricos por fecha
            bloques.sort((a, b) => {
                if (a.es_actual && !b.es_actual) return -1;
                if (!a.es_actual && b.es_actual) return 1;
                if (a.fecha_inicio && b.fecha_inicio) {
                    return new Date(b.fecha_inicio) - new Date(a.fecha_inicio);
                }
                return 0;
            });
            
            // Crear HTML para las opciones
            let opcionesHTML = '';
            bloques.forEach((bloque, index) => {
                const esActual = bloque.es_actual;
                const fechaInicio = bloque.fecha_inicio ? new Date(bloque.fecha_inicio).toLocaleDateString('es-ES') : 'Sin fecha';
                const fechaFin = bloque.fecha_fin ? new Date(bloque.fecha_fin).toLocaleDateString('es-ES') : (esActual ? 'Actualidad' : 'Sin fecha fin');
                
                // Calcular duración si hay fechas
                let duracion = '';
                if (bloque.fecha_inicio) {
                    const inicio = new Date(bloque.fecha_inicio);
                    const fin = bloque.fecha_fin ? new Date(bloque.fecha_fin) : new Date();
                    const diffTime = Math.abs(fin - inicio);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const years = Math.floor(diffDays / 365);
                    const months = Math.floor((diffDays % 365) / 30);
                    
                    if (years > 0) {
                        duracion = `${years} año${years !== 1 ? 's' : ''}`;
                        if (months > 0) duracion += ` y ${months} mes${months !== 1 ? 'es' : ''}`;
                    } else if (months > 0) {
                        duracion = `${months} mes${months !== 1 ? 'es' : ''}`;
                    } else {
                        duracion = `${diffDays} día${diffDays !== 1 ? 's' : ''}`;
                    }
                }
                
                const badgeClass = esActual ? 'badge-success' : 'badge-secondary';
                const badgeText = esActual ? 'ACTUAL' : 'HISTÓRICO';
                const iconClass = esActual ? 'bi-star-fill' : 'bi-clock-history';
                
                opcionesHTML += `
                    <div class="bloque-option mb-3 p-3 border rounded cursor-pointer" 
                         data-concejal-id="${concejalId}" 
                         data-bloque="${bloque.nombre_bloque}" 
                         data-es-actual="${esActual}"
                         style="border: 2px solid ${esActual ? '#198754' : '#6c757d'}; 
                                background: ${esActual ? 'linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%)' : 'linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%)'};
                                cursor: pointer; 
                                transition: all 0.2s ease;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi ${iconClass} me-2 text-${esActual ? 'success' : 'secondary'}"></i>
                                    <h6 class="mb-0 fw-bold">${bloque.nombre_bloque}</h6>
                                    <span class="badge ${badgeClass} ms-2">${badgeText}</span>
                                </div>
                                <div class="text-muted small">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <strong>Período:</strong>&nbsp;
                                        <span>${fechaInicio} ${fechaFin !== 'Sin fecha fin' ? '→ ' + fechaFin : ''}</span>
                                    </div>
                                    ${duracion ? `
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        <strong>Duración:</strong>&nbsp;
                                        <span>${duracion}</span>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="text-end">
                                <i class="bi bi-chevron-right text-muted fs-5"></i>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            Swal.fire({
                title: `<div style="display: flex; align-items: center; gap: 10px;">
                            <i class="bi bi-person-badge-fill" style="color: #198754; font-size: 1.5rem;"></i>
                            <span>${nombreConcejal}</span>
                        </div>`,
                html: `
                    <div class="text-start">
                        <p class="mb-3 text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Seleccione el bloque político con el que iniciará el expediente:
                        </p>
                        <div style="max-height: 400px; overflow-y: auto;">
                            ${opcionesHTML}
                        </div>
                    </div>
                `,
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Cancelar',
                cancelButtonColor: '#6c757d',
                width: '600px',
                customClass: {
                    container: 'swal-bloques-container',
                    popup: 'swal-bloques-popup',
                    htmlContainer: 'swal-bloques-content'
                },
                didOpen: () => {
                    // Agregar eventos de clic a las opciones
                    document.querySelectorAll('.bloque-option').forEach(option => {
                        option.addEventListener('click', function() {
                            const concejalId = this.dataset.concejalId;
                            const nombreBloque = this.dataset.bloque;
                            const esActual = this.dataset.esActual === 'true';
                            
                            // Cerrar el SweetAlert
                            Swal.close();
                            
                            // Seleccionar el bloque
                            seleccionarConcejalConBloque(concejalId, nombreConcejal, nombreBloque, esActual);
                        });
                        
                        // Efectos hover
                        option.addEventListener('mouseenter', function() {
                            this.style.transform = 'translateY(-2px)';
                            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
                        });
                        
                        option.addEventListener('mouseleave', function() {
                            this.style.transform = 'translateY(0)';
                            this.style.boxShadow = 'none';
                        });
                    });
                }
            });
        }
        
        // Función para seleccionar concejal con bloque específico  
        function seleccionarConcejalConBloque(concejalId, nombreConcejal, nombreBloque, esActual) {
            const value = `CO-${concejalId}`;
            const nombreCompleto = `${nombreConcejal} - ${nombreBloque}`;
            const tipo = `Concejal${esActual ? ' (Bloque Actual)' : ' (Bloque Histórico)'}`;
            
            // Guardar el bloque seleccionado
            document.getElementById('bloque_concejal_seleccionado').value = nombreBloque;
            
            seleccionarIniciador(value, nombreCompleto, tipo);
        }

        // Función para seleccionar iniciador
        function seleccionarIniciador(value, nombre, tipo) {
            // Actualizar el select oculto
            selectIniciador.value = value;
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
            
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
<<<<<<< HEAD
        });
=======
            document.getElementById('bloque_concejal_seleccionado').value = '';
            iniciadorSeleccionado.style.display = 'none';
            mensajeAyuda.style.display = 'block';
            buscarIniciador.value = '';
            buscarIniciador.focus();
            ocultarResultados();
        }

        // Eventos del campo de búsqueda
        buscarIniciador.addEventListener('input', function() {
            const termino = this.value.trim();
            
            if (termino.length === 0) {
                ocultarResultados();
                return;
            }
            
            if (termino.length >= 2) {
                buscarIniciadores(termino);
            }
        });

        // Evento para limpiar búsqueda
        limpiarBusqueda.addEventListener('click', limpiarSeleccion);

        // Evento para cambiar iniciador
        cambiarIniciador.addEventListener('click', limpiarSeleccion);

        // Eventos de teclado
        buscarIniciador.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const primeraOpcion = document.querySelector('.resultado-item');
                if (primeraOpcion) {
                    primeraOpcion.click();
                }
            }
            
            if (e.key === 'Escape') {
                ocultarResultados();
                this.blur();
            }
        });

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!buscarIniciador.contains(e.target) && !resultadosBusqueda.contains(e.target)) {
                if (!selectIniciador.value) {
                    ocultarResultados();
                }
            }
        });

        // Validación del formulario mejorada
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            // Debug: Mostrar valores antes de enviar
            console.log('=== ENVÍO DE FORMULARIO ===');
            console.log('Valor del select iniciador:', selectIniciador.value);
            console.log('Bloque concejal seleccionado:', document.getElementById('bloque_concejal_seleccionado').value);
            
            if (!selectIniciador.value) {
                e.preventDefault();
                Swal.fire({
                    title: 'Iniciador requerido',
                    text: 'Debe seleccionar un iniciador para el expediente',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#198754'
                });
                
                // Enfocar el campo de búsqueda y agregar clase de error
                buscarIniciador.focus();
                buscarIniciador.classList.add('is-invalid');
                setTimeout(() => {
                    buscarIniciador.classList.remove('is-invalid');
                }, 3000);
                
                return false;
            }
            
            console.log('Formulario validado, enviando...');
        });

        // Auto-foco en el campo de búsqueda
        setTimeout(() => {
            buscarIniciador.focus();
        }, 500);

        // Mensaje de bienvenida si no hay iniciadores
        if (todasLasOpciones.length === 0) {
            listaResultados.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                    <h6 class="mt-3">No hay iniciadores disponibles</h6>
                    <p class="text-muted">Debe agregar al menos un iniciador antes de crear expedientes.</p>
                    <div class="mt-3">
                        <a href="carga_persona_fisica.php" class="btn btn-primary btn-sm me-2" target="_blank">
                            <i class="bi bi-person-plus"></i> Agregar Persona
                        </a>
                        <a href="carga_concejal.php" class="btn btn-success btn-sm" target="_blank">
                            <i class="bi bi-person-badge"></i> Agregar Concejal
                        </a>
                    </div>
                </div>
            `;
            mostrarContenedorResultados();
            mensajeAyuda.style.display = 'none';
        }
    });
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec
    </script>


</body>
</html>