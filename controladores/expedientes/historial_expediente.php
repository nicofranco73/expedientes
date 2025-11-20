
<?php
header('Content-Type: application/json');

try {
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception("ID inválido");
    }

     // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "SELECT fecha_cambio, lugar_anterior, lugar_nuevo 
            FROM historial_lugares 
            WHERE expediente_id = :id 
            ORDER BY fecha_cambio DESC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($historial);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}