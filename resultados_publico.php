
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de la Consulta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="publico/css/estilos.css">
  
</head>
<body>
    <div class="container py-4">
        <div class="text-center mb-4">
            <img src="publico/imagen/LOGOCDE.png" alt="Logo" style="height:116px;">
            <h2 class="titulo-principal mt-2">Resultado de la Consulta</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4 text-center" style="font-size:1.5rem; font-weight:bold; color:#0d6efd; background:#e9ecef; padding:15px; border-radius:8px;">
                    <i class="bi bi-file-earmark-text-fill me-2"></i>
                    Expediente N°: <?= e($numero_original) ?>/<?= e($letra) ?>/<?= e($folio_original) ?>/<?= e($libro_original) ?>/<?= e($anio) ?>
                </h3>

                <?php if ($expediente): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 200px">Número:</th>
                                    <td><?= e($expediente['numero']) ?></td>
                                </tr>
                                <tr>
                                    <th>Letra:</th>
                                    <td><?= e($expediente['letra']) ?></td>
                                </tr>
                                <tr>
                                    <th>Folio:</th>
                                    <td><?= e($expediente['folio']) ?></td>
                                </tr>
                                <tr>
                                    <th>Libro:</th>
                                    <td><?= e($expediente['libro']) ?></td>
                                </tr>
                                <tr>
                                    <th>Año:</th>
                                    <td><?= e($expediente['anio']) ?></td>
                                </tr>
                                <tr>
                                    <th>Fecha de Ingreso:</th>
                                    <td><?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?></td>
                                </tr>
                               
                                <tr>
                                    <th>Extracto:</th>
                                    <td><?= e($expediente['extracto']) ?></td>
                                </tr>
                                <tr>
                                    <th>Iniciador:</th>
                                    <td><?= e($expediente['iniciador']) ?></td>
                                </tr>
                               
                            </tbody>
                        </table>
                    </div>

                    <!-- Agregar después de la tabla de datos del expediente -->
                    <?php if ($expediente && !empty($historial)): ?>
                        <div class="mt-5">
                            <h4 class="mb-4">Historial de Ubicaciones</h4>
                            <div class="tracking-timeline">
                                <!-- Mostrar lugar inicial -->
                                <div class="tracking-container tracking-left">
                                    <div class="tracking-content">
                                        <h3>Ingreso del Expediente</h3>
                                        <p>Ubicación: Mesa de Entrada</p>
                                        <p class="text-muted">
                                            <i class="bi bi-clock"></i> 
                                            <?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Mostrar historial de cambios -->
                                <?php foreach ($historial as $index => $pase): ?>
                                    <div class="tracking-container <?= $index % 2 == 0 ? 'tracking-right' : 'tracking-left' ?>">
                                        <div class="tracking-content">
                                            <h3>
                                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                                Traslado de Expediente
                                            </h3>
                                            <p>
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-arrow-right-circle"></i>
                                                    <?= e($pase['tipo_movimiento']) ?>
                                                </span>
                                            </p>
                                          
                                            <p>
                                                <span class="fw-semibold text-secondary">
                                                    <i class="bi bi-box-arrow-in-right"></i> A:
                                                </span>
                                                <span class="badge bg-success"><?= e($pase['lugar_nuevo']) ?></span>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-calendar-event"></i>
                                                <?= $pase['fecha_formateada'] ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Agregar después del div tracking-timeline -->
                    <?php if ($expediente && !empty($historial)): ?>
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-calendar-range"></i> 
                                    Tiempo Total de Tramitación
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 25px;">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: 100%;" 
                                             aria-valuenow="100" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $dias_transcurridos ?> días, <?= $horas_transcurridas ?> horas
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <p class="mb-0">
                                        <strong>Desde:</strong> <?= date('d/m/Y H:i', $primera_fecha) ?>
                                        <strong class="ms-3">Hasta:</strong> <?= date('d/m/Y H:i', $ultima_fecha) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No se encontró el expediente solicitado
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-left"></i> Nueva Consulta
                    </a>
                    
                </div>
            </div>
        </div>
    </div>
</body>
</html>