<?php
session_start();

// Agregar al inicio del archivo, después de session_start()
error_log('ID recibido: ' . print_r($_GET, true));

// Validar que el ID exista y sea un número
// Esta página requiere un parámetro ?id=X en la URL
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id) {
    // Redireccionar sin mensaje de error para evitar confusión
    header("Location: listar_expedientes.php");
    exit;
}

try {
   // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consultar expediente
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        $_SESSION['mensaje'] = "Expediente no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: listar_expedientes.php");
        exit;
    }

    // Consultar iniciadores
    $db_iniciadores = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $db_iniciadores->query("SELECT id, CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo FROM persona_fisica ORDER BY apellido, nombre");
    $personas_fisicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db_iniciadores->query("SELECT id, CONCAT(razon_social, ' (', cuit, ')') as nombre_completo FROM persona_juri_entidad ORDER BY razon_social");
    $personas_juridicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db_iniciadores->query("SELECT id, CONCAT(apellido, ', ', nombre, ' - ', bloque) as nombre_completo FROM concejales ORDER BY apellido, nombre");
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el ID del iniciador original - versión mejorada
    $iniciador_id = '';
    $debug_info = [
        'expediente_iniciador' => $expediente['iniciador'],
        'personas_fisicas_count' => count($personas_fisicas),
        'personas_juridicas_count' => count($personas_juridicas),
        'concejales_count' => count($concejales)
    ];
    
    // Función para normalizar strings para comparación
    function normalizar_string($str) {
        return trim(strtolower(preg_replace('/\s+/', ' ', $str)));
    }
    
    $iniciador_normalizado = normalizar_string($expediente['iniciador']);
    
    // Buscar en personas físicas
    foreach ($personas_fisicas as $pf) {
        if (normalizar_string($pf['nombre_completo']) === $iniciador_normalizado) {
            $iniciador_id = 'PF-' . $pf['id'];
            $debug_info['found_in'] = 'personas_fisicas';
            $debug_info['found_id'] = $iniciador_id;
            $debug_info['found_name'] = $pf['nombre_completo'];
            break;
        }
    }
    
    // Buscar en personas jurídicas
    if (!$iniciador_id) {
        foreach ($personas_juridicas as $pj) {
            if (normalizar_string($pj['nombre_completo']) === $iniciador_normalizado) {
                $iniciador_id = 'PJ-' . $pj['id'];
                $debug_info['found_in'] = 'personas_juridicas';
                $debug_info['found_id'] = $iniciador_id;
                $debug_info['found_name'] = $pj['nombre_completo'];
                break;
            }
        }
    }
    
    // Buscar en concejales
    if (!$iniciador_id) {
        foreach ($concejales as $co) {
            if (normalizar_string($co['nombre_completo']) === $iniciador_normalizado) {
                $iniciador_id = 'CO-' . $co['id'];
                $debug_info['found_in'] = 'concejales';
                $debug_info['found_id'] = $iniciador_id;
                $debug_info['found_name'] = $co['nombre_completo'];
                break;
            }
        }
    }
    
    // Si no se encuentra, intentar búsqueda por partes (apellido, nombre, etc.)
    if (!$iniciador_id) {
        $debug_info['fallback_search'] = true;
        
        // Intentar búsqueda parcial en personas físicas
        foreach ($personas_fisicas as $pf) {
            if (strpos($iniciador_normalizado, normalizar_string($pf['nombre_completo'])) !== false ||
                strpos(normalizar_string($pf['nombre_completo']), $iniciador_normalizado) !== false) {
                $iniciador_id = 'PF-' . $pf['id'];
                $debug_info['found_in'] = 'personas_fisicas_partial';
                $debug_info['found_id'] = $iniciador_id;
                $debug_info['found_name'] = $pf['nombre_completo'];
                break;
            }
        }
        
        // Intentar búsqueda parcial en concejales
        if (!$iniciador_id) {
            foreach ($concejales as $co) {
                if (strpos($iniciador_normalizado, normalizar_string($co['nombre_completo'])) !== false ||
                    strpos(normalizar_string($co['nombre_completo']), $iniciador_normalizado) !== false) {
                    $iniciador_id = 'CO-' . $co['id'];
                    $debug_info['found_in'] = 'concejales_partial';
                    $debug_info['found_id'] = $iniciador_id;
                    $debug_info['found_name'] = $co['nombre_completo'];
                    break;
                }
            }
        }
    }
    
    // Log de debugging (solo en desarrollo)
    error_log('DEBUG INICIADOR: ' . print_r($debug_info, true));
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar el expediente: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carga de Expediente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="/publico/css/estilos.css">
    <style>
