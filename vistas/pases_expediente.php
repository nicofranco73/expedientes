<?php
session_start();

// Headers para evitar cache en páginas dinámicas
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Validar ID
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['mensaje'] = "ID de expediente inválido";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}

try {
    // Conectar a la base de datos (usar base local)
    require_once '../db/connection.php';
    $db = $pdo;

    // Obtener datos del expediente
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("Expediente no encontrado");
    }

    // Obtener historial de pases
    $stmt = $db->prepare("
        SELECT 
            hl.id,
            hl.fecha_cambio,
            DATE_FORMAT(hl.fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
            hl.lugar_anterior,
            hl.lugar_nuevo,
            hl.tipo_movimiento,
            hl.numero_acta,
            hl.usuario_id,
            TIMESTAMPDIFF(HOUR, e.fecha_hora_ingreso, hl.fecha_cambio) as horas_desde_ingreso
        FROM historial_lugares hl
        JOIN expedientes e ON hl.expediente_id = e.id
        WHERE hl.expediente_id = ?
        ORDER BY hl.fecha_cambio DESC
    ");
    $stmt->execute([$id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug temporal - comentar en producción
    error_log("Expediente ID: $id");
    error_log("Registros encontrados en historial: " . count($historial));
    if (!empty($historial)) {
        error_log("Primer registro: " . json_encode($historial[0]));
    }

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pases de Expediente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/publico/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .table-primary {
            --bs-table-bg: rgba(13, 110, 253, 0.1);
            font-weight: 500;
        }

        .badge {
            margin-left: 0.5rem;
        }

        .text-muted {
            color: #6c757d !important;
        }

        /* Estilos para ordenamiento de tabla */
        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 20px !important;
        }

        .sortable:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .sortable::after {
            content: "⇅";
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 12px;
        }

        .sortable.asc::after {
            content: "↑";
            color: #0d6efd;
            font-weight: bold;
        }

        .sortable.desc::after {
            content: "↓";
            color: #0d6efd;
            font-weight: bold;
        }

        .non-sortable {
            cursor: default;
        }

        /* Estilos mejorados para el formulario */
        .form-floating > .form-select,
        .form-floating > .form-control {
            border: 2px solid #e8e9fd;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #ffffff;
        }

        .form-floating > .form-select:focus,
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 8px 16px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
            background-color: #ffffff;
        }

        .form-floating > label {
            font-weight: 600;
            color: #6b7280;
            padding-left: 0.75rem;
        }

        .card {
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            border: none;
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4) !important;
        }

        .btn:active {
            transform: translateY(-1px) scale(0.98);
        }

        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }

        /* Animaciones suaves */
        .form-floating {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .shadow-sm {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        /* Mejoras para la tabla */
        .table th {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            color: #4b5563;
            padding: 1rem;
        }

        .table td {
            border-color: #f3f4f6;
            vertical-align: middle;
            padding: 1rem;
            color: #374151;
        }

        .table-hover tbody tr {
            transition: all 0.2s ease;
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: translateX(4px);
            box-shadow: -4px 0 0 #667eea;
        }

        /* Efectos de botón mejorados */
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
        }

        .btn-outline-danger:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-color: transparent;
            color: white;
        }

        /* Animación para optgroup */
        select optgroup {
            font-weight: 600;
            color: #4b5563;
            padding: 0.5rem 0;
        }

        /* Indicador de campo requerido */
        .form-floating > label::after {
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #667eea;
            border-radius: 50%;
            margin-left: 4px;
            vertical-align: middle;
        }

        /* Efecto de pulsación en el botón */
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
        }

        .btn[type="submit"]:hover::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <?php require 'header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require '../vistas/sidebar.php'; ?>

            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Pases de Expediente <?= "{$expediente['numero']}/{$expediente['letra']}/{$expediente['folio']}/{$expediente['libro']}/{$expediente['anio']}" ?></h1>
                    <a href="listar_expedientes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Formulario de nuevo pase -->
                <div class="card mb-4 shadow-lg border-0 overflow-hidden">
                    <div class="card-header text-white py-3 position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 80 80%22><path fill=%22%23fff%22 d=%22M14 16H9v-2h5V9.87a4 4 0 1 1 2 0V14h5v2h-5v15.95A10 10 0 0 0 23.66 27l-3.46-2 8.2-2.2-2.9 5a12 12 0 0 1-21 0l-2.89-5 8.2 2.2-3.47 2A10 10 0 0 0 14 31.95V16zm40 40h-5v-2h5v-4.13a4 4 0 1 1 2 0V54h5v2h-5v15.95A10 10 0 0 0 63.66 67l-3.47-2 8.2-2.2-2.88 5a12 12 0 0 1-21.02 0l-2.88-5 8.2 2.2-3.47 2A10 10 0 0 0 54 71.95V56zm-39 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm40-40a2 2 0 1 1 0-4 2 2 0 0 1 0 4zM15 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm40 40a2 2 0 1 0 0-4 2 2 0 0 0 0 4z%22/%3E</svg>'); background-size: 60px;"></div>
                        <h5 class="card-title mb-0 d-flex align-items-center position-relative">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                <i class="bi bi-plus-circle-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold">Registrar nuevo pase</div>
                                <small class="opacity-75">Complete el formulario para agregar un pase</small>
                            </div>
                        </h5>
                    </div>
                    <div class="card-body p-4" style="background: linear-gradient(to bottom, #fafbfc 0%, #ffffff 100%);">
                        <form id="formPase" action="procesar_pase.php" method="POST">
                            <input type="hidden" name="expediente_id" value="<?= $expediente['id'] ?>">
                            <input type="hidden" name="lugar_anterior" value="<?= htmlspecialchars($expediente['lugar']) ?>">
                            
                            <div class="row g-4">
                                <div class="col-xl-3 col-lg-6 col-md-6">
                                    <div class="position-relative">
                                        <div class="form-floating">
                                            <select class="form-select shadow-sm" id="lugar_nuevo" name="lugar_nuevo" required style="border: 2px solid #e8e9fd; padding-right: 2.5rem;">
                                                <option value="">Seleccione un lugar...</option>
                                                <optgroup label="📍 Comisiones">
                                                    <option value="Comision I">Comisión I</option>
                                                    <option value="Comision II">Comisión II</option>
                                                    <option value="Comision III">Comisión III</option>
                                                    <option value="Comision IV">Comisión IV</option>
                                                    <option value="Comision V">Comisión V</option>
                                                    <option value="Comision VI">Comisión VI</option>
                                                    <option value="Comision VII">Comisión VII</option>
                                                    <option value="Concejo Comisión">Concejo Comisión</option>
                                                </optgroup>
                                                <optgroup label="🏢 Secretarías">
                                                    <option value="Secretaria Legislativa Administrativa">Secretaría Legislativa Administrativa</option>
                                                    <option value="Secretaria Legislativa Parlamentaria">Secretaría Legislativa Parlamentaria</option>
                                                    <option value="Pro Secretaria Legislativa Parlamentaria">Pro Secretaría Legislativa Parlamentaria</option>
                                                    <option value="Secretaria Legal y Técnica">Secretaría Legal y Técnica</option>
                                                    <option value="Secretaria Comunicacion e Informacion Parlamentaria">Secretaría Comunicación e Información Parlamentaria</option>
                                                </optgroup>
                                                <optgroup label="⚖️ Asesorías">
                                                    <option value="Asesoria Legal">Asesoría Legal</option>
                                                    <option value="Asesoria Contable">Asesoría Contable</option>
                                                </optgroup>
                                                <optgroup label="🏛️ Institucional">
                                                    <option value="Mesa de Entrada">Mesa de Entrada</option>
                                                    <option value="Presidencia">Presidencia</option>
                                                    <option value="D.E.M">D.E.M</option>
                                                    <option value="Concejo Estudiantil">Concejo Estudiantil</option>
                                                </optgroup>
                                                <optgroup label="📁 Archivos">
                                                    <option value="Archivo">Archivo</option>
                                                    <option value="Archivo Art. 75 R.I">Archivo Art. 75 R.I</option>
                                                </optgroup>
                                                <optgroup label="👤 Otros">
                                                    <option value="Particular">Particular</option>
                                                    <option value="Reunión">Reunión</option>
                                                    <option value="Otro">✏️ Otro (especificar)</option>
                                                </optgroup>
                                            </select>
                                            <label for="lugar_nuevo" class="d-flex align-items-center">
                                                <i class="bi bi-geo-alt-fill me-2" style="color: #667eea;"></i>
                                                <span>Nuevo lugar *</span>
                                            </label>
                                        </div>
                                        <div class="position-absolute top-50 end-0 translate-middle-y me-3 pe-2" style="pointer-events: none;">
                                            <i class="bi bi-chevron-down" style="color: #667eea;"></i>
                                        </div>
                                    </div>
                                    <!-- Campo para texto personalizado cuando se selecciona "Otro" -->
                                    <div class="form-floating mt-3 shadow-sm" id="otro_lugar_container" style="display: none;">
                                        <input type="text" 
                                               class="form-control" 
                                               id="otro_lugar_texto" 
                                               name="otro_lugar_texto"
                                               maxlength="100"
                                               placeholder="Especifique el lugar..."
                                               style="border: 2px solid #e8e9fd;">
                                        <label for="otro_lugar_texto" class="d-flex align-items-center">
                                            <i class="bi bi-pencil-fill me-2" style="color: #667eea;"></i>
                                            <span>Especifique el lugar</span>
                                        </label>
                                    </div>
                                    <!-- Campo oculto para enviar el valor final -->
                                    <input type="hidden" id="lugar_nuevo_final" name="lugar_nuevo_hidden">
                                </div>
                                
                                <div class="col-xl-3 col-lg-6 col-md-6">
                                    <div class="form-floating shadow-sm">
                                        <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required style="border: 2px solid #e8e9fd;">
                                            <option value="">Seleccione tipo...</option>
                                            <option value="Ingreso">📥 Ingreso</option>
                                            <option value="Salida">📤 Salida</option>
                                            <option value="Aprobado">✅ Aprobado</option>
                                        </select>
                                        <label for="tipo_movimiento" class="d-flex align-items-center">
                                            <i class="bi bi-arrow-left-right me-2" style="color: #10b981;"></i>
                                            <span>Tipo de movimiento *</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-lg-6 col-md-6">
                                    <div class="form-floating shadow-sm">
                                        <input type="datetime-local" 
                                               class="form-control" 
                                               id="fecha_hora" 
                                               name="fecha_hora"
                                               required
                                               autocomplete="off"
                                               min="1900-01-01T00:00"
                                               max="2100-12-31T23:59"
                                               style="border: 2px solid #e8e9fd;">
                                        <label for="fecha_hora" class="d-flex align-items-center">
                                            <i class="bi bi-calendar-event-fill me-2" style="color: #f59e0b;"></i>
                                            <span>Fecha y hora *</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-xl-3 col-lg-6 col-md-6">
                                    <div class="form-floating shadow-sm">
                                        <input type="text" 
                                               class="form-control" 
                                               id="numero_acta" 
                                               name="numero_acta" 
                                               maxlength="30" 
                                               placeholder="Ej: 123/2025"
                                               style="border: 2px solid #e8e9fd;">
                                        <label for="numero_acta" class="d-flex align-items-center">
                                            <i class="bi bi-file-text-fill me-2" style="color: #06b6d4;"></i>
                                            <span>Número de Acta</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-center mt-4 pt-4 border-top">
                                <button type="submit" class="btn btn-lg px-5 py-3 shadow position-relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 50px; color: white; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s ease;">
                                    <span class="position-relative d-flex align-items-center">
                                        <i class="bi bi-save2-fill me-2 fs-5"></i> 
                                        Guardar Pase
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historial de pases -->
                <div class="card shadow-lg border-0 overflow-hidden">
                    <div class="card-header py-3 position-relative" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 80 80%22><path fill=%22%23fff%22 d=%22M14 16H9v-2h5V9.87a4 4 0 1 1 2 0V14h5v2h-5v15.95A10 10 0 0 0 23.66 27l-3.46-2 8.2-2.2-2.9 5a12 12 0 0 1-21 0l-2.89-5 8.2 2.2-3.47 2A10 10 0 0 0 14 31.95V16zm40 40h-5v-2h5v-4.13a4 4 0 1 1 2 0V54h5v2h-5v15.95A10 10 0 0 0 63.66 67l-3.47-2 8.2-2.2-2.88 5a12 12 0 0 1-21.02 0l-2.88-5 8.2 2.2-3.47 2A10 10 0 0 0 54 71.95V56zm-39 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm40-40a2 2 0 1 1 0-4 2 2 0 0 1 0 4zM15 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm40 40a2 2 0 1 0 0-4 2 2 0 0 0 0 4z%22/%3E</svg>'); background-size: 60px;"></div>
                        <h5 class="card-title mb-0 d-flex align-items-center justify-content-between position-relative text-white">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                    <i class="bi bi-clock-history fs-4"></i>
                                </div>
                                <div>
                                    <div class="fs-5 fw-bold">Historial de pases</div>
                                    <small class="opacity-75">Registro completo de movimientos</small>
                                </div>
                            </div>
                            <div class="badge px-3 py-2" style="background: rgba(255,255,255,0.25); font-size: 1rem;">
                                <i class="bi bi-list-check me-1"></i>
                                <?= count($historial) ?> registros
                            </div>
                        </h5>
                    </div>
                    <div class="card-body p-4" style="background: linear-gradient(to bottom, #fafbfc 0%, #ffffff 100%);">
                        <?php if (count($historial) == 0): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No hay pases registrados para este expediente. Use el formulario de arriba para registrar el primer pase.
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <!-- Información del expediente -->
                            <div class="row mb-4 g-3">
                                <div class="col-md-6">
                                    <div class="p-4 rounded-3 h-100 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <div class="position-absolute top-0 end-0 opacity-25">
                                            <i class="bi bi-calendar-event" style="font-size: 8rem; color: white;"></i>
                                        </div>
                                        <div class="position-relative">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                                    <i class="bi bi-calendar-event text-white fs-4"></i>
                                                </div>
                                                <h6 class="text-white mb-0 fw-bold">Fecha de ingreso</h6>
                                            </div>
                                            <div class="ms-5">
                                                <div class="badge px-4 py-2 fs-6" style="background: rgba(255,255,255,0.9); color: #667eea;">
                                                    <i class="bi bi-clock-fill me-2"></i>
                                                    <?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-4 rounded-3 h-100 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <div class="position-absolute top-0 end-0 opacity-25">
                                            <i class="bi bi-geo-alt-fill" style="font-size: 8rem; color: white;"></i>
                                        </div>
                                        <div class="position-relative">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                                    <i class="bi bi-geo-alt-fill text-white fs-4"></i>
                                                </div>
                                                <h6 class="text-white mb-0 fw-bold">Lugar actual</h6>
                                            </div>
                                            <div class="ms-5">
                                                <div class="badge px-4 py-2 fs-6" style="background: rgba(255,255,255,0.9); color: #d97706;">
                                                    <i class="bi bi-pin-map-fill me-2"></i>
                                                    <?= htmlspecialchars($expediente['lugar']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-hover" id="historialTable">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="0" data-type="date">
                                            <i class="bi bi-calendar3 me-2"></i>
                                            Fecha y Hora
                                        </th>
                                        <th class="sortable" data-column="1" data-type="text">
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Desde
                                        </th>
                                        <th class="sortable" data-column="2" data-type="text">
                                            <i class="bi bi-box-arrow-in-left me-2"></i>
                                            Hacia
                                        </th>
                                        <th class="sortable" data-column="3" data-type="text">
                                            <i class="bi bi-arrows-move me-2"></i>
                                            Movimiento
                                        </th>
                                        <th class="sortable" data-column="4" data-type="text">
                                            <i class="bi bi-file-earmark-text me-2"></i>
                                            N° de Acta
                                        </th>
                                        <th class="sortable" data-column="5" data-type="numeric">
                                            <i class="bi bi-hourglass-split me-2"></i>
                                            Tiempo transcurrido
                                        </th>
                                        <th class="non-sortable">
                                            <i class="bi bi-gear me-2"></i>
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($historial)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="mb-3" style="font-size: 4rem; color: #d1d5db;">
                                                        <i class="bi bi-inbox"></i>
                                                    </div>
                                                    <h6 class="text-muted mb-2">No hay historial de pases</h6>
                                                    <p class="text-muted small mb-0">Este expediente aún no tiene movimientos registrados</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($historial as $pase): 
    $horas_desde_ingreso = $pase['horas_desde_ingreso'] ?? 0;
    $dias_desde_ingreso = floor($horas_desde_ingreso / 24);
    $horas_resto_ingreso = $horas_desde_ingreso % 24;
    $fecha_timestamp = strtotime($pase['fecha_cambio']);
?>
<tr>
    <td data-sort="<?= $fecha_timestamp ?>">
        <div class="d-flex align-items-center">
            <i class="bi bi-calendar3 me-2" style="color: #667eea;"></i>
            <span><?= $pase['fecha_formateada'] ?></span>
        </div>
    </td>
    <td data-sort="<?= htmlspecialchars($pase['lugar_anterior'] ?? '') ?>">
        <div class="d-flex align-items-center">
            <i class="bi bi-box-arrow-right me-2" style="color: #ef4444;"></i>
            <span><?= htmlspecialchars($pase['lugar_anterior'] ?? '') ?></span>
        </div>
    </td>
    <td data-sort="<?= htmlspecialchars($pase['lugar_nuevo'] ?? '') ?>">
        <div class="d-flex align-items-center">
            <i class="bi bi-box-arrow-in-left me-2" style="color: #10b981;"></i>
            <span><?= htmlspecialchars($pase['lugar_nuevo'] ?? '') ?></span>
        </div>
    </td>
    <td data-sort="<?= htmlspecialchars($pase['tipo_movimiento'] ?? 'Movimiento') ?>">
        <?php if ($pase['tipo_movimiento'] === 'Ingreso'): ?>
            <span class="badge px-3 py-2 rounded-pill" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="bi bi-arrow-down-circle-fill me-1"></i>
                Ingreso
            </span>
        <?php elseif ($pase['tipo_movimiento'] === 'Salida'): ?>
            <span class="badge px-3 py-2 rounded-pill" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <i class="bi bi-arrow-up-circle-fill me-1"></i>
                Salida
            </span>
        <?php elseif ($pase['tipo_movimiento'] === 'Aprobado'): ?>
            <span class="badge px-3 py-2 rounded-pill" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="bi bi-check-circle-fill me-1"></i>
                Aprobado
            </span>
        <?php else: ?>
            <span class="badge px-3 py-2 rounded-pill" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                <i class="bi bi-info-circle-fill me-1"></i>
                <?= htmlspecialchars($pase['tipo_movimiento']) ?>
            </span>
        <?php endif; ?>
    </td>
    <td data-sort="<?= htmlspecialchars($pase['numero_acta'] ?? '') ?>">
        <div class="d-flex align-items-center">
            <?php if (!empty($pase['numero_acta'])): ?>
                <i class="bi bi-file-earmark-text-fill me-2" style="color: #06b6d4;"></i>
                <span class="fw-semibold"><?= htmlspecialchars($pase['numero_acta']) ?></span>
            <?php else: ?>
                <span class="text-muted fst-italic">Sin acta</span>
            <?php endif; ?>
        </div>
    </td>
    <td data-sort="<?= $horas_desde_ingreso ?>">
        <div class="d-flex align-items-center">
            <i class="bi bi-hourglass-split me-2" style="color: #667eea;"></i>
            <span><?= $dias_desde_ingreso ?> días, <?= $horas_resto_ingreso ?> horas</span>
        </div>
    </td>
    <td>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary rounded-start-pill" onclick="editarPaseModal('<?= $pase['fecha_cambio'] ?>','<?= htmlspecialchars($pase['lugar_nuevo'] ?? '',ENT_QUOTES) ?>',<?= $pase['id'] ?? 0 ?>,'<?= htmlspecialchars($pase['tipo_movimiento'] ?? '',ENT_QUOTES) ?>','<?= htmlspecialchars($pase['numero_acta'] ?? '',ENT_QUOTES) ?>')" style="border-color: #667eea; color: #667eea;">
                <i class="bi bi-pencil-fill"></i> Editar
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-end-pill" onclick="eliminarPase(<?= $pase['id'] ?? 0 ?>)" style="border-color: #ef4444; color: #ef4444;">
                <i class="bi bi-trash-fill"></i> Eliminar
            </button>
        </div>
    </td>
</tr>
<?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Funcionalidad de ordenamiento de tabla
class TableSorter {
    constructor(tableId) {
        this.table = document.getElementById(tableId);
        this.tbody = this.table.querySelector('tbody');
        this.headers = this.table.querySelectorAll('th.sortable');
        this.currentSort = { column: -1, direction: 'asc' };
        this.init();
    }

    init() {
        this.headers.forEach((header, index) => {
            header.addEventListener('click', () => this.sortTable(index, header));
        });
    }

    sortTable(columnIndex, header) {
        const dataType = header.getAttribute('data-type');
        const rows = Array.from(this.tbody.querySelectorAll('tr'));
        
        // Determinar dirección del ordenamiento
        let direction = 'asc';
        if (this.currentSort.column === columnIndex && this.currentSort.direction === 'asc') {
            direction = 'desc';
        }
        
        // Limpiar clases de otros encabezados
        this.headers.forEach(h => h.classList.remove('asc', 'desc'));
        
        // Agregar clase al encabezado actual
        header.classList.add(direction);
        
        // Ordenar filas
        rows.sort((a, b) => {
            const aVal = this.getCellValue(a, columnIndex, dataType);
            const bVal = this.getCellValue(b, columnIndex, dataType);
            
            let result = this.compareValues(aVal, bVal, dataType);
            return direction === 'desc' ? -result : result;
        });
        
        // Reorganizar filas en el DOM
        rows.forEach(row => this.tbody.appendChild(row));
        
        // Actualizar estado actual
        this.currentSort = { column: columnIndex, direction: direction };
    }

    getCellValue(row, columnIndex, dataType) {
        const cell = row.cells[columnIndex];
        const sortValue = cell.getAttribute('data-sort');
        
        if (sortValue) {
            if (dataType === 'numeric' || dataType === 'date') {
                return parseFloat(sortValue) || 0;
            }
            return sortValue.toLowerCase();
        }
        
        const textValue = cell.textContent.trim();
        if (dataType === 'numeric') {
            // Extraer números del texto (para "X días, Y horas")
            const match = textValue.match(/(\d+)/);
            return match ? parseFloat(match[1]) : 0;
        }
        
        return textValue.toLowerCase();
    }

    compareValues(a, b, dataType) {
        if (dataType === 'numeric' || dataType === 'date') {
            return a - b;
        }
        
        if (a < b) return -1;
        if (a > b) return 1;
        return 0;
    }
}

// Inicializar ordenamiento cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('historialTable')) {
        new TableSorter('historialTable');
    }
});
</script>
<script>
// Manejar funcionalidad "Otro" lugar
document.getElementById('lugar_nuevo').addEventListener('change', function() {
    const otroContainer = document.getElementById('otro_lugar_container');
    const otroTexto = document.getElementById('otro_lugar_texto');
    const lugarFinal = document.getElementById('lugar_nuevo_final');
    
    if (this.value === 'Otro') {
        otroContainer.style.display = 'block';
        otroTexto.required = true;
        otroTexto.focus();
        // Animación suave
        otroContainer.style.opacity = '0';
        otroContainer.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            otroContainer.style.transition = 'all 0.3s ease';
            otroContainer.style.opacity = '1';
            otroContainer.style.transform = 'translateY(0)';
        }, 10);
    } else {
        otroContainer.style.display = 'none';
        otroTexto.required = false;
        otroTexto.value = '';
        lugarFinal.value = this.value;
    }
});

