<?php
/**
 * Sistema de descarga automática de PDF - Comprobante de Expediente
 * Genera archivo con formato: FECHA_HORA_Expediente_NUMERO_LETRA_FOLIO_LIBRO_AÑO.pdf
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
    // CONEXIÓN CORREGIDA: Usar el archivo de conexión centralizado.
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

    // Generar nombre del archivo según especificaciones
    $fecha_actual = date('Ymd_Hi'); // YYYYMMDD_HHMM
    $numero_exp = $expediente['numero'] . '_' . $expediente['letra'] . '_' . $expediente['folio'] . '_' . $expediente['libro'] . '_' . $expediente['anio'];
    $nombre_archivo = $fecha_actual . '_Expediente_' . $numero_exp . '.pdf';

    // URL para el QR
    $qr_url = 'https://expedientescde.online/resultados_publico.php?numero=' . $expediente['numero'] . 
              '&letra=' . $expediente['letra'] . 
              '&folio=' . $expediente['folio'] . 
              '&libro=' . $expediente['libro'] . 
              '&anio=' . $expediente['anio'];

    // QR Code usando API gratuita de QR Server (más confiable)
    $qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qr_url);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al generar comprobante: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_expedientes.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nombre_archivo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @media print {
            body { 
                font-size: 10pt !important; 
                line-height: 1.2 !important;
                color: #000 !important;
                background: white !important;
                margin: 0;
                padding: 0;
            }
            .no-print { display: none !important; }
            .container { max-width: none !important; margin: 0 !important; padding: 8px !important; }
            .btn { display: none !important; }
            a { color: #000 !important; text-decoration: none !important; }
            .qr-section { page-break-inside: avoid; margin: 10px 0 !important; padding: 8px !important; }
            .qr-code { max-width: 120px !important; }
            .comprobante { 
                border: none !important;
                box-shadow: none !important;
                padding: 10px !important;
                margin: 0 !important;
            }
            .header { margin-bottom: 15px !important; padding-bottom: 10px !important; }
            .expediente-numero { margin: 11px 0 !important; padding: 10px !important; font-size: 16pt !important; }
            .datos-expediente { margin: 6px 0 !important; padding: 10px !important; }
            .campo { margin: 8px 0 !important; }
            .seguimiento { margin: 0px 0 !important; padding: 4px !important; }
            .info-carga { margin-top: 15px !important; padding-top: 10px !important; }
            .footer { margin-top: 15px !important; padding-top: 8px !important; font-size: 9pt !important; }
            .logo { width: 90px !important; margin-bottom: 2px !important; }
            .titulo { font-size: 18pt !important; margin: 5px 0 !important; }
            .subtitulo { font-size: 14pt !important; }
            .extracto { padding: 10px !important; margin: 8px 0 !important; }
            .alert { margin: 8px 0 !important; padding: 8px !important; }
        }
        
        @page {
            margin: 0.5cm;
            size: A4;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .comprobante {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 25px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .titulo {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin: 8px 0;
        }
        
        .subtitulo {
            font-size: 14px;
            color: #6c757d;
        }
        
        .expediente-numero {
            background: white;
            color: black;
            padding: 15px;
            border: 2px solid black;
            border-radius: 10px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .datos-expediente {
            background: #f8f9fa;
            border-left: 5px solid #0d6efd;
            padding: 14px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .campo {
            margin: 10px 0;
            display: flex;
            align-items: flex-start;
        }
        
        .campo-label {
            font-weight: bold;
            min-width: 150px;
            color: #495057;
        }
        
        .campo-valor {
            flex: 1;
            margin-left: 15px;
        }
        
        .extracto {
            background: white;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        
        .info-carga {
            border-top: 2px solid #dee2e6;
            padding-top: 15px;
            margin-top: 20px;
        }
        
        .seguimiento {
            background: linear-gradient(135deg, #e7f3ff, #cce7ff);
            border: 2px solid #0d6efd;
            padding: 18px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .qr-code {
            border: 2px solid #dee2e6;
            border-radius: 5px;
            padding: 8px;
            background: white;
            max-width: 160px;
            height: auto;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: block !important;
            margin: 0 auto !important;
        }
        
        .qr-fallback {
            background: #fff3cd;
            border: 2px dashed #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            color: #856404;
        }
        
        /* Estilos adicionales para centrado perfecto */
        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .qr-section .row {
            justify-content: center;
        }
        
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }
        
        .url-link {
            font-weight: bold;
            color: #0d6efd;
            font-size: 15px;
        }
        
        .nombre-archivo {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print mb-4">
            <div class="alert alert-success">
                <h5 class="alert-heading"><i class="bi bi-check-circle"></i> Comprobante Listo</h5>
                <p class="mb-2"><strong>Archivo:</strong></p>
                <div class="nombre-archivo"><?= htmlspecialchars($nombre_archivo) ?></div>
            </div>
            
            <div class="text-center">
                <button onclick="descargarPDF()" class="btn btn-primary btn-lg me-3">
                    <i class="bi bi-download"></i> Descargar PDF Automáticamente
                </button>
                <button onclick="window.print()" class="btn btn-success btn-lg me-3">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <button onclick="window.close()" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cerrar
                </button>
            </div>
        </div>

        <div class="comprobante">
            <div class="header">
                <img src="../publico/imagen/LOGOCDE.png" alt="Logo CDE" class="logo">
                <div class="titulo">CONCEJO DELIBERANTE ELDORADO</div>
                <div class="subtitulo">COMPROBANTE DE EXPEDIENTE</div>
            </div>

            <div class="expediente-numero">
                EXPEDIENTE N° <?= htmlspecialchars($expediente['numero']) ?>/<?= htmlspecialchars($expediente['letra']) ?>/<?= htmlspecialchars($expediente['folio']) ?>/<?= htmlspecialchars($expediente['libro']) ?>/<?= htmlspecialchars($expediente['anio']) ?>
            </div>

            <div class="datos-expediente">
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
            </div>

            <div class="info-carga">
                <h5 class="mb-3"><i class="bi bi-info-circle"></i> Información de Carga</h5>
                <div class="campo">
                    <div class="campo-label">Fecha de Carga:</div>
                    <div class="campo-valor"><?= date('d/m/Y H:i') ?></div>
                </div>
                <div class="campo">
                    <div class="campo-label">Usuario:</div>
                    <div class="campo-valor"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Sistema') ?></div>
                </div>
            </div>

            <div class="seguimiento">
                <h4 class="mb-3"><i class="bi bi-search"></i> SEGUIMIENTO DEL EXPEDIENTE</h4>
                <p class="mb-3">Puede seguir el movimiento de su expediente a través de esta web:</p>
                <div class="url-link">https://expedientescde.online/</div>
            </div>

            <div class="qr-section">
                <h5 class="mb-4 text-center"><i class="bi bi-qr-code"></i> Código QR para Acceso Rápido</h5>
                
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <?php 
                        $portal_url = 'https://expedientescde.online/';
                        $qr_portal_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($portal_url);
                        ?>
                        
                        <div class="text-center">
                            <img src="<?= $qr_portal_url ?>" 
                                 alt="QR portal expedientes" 
                                 class="qr-code mx-auto" 
                                 onerror="this.style.display='none'; document.getElementById('qr-fallback-portal').style.display='block';"
                                 onload="console.log('QR Portal cargado correctamente');">
                            
                            <div id="qr-fallback-portal" style="display: none; text-align: center; padding: 15px; border: 2px dashed #dc3545; border-radius: 5px; color: #dc3545; margin: 20px auto; max-width: 250px;">
                                <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem;"></i>
                                <p class="mt-2 mb-1 small">Error al cargar QR</p>
                                <p class="small">Acceda manualmente a: <br><strong><?= htmlspecialchars($portal_url) ?></strong></p>
                            </div>
                            <br>
                            
                            
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Instrucciones:</strong> Escanee el código QR con la cámara de su dispositivo móvil o una app de lectura de códigos QR para acceder al portal de expedientes.
                </div>
            </div>

            <div class="footer">
                <p><i class="bi bi-calendar"></i> Este documento fue generado automáticamente el <?= date('d/m/Y H:i:s') ?></p>
                <p><i class="bi bi-building"></i> Sistema de Gestión de Expedientes - Concejo Deliberante Eldorado</p>
            </div>
        </div>
    </div>

    <script>
        // Configurar nombre del archivo para descarga
        const nombreArchivo = '<?= $nombre_archivo ?>';
        
        // Función para descargar PDF
        function descargarPDF() {
            // Configurar título del documento
            document.title = nombreArchivo;
            
            // Iniciar impresión/descarga
            window.print();
        }
        
        // Auto-descarga al cargar (después de mostrar información)
        window.addEventListener('load', function() {
            // Esperar 2 segundos para que el usuario vea la información
            setTimeout(function() {
                if (confirm('¿Desea descargar automáticamente el comprobante PDF?\n\nArchivo: ' + nombreArchivo)) {
                    descargarPDF();
                }
            }, 2000);
        });
        
        // Cerrar ventana después de imprimir
        window.addEventListener('afterprint', function() {
            setTimeout(function() {
                if (window.opener) {
                    if (confirm('¿Desea cerrar esta ventana?')) {
                        window.close();
                    }
                }
            }, 1000);
        });
        
        // Configurar el nombre del archivo en el título permanentemente
        document.title = nombreArchivo;
    </script>
</body>
</html>