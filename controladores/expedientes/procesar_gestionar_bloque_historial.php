<?php
session_start();
header('Content-Type: application/json');

try {
    // Validar datos recibidos
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $accion = $_POST['accion'] ?? '';
    $bloque_id = filter_var($_POST['bloque_id'] ?? null, FILTER_VALIDATE_INT);
    $concejal_id = filter_var($_POST['concejal_id'] ?? null, FILTER_VALIDATE_INT);

    // Validaciones básicas
    if (!in_array($accion, ['editar', 'eliminar', 'marcar_actual'])) {
        throw new Exception('Acción no válida');
    }

    if (!$bloque_id || !$concejal_id) {
        throw new Exception('IDs inválidos');
    }

    // Conectar a la base de datos
    require_once '../db/connection.php';
    $db = $pdo;

    // Verificar que el concejal y el bloque existen
    $stmt = $db->prepare("
        SELECT cbh.*, c.bloque as bloque_actual_concejal 
        FROM concejal_bloques_historial cbh
        JOIN concejales c ON c.id = cbh.concejal_id
        WHERE cbh.id = ? AND cbh.concejal_id = ? AND (cbh.eliminado IS NULL OR cbh.eliminado = FALSE)
    ");
    $stmt->execute([$bloque_id, $concejal_id]);
    $bloque_historial = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bloque_historial) {
        throw new Exception('Registro no encontrado o ya eliminado');
    }

    // Iniciar transacción
    $db->beginTransaction();

    try {
        switch ($accion) {
            case 'editar':
                $nombre_bloque = trim($_POST['nombre_bloque'] ?? '');
                $fecha_inicio = $_POST['fecha_inicio'] ?? null;
                $observaciones = trim($_POST['observaciones'] ?? '');
                $hacer_actual = isset($_POST['hacer_actual']) && $_POST['hacer_actual'] === 'true';

                if (empty($nombre_bloque)) {
                    throw new Exception('El nombre del bloque es obligatorio');
                }

                if ($fecha_inicio && !DateTime::createFromFormat('Y-m-d', $fecha_inicio)) {
                    throw new Exception('Formato de fecha inválido');
                }

                // Si se marca como actual, desactivar el bloque actual anterior
                if ($hacer_actual && !$bloque_historial['es_actual']) {
                    // Marcar bloque actual anterior como no actual y ponerle fecha de fin
                    $stmt = $db->prepare("
                        UPDATE concejal_bloques_historial 
                        SET es_actual = FALSE, 
                            fecha_fin = COALESCE(fecha_fin, CURDATE())
                        WHERE concejal_id = ? AND es_actual = TRUE AND id != ?
                    ");
                    $stmt->execute([$concejal_id, $bloque_id]);

                    // Actualizar el bloque en la tabla principal de concejales
                    $stmt = $db->prepare("UPDATE concejales SET bloque = ? WHERE id = ?");
                    $stmt->execute([$nombre_bloque, $concejal_id]);
                }

                // Actualizar el registro
                $stmt = $db->prepare("
                    UPDATE concejal_bloques_historial 
                    SET nombre_bloque = ?, 
                        fecha_inicio = ?, 
                        observacion = ?,
                        es_actual = ?
                    WHERE id = ? AND concejal_id = ?
                ");
                $stmt->execute([
                    $nombre_bloque,
                    $fecha_inicio ?: null,
                    $observaciones ?: null,
                    $hacer_actual ? 1 : ($bloque_historial['es_actual'] ? 1 : 0),
                    $bloque_id,
                    $concejal_id
                ]);

                // Si es el bloque actual y cambió el nombre, actualizar en la tabla principal
                if ($bloque_historial['es_actual'] || $hacer_actual) {
                    $stmt = $db->prepare("UPDATE concejales SET bloque = ? WHERE id = ?");
                    $stmt->execute([$nombre_bloque, $concejal_id]);
                }

                $mensaje = 'Bloque actualizado exitosamente';
                break;

            case 'eliminar':
                if ($bloque_historial['es_actual']) {
                    throw new Exception('No se puede eliminar el bloque actual');
                }

                $motivo_eliminacion = trim($_POST['motivo_eliminacion'] ?? '');

                // Marcar como eliminado (borrado lógico)
                $stmt = $db->prepare("
                    UPDATE concejal_bloques_historial 
                    SET eliminado = TRUE,
                        fecha_eliminacion = NOW(),
                        motivo_eliminacion = ?
                    WHERE id = ? AND concejal_id = ?
                ");
                $stmt->execute([
                    $motivo_eliminacion ?: 'Sin motivo especificado',
                    $bloque_id,
                    $concejal_id
                ]);

                $mensaje = 'Bloque eliminado del historial exitosamente';
                break;

            case 'marcar_actual':
                if ($bloque_historial['es_actual']) {
                    throw new Exception('Este bloque ya es el actual');
                }

                // Desactivar el bloque actual anterior
                $stmt = $db->prepare("
                    UPDATE concejal_bloques_historial 
                    SET es_actual = FALSE, 
                        fecha_fin = COALESCE(fecha_fin, CURDATE())
                    WHERE concejal_id = ? AND es_actual = TRUE
                ");
                $stmt->execute([$concejal_id]);

                // Marcar este bloque como actual y quitar fecha de fin si la tiene
                $stmt = $db->prepare("
                    UPDATE concejal_bloques_historial 
                    SET es_actual = TRUE,
                        fecha_fin = NULL
                    WHERE id = ? AND concejal_id = ?
                ");
                $stmt->execute([$bloque_id, $concejal_id]);

                // Actualizar el bloque en la tabla principal de concejales
                $stmt = $db->prepare("UPDATE concejales SET bloque = ? WHERE id = ?");
                $stmt->execute([$bloque_historial['nombre_bloque'], $concejal_id]);

                $mensaje = 'Bloque marcado como actual exitosamente';
                break;

            default:
                throw new Exception('Acción no implementada');
        }

        // Confirmar transacción
        $db->commit();

        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => $mensaje,
            'data' => [
                'accion' => $accion,
                'bloque_id' => $bloque_id,
                'concejal_id' => $concejal_id
            ]
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>