<?php
/**
 * Generador de PDF con descarga automática
 * Versión que fuerza la descarga del archivo
 */
session_start();

// Validar que se reciba el ID del expediente
$expediente_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$expediente_id) {
    $_SESSION['mensaje'] = "ID de expediente inválido";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_expedientes.php");
    exit;
}

try {
    // -----------------------------------------------------------------
    // MODIFICACIÓN CLAVE: Incluir el archivo de conexión centralizado.
    // Sale de 'controladores/' a la raíz, entra a 'db/' y usa 'connection.php'.
    require_once('../db/connection.php'); 
    // La variable $db (conexión PDO) ahora está disponible.
    // -----------------------------------------------------------------

    // Obtener datos del expediente
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$expediente_id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("Expediente no encontrado");
    }

    // Generar nombre del archivo
    $fecha_actual = date('Ymd_Hi');
    $numero_exp = $expediente['numero'] . '_' . $expediente['letra'] . '_' . $expediente['folio'] . '_' . $expediente['libro'] . '_' . $expediente['anio'];
    $nombre_archivo = $fecha_actual . '_Expediente_' . $numero_exp . '.pdf';

    // Si el parámetro 'download' está presente, mostrar página de descarga
    if (isset($_GET['download'])) {
        // Generar URL del QR
        $qr_url = 'https://expedientescde.online/resultados_publico.php?numero=' . $expediente['numero'] . 
                  '&letra=' . $expediente['letra'] . 
                  '&folio=' . $expediente['folio'] . 
                  '&libro=' . $expediente['libro'] . 
                  '&anio=' . $expediente['anio'];

        // Página HTML que se convertirá automáticamente en PDF
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre_archivo ?></title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        .comprobante {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .titulo {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .subtitulo {
            font-size: 14pt;
            color: #666;
        }
        
        .expediente-numero {
            background: #f0f0f0;
            border: 2px solid #333;
            padding: 15px;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .campo {
            margin: 10px 0;
            display: table;
            width: 100%;
        }
        
        .campo-label {
            font-weight: bold;
            display: table-cell;
            width: 120px;
            vertical-align: top;
        }
        
        .campo-valor {
            display: table-cell;
            vertical-align: top;
            padding-left: 10px;
        }
        
        .extracto {
            background: #f9f9f9;
            border-left: 4px solid #333;
            padding: 15px;
            margin: 15px 0;
        }
        
        .seguimiento {
            background: #f0f8ff;
            border: 1px solid #333;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        
        .qr-info {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            font-size: 10pt;
            text-align: center;
        }
        
        .url-destacada {
            font-weight: bold;
            font-size: 14pt;
        }
    </style>
</head>
<body>
    <div class="comprobante">
        <div class="header">
            <div class="titulo">CONCEJO DELIBERANTE</div>
            <div class="subtitulo">COMPROBANTE DE EXPEDIENTE</div>
        </div>

        <div class="expediente-numero">
            EXPEDIENTE N° <?= htmlspecialchars($expediente['numero']) ?>/<?= htmlspecialchars($expediente['letra']) ?>/<?= htmlspecialchars($expediente['folio']) ?>/<?= htmlspecialchars($expediente['libro']) ?>/<?= htmlspecialchars($expediente['anio']) ?>
        </div>

        <div class="campo">
            <div class="campo-label">Fecha de Ingreso:</div>
            <div class="campo-valor"><?= date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])) ?></div>
        </div>

        <div class="campo">
            <div class="campo-label">Iniciador:</div>
            <div class="campo-valor"><?= htmlspecialchars($expediente['iniciador']) ?></div>
        </div>

        <div class="campo">
            <div class="campo-label">Lugar Actual:</div>
            <div class="campo-valor"><?= htmlspecialchars($expediente['lugar']) ?></div>
        </div>

        <div class="campo">
            <div class="campo-label">Extracto:</div>
            <div class="campo-valor">
                <div class="extracto">
                    <?= nl2br(htmlspecialchars($expediente['extracto'])) ?>
                </div>
            </div>
        </div>

        <hr style="margin: 30px 0;">

        <div class="campo">
            <div class="campo-label">Fecha de Carga:</div>
            <div class="campo-valor"><?= date('d/m/Y H:i') ?></div>
        </div>

        <div class="campo">
            <div class="campo-label">Usuario:</div>
            <div class="campo-valor"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Sistema') ?></div>
        </div>

        <div class="seguimiento">
            <h3 style="margin-top: 0;">SEGUIMIENTO DEL EXPEDIENTE</h3>
            <p>Puede seguir el movimiento de su expediente a través de esta web:</p>
            <div class="url-destacada">https://expedientescde.online/</div>
        </div>

        <div class="qr-info">
            <p><strong>Para acceso directo desde dispositivos móviles:</strong></p>
            <p>Visite: <?= htmlspecialchars($qr_url) ?></p>
        </div>

        <div class="footer">
            <p>Este documento fue generado automáticamente el <?= date('d/m/Y H:i:s') ?></p>
            <p>Sistema de Gestión de Expedientes - Concejo Deliberante</p>
        </div>
    </div>

    <script>
        // Configurar el título para la descarga
        document.title = '<?= $nombre_archivo ?>';
        
        // Iniciar descarga automática
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
                
                // Cerrar después de un tiempo
                setTimeout(function() {
                    if (window.opener) {
                        window.close();
                    }
                }, 3000);
            }, 500);
        });
    </script>
</body>
</html>
        <?php
        exit;
    }

    // Si no hay parámetro download, mostrar página de preparación
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generando PDF...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <h5 class="card-title">Preparando su comprobante</h5>
                        <p class="card-text">
                            <strong>Expediente:</strong> <?= htmlspecialchars($expediente['numero']) ?>/<?= htmlspecialchars($expediente['letra']) ?>/<?= htmlspecialchars($expediente['folio']) ?>/<?= htmlspecialchars($expediente['libro']) ?>/<?= htmlspecialchars($expediente['anio']) ?>
                        </p>
                        <p class="card-text">
                            <strong>Archivo:</strong> <?= htmlspecialchars($nombre_archivo) ?>
                        </p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Su descarga comenzará automáticamente en unos segundos...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Redirigir automáticamente para iniciar la descarga
        setTimeout(function() {
            window.location.href = 'generar_pdf_descarga.php?id=<?= $expediente_id ?>&download=1';
        }, 2000);

        // Mostrar botón manual después de un tiempo
        setTimeout(function() {
            Swal.fire({
                title: 'Descargar Comprobante',
                text: 'Archivo: <?= $nombre_archivo ?>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Descargar PDF',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('generar_pdf_descarga.php?id=<?= $expediente_id ?>&download=1', '_blank');
                } else {
                    window.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>
    <?php

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al generar comprobante: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_expedientes.php");
    exit;
}
?>