// Actualizar valor final cuando se escribe en "otro"
document.getElementById('otro_lugar_texto').addEventListener('input', function() {
    const lugarFinal = document.getElementById('lugar_nuevo_final');
    lugarFinal.value = this.value;
});

// Mejorar comportamiento del campo datetime-local para permitir entrada manual
const fechaHoraInput = document.getElementById('fecha_hora');
if (fechaHoraInput) {
    // Prevenir el salto automático entre campos de fecha y hora
    fechaHoraInput.addEventListener('keydown', function(e) {
        // Permitir navegación con teclado sin restricciones
        const allowedKeys = ['Tab', 'ArrowLeft', 'ArrowRight', 'Backspace', 'Delete', 'Home', 'End'];
        if (allowedKeys.includes(e.key)) {
            return true;
        }
    });
    
    // Validar el año ingresado (debe tener 4 dígitos)
    fechaHoraInput.addEventListener('change', function(e) {
        if (this.value) {
            const fecha = new Date(this.value);
            const year = fecha.getFullYear();
            
            // Validar que el año esté en un rango razonable (1900-2100)
            if (year < 1900 || year > 2100) {
                Swal.fire({
                    title: 'Año inválido',
                    text: 'El año debe estar entre 1900 y 2100',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
                this.value = '';
                this.focus();
            }
        }
    });
    
    // Mejorar la experiencia de usuario
    fechaHoraInput.addEventListener('focus', function() {
        this.style.borderColor = '#667eea';
    });
    
    fechaHoraInput.addEventListener('blur', function() {
        this.style.borderColor = '#e8e9fd';
    });
}

// Manejar envío del formulario de nuevo pase
document.getElementById('formPase').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar campo "Otro" si está seleccionado
    const lugarSelect = document.getElementById('lugar_nuevo');
    const otroTexto = document.getElementById('otro_lugar_texto');
    const lugarFinal = document.getElementById('lugar_nuevo_final');
    
    if (lugarSelect.value === 'Otro') {
        if (!otroTexto.value.trim()) {
            Swal.fire({
                title: 'Campo requerido',
                text: 'Debe especificar el lugar cuando selecciona "Otro"',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            otroTexto.focus();
            return;
        }
        lugarFinal.value = otroTexto.value.trim();
    } else {
        lugarFinal.value = lugarSelect.value;
    }
    
    const formData = new FormData(this);
    // Reemplazar el valor del lugar con el valor final
    formData.set('lugar_nuevo', lugarFinal.value);
    
    fetch('procesar_pase.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: '¡Éxito!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                this.reset(); // Limpiar el formulario
                location.reload(); // Recargar para mostrar el nuevo pase
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo conectar con el servidor',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    });
});

function eliminarPase(id) {
    Swal.fire({
        title: '¿Eliminar pase?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_pase.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}`
            })
            .then(async res => {
                let data;
                try {
                    data = await res.json();
                } catch (e) {
                    Swal.fire('Error', 'Respuesta inesperada del servidor', 'error');
                    return;
                }
                if (data.success) {
                    Swal.fire('Eliminado', data.message, 'success').then(()=>location.reload());
                } else {
                    Swal.fire('Error', data.message || 'No se pudo eliminar', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            });
        }
    });
}
function editarPaseModal(fecha, lugar, id, tipoMovimiento = '', numeroActa = '') {
    const lugares = [
        'Mesa de Entrada', 'Comision I', 'Comision II', 'Comision III', 'Comision IV', 'Comision V', 'Comision VI', 'Comision VII',
        'Concejo Comisión', 'Asesoria Legal', 'Asesoria Contable', 'Secretaria Legislativa Administrativa',
        'Secretaria Legislativa Parlamentaria','Pro Secretaria Legislativa Parlamentaria', 'Secretaria Legal y Técnica', 'Secretaria Comunicacion e Informacion Parlamentaria',
        'Presidencia', 'D.E.M', 'Concejo Estudiantil', 'Archivo', 'Archivo Art. 75 R.I'
    ];
    
    let selectLugarHtml = `<select id='lugarEdit' class='swal2-input' style='width:100%;margin-top:8px;'>`;
    lugares.forEach(l => {
        selectLugarHtml += `<option value="${l}"${l === lugar ? ' selected' : ''}>${l}</option>`;
    });
    selectLugarHtml += `</select>`;
    
    let selectTipoHtml = `<select id='tipoEdit' class='swal2-input' style='width:100%;margin-top:8px;'>
        <option value="">Seleccione tipo de movimiento...</option>
        <option value="Ingreso"${tipoMovimiento === 'Ingreso' ? ' selected' : ''}>Ingreso</option>
        <option value="Salida"${tipoMovimiento === 'Salida' ? ' selected' : ''}>Salida</option>
        <option value="Aprobado"${tipoMovimiento === 'Aprobado' ? ' selected' : ''}>Aprobado</option>
    </select>`;
    
    Swal.fire({
        title: 'Editar pase',
        html: `
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Fecha y hora:</label>
                <input type='datetime-local' id='fechaEdit' class='swal2-input' value='${fecha.replace(' ', 'T')}' style='margin-top: 0;'>
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Nuevo lugar:</label>
                ${selectLugarHtml}
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Tipo de movimiento:</label>
                ${selectTipoHtml}
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Número de acta:</label>
                <input type='text' id='actaEdit' class='swal2-input' value='${numeroActa}' placeholder='Ej: 123/2025' maxlength='30' style='margin-top: 0;'>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar cambios',
        cancelButtonText: 'Cancelar',
        width: '500px',
        preConfirm: () => {
            const fechaVal = document.getElementById('fechaEdit').value;
            const lugarVal = document.getElementById('lugarEdit').value;
            const tipoVal = document.getElementById('tipoEdit').value;
            const actaVal = document.getElementById('actaEdit').value;
            
            if (!fechaVal || !lugarVal || !tipoVal) {
                Swal.showValidationMessage('Los campos fecha, lugar y tipo de movimiento son obligatorios');
                return false;
            }
            return {
                fecha: fechaVal, 
                lugar: lugarVal, 
                tipo: tipoVal, 
                acta: actaVal
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Actualizando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('editar_pase.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${id}&fecha=${encodeURIComponent(result.value.fecha)}&lugar_nuevo=${encodeURIComponent(result.value.lugar)}&tipo_movimiento=${encodeURIComponent(result.value.tipo)}&numero_acta=${encodeURIComponent(result.value.acta)}`
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        throw new Error('Respuesta del servidor no es JSON válido: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Forzar recarga completa de la página
                        window.location.reload(true);
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudo actualizar el pase',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Verifique su conexión.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });
}
</script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Crear gráfica de línea temporal
        const historialData = <?= json_encode(array_map(function($pase) {
            return [
                'fecha' => $pase['fecha_formateada'],
                'lugar' => $pase['lugar_nuevo'],
                'horas' => $pase['horas_desde_ingreso']
            ];
        }, $historial)) ?>;

        const canvas = document.createElement('canvas');
        canvas.id = 'timelineChart';
        canvas.style.maxHeight = '150px';
        canvas.style.height = '150px';
        document.querySelector('.card-body').appendChild(canvas);

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: historialData.map(d => d.fecha),
                datasets: [{
                    label: 'Horas transcurridas',
                    data: historialData.map(d => d.horas),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Línea de tiempo de pases'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const horas = context.raw;
                                const dias = Math.floor(horas / 24);
                                const horasResto = horas % 24;
                                return `${dias} días, ${horasResto} horas`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'DD/MM/YYYY HH:mm',
                            displayFormats: {
                                day: 'DD/MM',
                                hour: 'HH:mm'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Fecha y Hora'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Horas'
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>