<?php
// vistas/pases_expediente_vista.php 
// Este archivo requiere las variables $expediente y $historial.

// Función de utilidad para generar las etiquetas de tiempo.
function formatHoras($horas) {
    $dias = floor($horas / 24);
    $horas_resto = $horas % 24;
    $output = '';
    if ($dias > 0) {
        $output .= "$dias día" . ($dias > 1 ? 's' : '') . " ";
    }
    if ($horas_resto > 0 || $dias == 0) {
        $output .= "$horas_resto hora" . ($horas_resto !== 1 ? 's' : '');
    }
    return trim($output);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="/publico/css/estilos.css">
    <link rel="stylesheet" href="/publico/css/pases-expediente.css"> 
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

                <?php require 'includes/form_pase.php'; ?>

                <div class="card shadow-lg border-0 overflow-hidden">
                    <div class="card-header py-3 position-relative" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
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
                        
                        <div class="row mb-4 g-3">
                            <div class="col-md-6">
                                <div class="p-4 rounded-3 h-100 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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

                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="historialTable">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="0" data-type="date"><i class="bi bi-calendar3 me-2"></i> Fecha y Hora</th>
                                        <th class="sortable" data-column="1" data-type="text"><i class="bi bi-box-arrow-right me-2"></i> Desde</th>
                                        <th class="sortable" data-column="2" data-type="text"><i class="bi bi-box-arrow-in-left me-2"></i> Hacia</th>
                                        <th class="sortable" data-column="3" data-type="text"><i class="bi bi-arrows-move me-2"></i> Movimiento</th>
                                        <th class="sortable" data-column="4" data-type="text"><i class="bi bi-file-earmark-text me-2"></i> N° de Acta</th>
                                        <th class="sortable" data-column="5" data-type="numeric"><i class="bi bi-hourglass-split me-2"></i> Tiempo transcurrido</th>
                                        <th class="non-sortable"><i class="bi bi-gear me-2"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($historial)): ?>
                                        <?php foreach ($historial as $pase): ?>
                                            <?php $fecha_timestamp = strtotime($pase['fecha_cambio']); ?>
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
                                                <td>
                                                    <?php 
                                                        $tipo = htmlspecialchars($pase['tipo_movimiento'] ?? '');
                                                        $clase = match($tipo) {
                                                            'Ingreso' => 'primary',
                                                            'Salida' => 'warning',
                                                            'Aprobado' => 'success',
                                                            'Desaprobado' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>
                                                    <span class="badge bg-<?= $clase ?> rounded-pill">
                                                        <?= $tipo ?>
                                                    </span>
                                                </td>
                                                <td data-sort="<?= htmlspecialchars($pase['numero_acta'] ?? '') ?>">
                                                     <?= htmlspecialchars($pase['numero_acta'] ?? 'N/A') ?>
                                                </td>
                                                <td data-sort="<?= $pase['horas_desde_ingreso'] ?? 0 ?>">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-stopwatch me-2" style="color: #f59e0b;"></i>
                                                        <span class="text-muted small">
                                                            <?= formatHoras($pase['horas_desde_ingreso'] ?? 0) ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar Pase" onclick="confirmarEliminacion(<?= $pase['id'] ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No hay movimientos registrados.</td>
                                        </tr>
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
    <?php require 'includes/pases_expediente_scripts.js'; // Incluir JS en un archivo separado ?>
</body>
</html>