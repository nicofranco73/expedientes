<?php
session_start();
header('Content-Type: application/json');

try {
    // Validar parámetros
    $concejal_id = filter_var($_GET['concejal_id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$concejal_id) {
        throw new Exception('ID de concejal inválido');
    }

    // -----------------------------------------------------------------
    // CONEXIÓN CORREGIDA: Usar el archivo de conexión centralizado.
    // Asume que connection.php ya expone la conexión como $db.
    require_once '../db/connection.php';
    // -----------------------------------------------------------------

    // Verificar que el concejal existe
    $stmt = $db->prepare("SELECT id FROM concejales WHERE id = ?");
    $stmt->execute([$concejal_id]);
    
    if (!$stmt->fetchColumn()) {
        throw new Exception('Concejal no encontrado');
    }

    // Obtener registros eliminados
    $stmt = $db->prepare("
        SELECT 
            id,
            nombre_bloque,
            fecha_inicio,
            fecha_fin,
            observacion,
            fecha_registro,
            fecha_eliminacion,
            motivo_eliminacion
        FROM concejal_bloques_historial 
        WHERE concejal_id = ? AND eliminado = TRUE
        ORDER BY fecha_eliminacion DESC
    ");
    $stmt->execute([$concejal_id]);
    $eliminados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear fechas para mostrar
    foreach ($eliminados as &$bloque) {
        $bloque['fecha_inicio'] = $bloque['fecha_inicio'] ? date('d/m/Y', strtotime($bloque['fecha_inicio'])) : null;
        $bloque['fecha_registro'] = date('d/m/Y H:i', strtotime($bloque['fecha_registro']));
        $bloque['fecha_eliminacion'] = date('d/m/Y H:i', strtotime($bloque['fecha_eliminacion']));
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'eliminados' => $eliminados,
        'total' => count($eliminados)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>