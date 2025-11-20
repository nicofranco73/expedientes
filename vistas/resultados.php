<?php
/**
 * Resultados de búsqueda pública de expedientes
 */

// Iniciar sesión y configuración
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Función para escapar output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Validar método POST o GET (para búsqueda rápida)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['busqueda_rapida'])) {
    header('Location: consulta.php');
    exit;
}

try {
    // Verificar si es búsqueda rápida
    if (isset($_GET['busqueda_rapida'])) {
        $termino_busqueda = trim($_GET['busqueda_rapida']);
        
        if (empty($termino_busqueda)) {
            throw new Exception("Término de búsqueda vacío");
        }
        
         // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

        // Búsqueda en múltiples campos
        $sql = "SELECT * FROM expedientes 
                WHERE numero LIKE :termino
                   OR letra LIKE :termino
                   OR folio LIKE :termino
                   OR libro LIKE :termino
                   OR anio LIKE :termino
                   OR extracto LIKE :termino_texto
                   OR iniciador LIKE :termino_texto
                   OR lugar LIKE :termino_texto
                ORDER BY fecha_hora_ingreso DESC 
                LIMIT 20";

        $stmt = $db->prepare($sql);
        $termino_like = '%' . $termino_busqueda . '%';
        
        $stmt->execute([
            ':termino' => $termino_like,
            ':termino_texto' => $termino_like
        ]);

        $expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $es_busqueda_rapida = true;
        
    } else {
        // Búsqueda avanzada (POST)
        // CORREGIDO: No usar FILTER_VALIDATE_INT para preservar ceros iniciales
        $numero = !empty($_POST['numero']) ? trim($_POST['numero']) : null;
        $letra = !empty($_POST['letra']) ? strtoupper(substr($_POST['letra'], 0, 1)) : null;
        $folio = !empty($_POST['folio']) ? trim($_POST['folio']) : null;
        $libro = !empty($_POST['libro']) ? trim($_POST['libro']) : null;
        $anio = !empty($_POST['anio']) ? filter_var($_POST['anio'], FILTER_VALIDATE_INT) : null;

        // Validar que al menos un campo tenga valor
        if (!$numero && !$letra && !$folio && !$libro && !$anio) {
            throw new Exception("Debe ingresar al menos un criterio de búsqueda");
        }

        // Validar y sanitizar campos
        if ($numero && !preg_match('/^\d+$/', $numero)) {
            throw new Exception("Número de expediente inválido");
        }
        
        if ($folio && !preg_match('/^\d+$/', $folio)) {
            throw new Exception("Folio inválido");
        }
        
        if ($libro && !preg_match('/^\d+$/', $libro)) {
            throw new Exception("Libro inválido");
        }
        
        if ($anio && ($anio < 1973 || $anio > 2030)) {
            throw new Exception("Año fuera de rango permitido (1973-2030)");
        }
        
        if ($letra && !preg_match('/^[A-Z]$/', $letra)) {
            throw new Exception("Letra inválida");
        }

        // Conectar a la base de datos
         $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

        // Construir consulta dinámica según los campos proporcionados
        $where_conditions = [];
        $params = [];

        if ($numero !== null) {
            $where_conditions[] = "numero = :numero";
            $params[':numero'] = $numero;
        }
        
        if ($letra !== null) {
            $where_conditions[] = "letra = :letra";
            $params[':letra'] = $letra;
        }
        
        if ($folio !== null) {
            $where_conditions[] = "folio = :folio";
            $params[':folio'] = $folio;
        }
        
        if ($libro !== null) {
            $where_conditions[] = "libro = :libro";
            $params[':libro'] = $libro;
        }
        
        if ($anio !== null) {
            $where_conditions[] = "anio = :anio";
            $params[':anio'] = $anio;
        }

        // Consultar expedientes
        $sql = "SELECT * FROM expedientes";
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        $sql .= " ORDER BY fecha_hora_ingreso DESC LIMIT 10";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $es_busqueda_rapida = false;
    }

    // Agregar después de obtener los expedientes
    if (!empty($expedientes)) {
        // Para el primer expediente, obtener historial si existe
        $expediente = $expedientes[0];
        
        // Consultar historial de lugares
        $sql = "SELECT 
                fecha_cambio,
                DATE_FORMAT(fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
                lugar_anterior,
                lugar_nuevo,
                tipo_movimiento
            FROM historial_lugares 
            WHERE expediente_id = :id
            ORDER BY fecha_cambio ASC";
                    
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $expediente['id']]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar después de obtener el historial
    if ($expediente && !empty($historial)) {
        // Calcular días transcurridos
        $primera_fecha = strtotime($expediente['fecha_hora_ingreso']);
        $ultima_fecha = strtotime(end($historial)['fecha_cambio']);
        
        $diferencia = $ultima_fecha - $primera_fecha;
        $dias_transcurridos = floor($diferencia / (60 * 60 * 24));
        $horas_transcurridas = floor(($diferencia % (60 * 60 * 24)) / (60 * 60));
    }

    // Limpiar CAPTCHA usado
    unset($_SESSION['captcha']);

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de la Consulta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="publico/css/estilos.css">
    <style>
.tracking-timeline {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.tracking-timeline::after {
    content: '';
    position: absolute;
    width: 6px;
    background-color: #e9ecef;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -3px;
}

.tracking-container {
    padding: 10px 40px;
    position: relative;
    width: 50%;
}

.tracking-container::after {
    content: '';
    position: absolute;
    width: 25px;
    height: 25px;
    right: -17px;
    background-color: white;
    border: 4px solid #0d6efd;
    top: 15px;
    border-radius: 50%;
    z-index: 1;
}

.tracking-left {
    left: 0;
}

.tracking-right {
    left: 50%;
}

.tracking-right::after {
    left: -16px;
}

.tracking-content {
    padding: 20px;
    background-color: white;
    position: relative;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.tracking-content h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.tracking-content p {
    margin: 0;
    font-size: 0.9rem;
    color: #6c757d;
}

.progress {
    background-color: #e9ecef;
    box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
}

.progress-bar {
    font-size: 0.9rem;
    line-height: 25px;
    font-weight: 500;
}

.card-title {
    color: #495057;
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.bi-calendar-range {
    color: #198754;
    margin-right: 0.5rem;
}

@media screen and (max-width: 600px) {
    .tracking-timeline::after {
        left: 31px;
    }
    
    .tracking-container {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    .tracking-right {
        left: 0%;
    }
    
    .tracking-container::after {
        left: 15px;
    }
}
</style>
</head>
<body>
    <div class="container py-4">
        <div class="text-center mb-4">
            <img src="/publico/imagen/LOGOCDE.png" alt="Logo" style="height:116px;">
            <h2 class="titulo-principal mt-2">Resultado de la Consulta</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (isset($es_busqueda_rapida) && $es_busqueda_rapida): ?>
                    <h3 class="card-title mb-4">
                        <i class="bi bi-lightning-fill text-warning me-2"></i>
                        Resultados de Búsqueda Rápida: "<?= e($termino_busqueda) ?>"
                    </h3>
                <?php else: ?>
                    <h3 class="card-title mb-4">
                        <i class="bi bi-sliders me-2"></i>
                        Resultados de Búsqueda Avanzada
                    </h3>
                <?php endif; ?>

                <?php if (!empty($expedientes)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        Se encontraron <?= count($expedientes) ?> expediente(s) que coinciden con los criterios de búsqueda.
                    </div>

                    <?php foreach ($expedientes as $exp): ?>
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text"></i>
                                    Expediente N°: <?= e($exp['numero']) ?>/<?= e($exp['letra']) ?>/<?= e($exp['folio']) ?>/<?= e($exp['libro']) ?>/<?= e($exp['anio']) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th style="width: 120px">Número:</th>
                                                <td><?= e($exp['numero']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Letra:</th>
                                                <td><?= e($exp['letra']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Folio:</th>
                                                <td><?= e($exp['folio']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Libro:</th>
                                                <td><?= e($exp['libro']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Año:</th>
                                                <td><?= e($exp['anio']) ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th style="width: 140px">Fecha de Ingreso:</th>
                                                <td><?= date('d/m/Y H:i', strtotime($exp['fecha_hora_ingreso'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ubicación Actual:</th>
                                                <td><span class="badge rounded-pill text-bg-warning"><?= e($exp['lugar']) ?></span></td>
                                            </tr>
                                            <tr>
                                                <th>Iniciador:</th>
                                                <td><?= e($exp['iniciador']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Extracto:</th>
                                                <td><?= e(substr($exp['extracto'], 0, 100)) ?><?= strlen($exp['extracto']) > 100 ? '...' : '' ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="text-end mt-2">
                                    <a href="pases_expediente.php?id=<?= $exp['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> Ver Historial de Pases
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

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

                    
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                        <h4 class="mt-3">No se encontraron expedientes</h4>
                        <p class="mb-0">No se encontraron expedientes que coincidan con los criterios de búsqueda proporcionados.</p>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="/vistas/consulta.php" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-left"></i> Nueva Consulta
                    </a>
                    
                </div>
            </div>
        </div>
    </div>
</body>
</html>