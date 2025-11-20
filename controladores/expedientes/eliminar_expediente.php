
<?php
session_start();
// Incluir la conexión PDO. Asumo que la variable $db (objeto PDO) está disponible.
require_once('../../db/connection.php'); 

header('Content-Type: application/json');

try {
    // Obtener y validar datos
    $data = json_decode(file_get_contents('php://input'), true);
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$id) {
        throw new Exception('ID de expediente inválido');
    }

    // Conectar a la base de datos
    /* CÓDIGO ELIMINADO: Se ha eliminado el bloque new PDO(...) */
    
    // Iniciar transacción
    $db->beginTransaction();

    // Eliminar primero registros relacionados en historial_lugares
    $sql = "DELETE FROM historial_lugares WHERE expediente_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    // Eliminar el expediente
    $sql = "DELETE FROM expedientes WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);

    // Confirmar transacción
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Expediente eliminado correctamente'
    ]);

} catch (Exception $e) {
    // Revertir cambios si hay error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar: ' . $e->getMessage()
    ]);
}