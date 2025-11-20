
<?php
session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Validar datos
    $expediente_id = filter_var($_POST['expediente_id'] ?? null, FILTER_VALIDATE_INT);
    $lugar_anterior = trim($_POST['lugar_anterior'] ?? '');
    $lugar_nuevo = trim($_POST['lugar_nuevo'] ?? '');
    $fecha_hora = $_POST['fecha_hora'] ?? '';
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? '';
    $numero_acta = trim($_POST['numero_acta'] ?? '');

    if (!$expediente_id || !$lugar_anterior || !$lugar_nuevo || !$fecha_hora || !$tipo_movimiento) {
        throw new Exception('Todos los campos son requeridos');
    }

    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;

    // Iniciar transacción
    $db->beginTransaction();

    // Registrar en historial
    $sql = "INSERT INTO historial_lugares (
                expediente_id, 
                lugar_anterior, 
                lugar_nuevo, 
                fecha_cambio,
                tipo_movimiento,
                numero_acta
            ) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $expediente_id,
        $lugar_anterior,
        $lugar_nuevo,
        $fecha_hora,
        $tipo_movimiento,
        $numero_acta
    ]);

    // Actualizar lugar actual del expediente
    $sql = "UPDATE expedientes SET lugar = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$lugar_nuevo, $expediente_id]);

    // Confirmar transacción
    $db->commit();

    // Verificar si es una petición AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Pase registrado correctamente']);
        exit;
    }

    $_SESSION['mensaje'] = "Pase registrado correctamente";
    $_SESSION['tipo_mensaje'] = "success";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    // Verificar si es una petición AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al registrar el pase: ' . $e->getMessage()]);
        exit;
    }
    
    $_SESSION['mensaje'] = "Error al registrar el pase: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: pases_expediente.php?id=" . $expediente_id);
exit;
?>