<?php
session_start();
header('Content-Type: application/json');

try {
    // Validar datos recibidos
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $concejal_id = filter_var($_POST['concejal_id'] ?? null, FILTER_VALIDATE_INT);
    $nombre_bloque = trim($_POST['nombre_bloque'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $observaciones = trim($_POST['observaciones'] ?? '');
    $hacer_actual = isset($_POST['hacer_actual']) && $_POST['hacer_actual'] === 'true';

    // Validaciones
    if (!$concejal_id) {
        throw new Exception('ID de concejal inválido');
    }

    if (empty($nombre_bloque)) {
        throw new Exception('El nombre del bloque es obligatorio');
    }

    if ($fecha_inicio && !DateTime::createFromFormat('Y-m-d', $fecha_inicio)) {
        throw new Exception('Formato de fecha inválido');
    }

    // Conectar a la base de datos
    require_once '../db/connection.php';
    $db = $pdo;

    // Verificar que el concejal existe
    $stmt = $db->prepare("SELECT id, bloque FROM concejales WHERE id = ?");
    $stmt->execute([$concejal_id]);
    $concejal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$concejal) {
        throw new Exception('Concejal no encontrado');
    }

    // Iniciar transacción
    $db->beginTransaction();

    try {
        // Verificar si la tabla de historial existe, si no, crearla
        $stmt = $db->prepare("SHOW TABLES LIKE 'concejal_bloques_historial'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $create_table_sql = "
                CREATE TABLE concejal_bloques_historial (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    concejal_id INT NOT NULL,
                    nombre_bloque VARCHAR(255) NOT NULL,
                    fecha_inicio DATE,
                    fecha_fin DATE,
                    es_actual BOOLEAN DEFAULT FALSE,
                    observacion TEXT,
                    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (concejal_id) REFERENCES concejales(id) ON DELETE CASCADE,
                    INDEX idx_concejal_id (concejal_id),
                    INDEX idx_es_actual (es_actual),
                    INDEX idx_fecha_inicio (fecha_inicio)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $db->exec($create_table_sql);
        }

        // Si se marca como actual, primero desactivar el bloque actual anterior
        if ($hacer_actual) {
            // Marcar bloque actual anterior como no actual y ponerle fecha de fin
            $stmt = $db->prepare("
                UPDATE concejal_bloques_historial 
                SET es_actual = FALSE, 
                    fecha_fin = COALESCE(fecha_fin, CURDATE())
                WHERE concejal_id = ? AND es_actual = TRUE
            ");
            $stmt->execute([$concejal_id]);

            // Actualizar el bloque en la tabla principal de concejales
            $stmt = $db->prepare("UPDATE concejales SET bloque = ? WHERE id = ?");
            $stmt->execute([$nombre_bloque, $concejal_id]);
        }

        // Insertar el nuevo bloque en el historial
        $stmt = $db->prepare("
            INSERT INTO concejal_bloques_historial 
            (concejal_id, nombre_bloque, fecha_inicio, es_actual, observacion, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $concejal_id,
            $nombre_bloque,
            $fecha_inicio ?: null,
            $hacer_actual ? 1 : 0,
            $observaciones ?: null
        ]);

        // Si no hay un bloque actual y este no se marca como actual, 
        // verificar si hay algún bloque marcado como actual
        if (!$hacer_actual) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM concejal_bloques_historial 
                WHERE concejal_id = ? AND es_actual = TRUE
            ");
            $stmt->execute([$concejal_id]);
            $tiene_actual = $stmt->fetchColumn();

            // Si no hay ningún bloque actual, marcar este como actual
            if ($tiene_actual == 0) {
                $stmt = $db->prepare("
                    UPDATE concejal_bloques_historial 
                    SET es_actual = TRUE 
                    WHERE concejal_id = ? AND nombre_bloque = ? 
                    ORDER BY fecha_registro DESC LIMIT 1
                ");
                $stmt->execute([$concejal_id, $nombre_bloque]);

                // Actualizar también en la tabla principal
                $stmt = $db->prepare("UPDATE concejales SET bloque = ? WHERE id = ?");
                $stmt->execute([$nombre_bloque, $concejal_id]);
            }
        }

        // Confirmar transacción
        $db->commit();

        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Bloque agregado exitosamente al historial',
            'data' => [
                'concejal_id' => $concejal_id,
                'nombre_bloque' => $nombre_bloque,
                'fecha_inicio' => $fecha_inicio,
                'es_actual' => $hacer_actual,
                'observaciones' => $observaciones
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