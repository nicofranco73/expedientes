<?php
session_start();
// Incluir la conexión PDO. Asumo que la variable $db (objeto PDO) está disponible.
require_once('../../db/connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Método no permitido']);
    exit;
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success'=>false,'message'=>'ID inválido']);
    exit;
}

try {
    
    
    $stmt = $db->prepare('DELETE FROM historial_lugares WHERE id = ?');
    $stmt->execute([$id]);
    
    // NOTA: Si este pase eliminado fuera el último, el lugar del expediente 
    // en la tabla 'expedientes' debería actualizarse al pase anterior. 
    // Este script no maneja esa lógica, solo elimina el registro.

    echo json_encode(['success'=>true,'message'=>'Pase eliminado']);
    
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

exit;
?>