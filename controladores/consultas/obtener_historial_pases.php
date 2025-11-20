<?php
header('Content-Type: application/json');

try {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('ID inválido');
    }

    // -----------------------------------------------------------------
    // CONEXIÓN CORREGIDA: Usar el archivo de conexión centralizado.
    require_once '../db/connection.php';
    // La variable $db (conexión PDO) ahora está disponible.
    // -----------------------------------------------------------------

    $sql = "SELECT 
                hl.fecha_cambio,
                DATE_FORMAT(hl.fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
                hl.lugar_anterior,
                hl.lugar_nuevo
            FROM historial_lugares hl
            WHERE hl.expediente_id = ?
            ORDER BY hl.fecha_cambio ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $historial]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>