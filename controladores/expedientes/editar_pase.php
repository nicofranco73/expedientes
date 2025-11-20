<?php
session_start();

// Incluir la conexión PDO. Asumo que la variable $db (objeto PDO) está disponible.
require_once('../../db/connection.php'); 

// Headers para evitar cache
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $fecha = $_POST['fecha'] ?? '';
    $lugar_nuevo = $_POST['lugar_nuevo'] ?? '';
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? '';
    $numero_acta = $_POST['numero_acta'] ?? '';
    
    if (!$id || !$fecha || !$lugar_nuevo || !$tipo_movimiento) {
        throw new Exception('Los campos ID, fecha, lugar y tipo de movimiento son obligatorios');
    }
    
    // Iniciar transacción
    $db->beginTransaction();
    
    // Obtener información del pase antes de actualizar
    $stmt = $db->prepare("SELECT expediente_id, fecha_cambio FROM historial_lugares WHERE id = ?");
    $stmt->execute([$id]);
    $pase_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pase_actual) {
        throw new Exception('Pase no encontrado');
    }
    
    $expediente_id = $pase_actual['expediente_id'];
    
    // Actualizar el pase
    $stmt = $db->prepare("UPDATE historial_lugares SET fecha_cambio = ?, lugar_nuevo = ?, tipo_movimiento = ?, numero_acta = ? WHERE id = ?");
    $stmt->execute([$fecha, $lugar_nuevo, $tipo_movimiento, $numero_acta, $id]);
    
    
    // Verificar si este es el último pase (más reciente) del expediente
    $stmt = $db->prepare("
        SELECT id FROM historial_lugares 
        WHERE expediente_id = ? 
        ORDER BY fecha_cambio DESC, id DESC 
        LIMIT 1
    ");
    $stmt->execute([$expediente_id]);
    $ultimo_pase = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si el pase editado es el último, actualizar el lugar actual del expediente
    if ($ultimo_pase && $ultimo_pase['id'] == $id) {
        $stmt = $db->prepare("UPDATE expedientes SET lugar = ? WHERE id = ?");
        $stmt->execute([$lugar_nuevo, $expediente_id]);
    }
    
    // Confirmar transacción
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pase actualizado correctamente',
        'data' => [
            'id' => $id,
            'fecha' => $fecha,
            'lugar' => $lugar_nuevo,
            'tipo' => $tipo_movimiento,
            'acta' => $numero_acta
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit; 
?>