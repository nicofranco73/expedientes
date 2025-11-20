<?php
/**
 * Generador de PDF usando FPDF (librería simple)
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

    // Incluir FPDF
    require_once('fpdf/fpdf.php');

    // Crear clase personalizada para PDF
    class PDF extends FPDF {
        function Header() {
            // Logo
            if (file_exists('../publico/imagen/LOGOCDE.png')) {
                $this->Image('../publico/imagen/LOGOCDE.png', 10, 6, 30);
            }
            // Título
            $this->SetFont('Arial', 'B', 16);
            $this->SetX(50);
            $this->Cell(0, 10, utf8_decode('CONCEJO DELIBERANTE'), 0, 1, 'C');
            $this->SetFont('Arial', '', 12);
            $this->SetX(50);
            $this->Cell(0, 8, 'COMPROBANTE DE EXPEDIENTE', 0, 1, 'C');
            $this->Ln(10);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Generado: ' . date('d/m/Y H:i:s') . ' - Sistema de Expedientes', 0, 0, 'C');
        }
    }

    // Crear nuevo PDF
    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Número de expediente destacado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetFillColor(200, 220, 255);
    $expediente_numero = 'EXPEDIENTE N° ' . $expediente['numero'] . '/' . $expediente['letra'] . '/' . $expediente['folio'] . '/' . $expediente['libro'] . '/' . $expediente['anio'];
    $pdf->Cell(0, 15, utf8_decode($expediente_numero), 1, 1, 'C', true);
    $pdf->Ln(10);

    // Datos del expediente
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 8, 'Fecha de Ingreso:', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, date('d/m/Y H:i', strtotime($expediente['fecha_hora_ingreso'])), 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 8, 'Iniciador:', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, utf8_decode($expediente['iniciador']));
    $pdf->Ln(3);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 8, 'Lugar Actual:', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode($expediente['lugar']), 0, 1);
    $pdf->Ln(5);

    // Extracto
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Extracto:', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 6, utf8_decode($expediente['extracto']));
    $pdf->Ln(10);

    // Información de carga
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, utf8_decode('INFORMACIÓN DE CARGA'), 0, 1);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, 'Fecha de carga: ' . date('d/m/Y H:i'), 0, 1);
    $pdf->Cell(0, 6, 'Usuario: ' . ($_SESSION['usuario'] ?? 'Sistema'), 0, 1);
    $pdf->Ln(10);

    // Mensaje de seguimiento
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'SEGUIMIENTO DEL EXPEDIENTE', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 6, utf8_decode('Puede seguir el movimiento de su expediente a través de esta web:'), 0, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'https://expedientescde.online/', 0, 1, 'C');

    // Generar nombre del archivo
    $fecha_actual = date('Ymd_Hi');
    $numero_exp = $expediente['numero'] . '_' . $expediente['letra'] . '_' . $expediente['folio'] . '_' . $expediente['libro'] . '_' . $expediente['anio'];
    $nombre_archivo = $fecha_actual . '_Expediente_' . $numero_exp . '.pdf';

    // Salida del PDF
    $pdf->Output('D', $nombre_archivo);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al generar PDF: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_expedientes.php");
    exit;
}
?>