.historial-modal .swal2-html-container {
    margin: 1em 0;
}

.historial-modal .table {
    margin-bottom: 0;
}

.historial-modal .table-responsive {
    max-height: 400px;
    overflow-y: auto;
}

.historial-modal th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 1;
}

.historial-modal td {
    vertical-align: middle;
}

input[readonly] {
    background-color: #e9ecef;
    cursor: not-allowed;
}
</style>
</head>

<body>
    <!-- HEADER CON LOGO (igual que dashboard) -->
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


            <!-- Sidebar -->
            <?php require '../vistas/sidebar.php'; ?>
            <!-- Sidebar -->


            <!-- Main Content -->
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <div class="main-box carga">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="titulo-principal text-center">Actualizar Expediente</h1>
                        <a href="listar_expedientes.php" class="btn btn-primary px-4">
                            <i class="bi bi-list-ul"></i> Listar Expedientes
                        </a>
                    </div>
                    <?php
                    session_start();

                    if (isset($_SESSION['mensaje'])) {
                        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
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
                                Swal.fire({
                                    title: '<?= htmlspecialchars($_SESSION['mensaje']) ?>',
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
                            <!-- Número -->
                            <div class="col-md-3">
                                <label for="numero" class="form-label">Número *</label>
                                <input type="text" 
                                       id="numero" 
                                       name="numero" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($expediente['numero'] ?? '') ?>" 
                                       required
                                       pattern="[0-9]{1,6}"
                                       maxlength="6"
                                       title="Solo números, máximo 6 dígitos">
                                <div class="invalid-feedback">Ingrese un número válido (1-6 dígitos)</div>
                            </div>

                            <!-- Letra -->
                            <div class="col-md-3">
                                <label for="letra" class="form-label">Letra *</label>
                                <select id="letra" 
                                        name="letra" 
                                        class="form-select" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $l): ?>
                                        <option value="<?= $l ?>" <?= (($expediente['letra'] ?? '') === $l) ? 'selected' : '' ?>>
                                            <?= $l ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccione una letra</div>
                            </div>

                            <!-- Folio -->
                            <div class="col-md-3">
                                <label for="folio" class="form-label">Folio *</label>
                                <input type="text" 
                                       id="folio" 
                                       name="folio" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($expediente['folio'] ?? '') ?>" 
                                       required
                                       pattern="[0-9]{1,6}"
                                       maxlength="6"
                                       title="Solo números, máximo 6 dígitos">
                                <div class="invalid-feedback">Ingrese un folio válido (1-6 dígitos)</div>
                            </div>

                            <!-- Año -->
                            <div class="col-md-3">
                                <label for="anio" class="form-label">Año *</label>
                                <select id="anio" 
                                        name="anio" 
                                        class="form-select" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    <?php for ($y = 2030; $y >= 1973; $y--): ?>
                                        <option value="<?= $y ?>" <?= (($expediente['anio'] ?? '') == $y) ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <div class="invalid-feedback">Seleccione un año</div>
                            </div>

                            <!-- Lugar -->
                            <div class="col-md-6">
                                <label for="lugar" class="form-label">Lugar actual *</label>
                                <input type="text" 
                                       id="lugar" 
                                       name="lugar" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($expediente['lugar'] ?? '') ?>" 
                                       required
                                       maxlength="50">
                                <div class="invalid-feedback">Ingrese el lugar actual del expediente</div>
                            </div>

                            <!-- Extracto -->
                            <div class="col-12">
                                <label for="extracto" class="form-label">Extracto</label>
                                <textarea id="extracto" 
                                          name="extracto" 
                                          class="form-control" 
                                          rows="3"><?= htmlspecialchars($expediente['extracto'] ?? '') ?></textarea>
                                <div class="form-text">Sin límite de caracteres (opcional)</div>
                            </div>

                            <!-- Iniciador -->
                            <div class="col-12 mb-2">
                                <label for="iniciador" class="form-label">Iniciador</label>
                                <?php if (!$iniciador_id): ?>
                                    <div class="alert alert-warning mb-2">
                                        <small><strong>Iniciador actual:</strong> <?= htmlspecialchars($expediente['iniciador']) ?></small><br>
                                        <small><em>No se pudo encontrar en la base de datos de iniciadores. Seleccione uno nuevo o verifique los datos.</em></small>
                                    </div>
                                <?php endif; ?>
                                <select id="iniciador" name="iniciador" class="form-select" required>
                                    <option value="">Seleccione un iniciador...</option>
                                    <?php if (!$iniciador_id): ?>
                                        <option value="<?= htmlspecialchars($expediente['iniciador']) ?>" selected>
                                            <?= htmlspecialchars($expediente['iniciador']) ?> (ACTUAL)
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
                                
                               <!-- <button type="button" class="btn btn-info text-white px-4" onclick="verHistorial(<?= $expediente['id'] ?>)">
                                    <i class="bi bi-clock-history"></i> Ver Historial
                                </button> -->

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

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 en el campo iniciador
        $('#iniciador').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccione o busque un iniciador...',
            allowClear: true,
            language: 'es'
        });
    });
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const extracto = document.getElementById('extracto');

    // Contador de caracteres sin límite
    if (extracto) {
        extracto.addEventListener('input', function() {
            const caracteresActuales = this.value.length;
            const formText = this.nextElementSibling;
            if (formText) {
                formText.textContent = `Caracteres: ${caracteresActuales} (sin límite)`;
            }
        });
    }

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar campos requeridos
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            Swal.fire({
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos requeridos',
                icon: 'warning',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        // Obtener valores para mostrar en confirmación
        const numero = document.getElementById('numero').value;
        const letra = document.getElementById('letra').value;
        const folio = document.getElementById('folio').value;
        const anio = document.getElementById('anio').value;

        // Confirmar envío
        Swal.fire({
            title: '¿Desea guardar los cambios?',
            html: `
                <div class="text-start">
                    <p><strong>Expediente:</strong> ${numero}/${letra}/${folio}/${anio}</p>
                    <p class="text-muted">Verifique que los datos sean correctos antes de guardar</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-save"></i> Sí, guardar',
            cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

// Verificar que tengamos un ID en la URL
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    if (!params.has('id')) {
        Swal.fire({
            title: 'Error',
            text: 'No se especificó un expediente para editar',
            icon: 'error',
            confirmButtonText: 'Volver',
        }).then(() => {
            window.location.href = 'listar_expedientes.php';
        });
    }
});
</script>


<!-- Ver historial de cambios -->
<script>
async function verHistorial(id) {
    try {
        // Mostrar loader
        Swal.fire({
            title: 'Cargando historial...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false
        });

        // Hacer la petición
        const response = await fetch(`obtener_historial.php?id=${id}`);
        const resultado = await response.json();

        // Cerrar loader
        Swal.close();

        if (!resultado.success) {
            throw new Error(resultado.message);
        }

        if (!resultado.data || resultado.data.length === 0) {
            Swal.fire({
                title: 'Sin cambios',
                text: 'Este expediente no tiene historial de cambios registrados',
                icon: 'info'
            });
            return;
        }

        // Crear tabla HTML
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

        // Agregar filas
        resultado.data.forEach(cambio => {
            const fecha = new Date(cambio.fecha_cambio).toLocaleString('es-AR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });

            html += `
                <tr>
                    <td>${fecha}</td>
                    <td>${cambio.lugar_anterior}</td>
                    <td>${cambio.lugar_nuevo}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        // Mostrar modal
        Swal.fire({
            title: 'Historial de Cambios',
            html: html,
            width: '800px',
            confirmButtonText: 'Cerrar',
            customClass: {
                container: 'historial-modal'
            }
        });

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo cargar el historial: ' + error.message,
            icon: 'error'
        });
    }
}
</script>

</html>
</body>
</body>

</html>