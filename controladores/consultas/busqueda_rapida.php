<?php
/**
 * Búsqueda rápida AJAX para expedientes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Función para escapar HTML
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

try {
    // Validar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener término de búsqueda
    $termino = trim($_POST['termino'] ?? '');
    
    if (empty($termino)) {
        throw new Exception('El término de búsqueda es requerido');
    }

    // ----------------------------------------------------
    // CORRECCIÓN CLAVE: Centralizar la Conexión PDO
    // El archivo está en 'controladores/consultas/', necesitamos salir dos niveles (../../)
    // para llegar a la carpeta 'db/'.
    require_once('../../db/connection.php');
    // La variable $db ahora está disponible.
    // ----------------------------------------------------

    // ----------------------------------------------------
    // CÓDIGO ELIMINADO: (Se ha eliminado el bloque 'new PDO(...)')
    /*
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    */
    // ----------------------------------------------------

    // Preparar búsqueda en múltiples campos
    $sql = "SELECT 
                id,
                numero,
                letra,
                folio,
                libro,
                anio,
                DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y') as fecha_ingreso,
                extracto,
                CASE 
                    WHEN LENGTH(extracto) > 50 
                    THEN CONCAT(SUBSTRING(extracto, 1, 50), '...') 
                    ELSE extracto 
                END as extracto_corto,
                iniciador,
                lugar
            FROM expedientes 
            WHERE numero LIKE :termino
                OR letra LIKE :termino
                OR folio LIKE :termino
                OR libro LIKE :termino
                OR anio LIKE :termino
                OR extracto LIKE :termino_texto
                OR iniciador LIKE :termino_texto
                OR lugar LIKE :termino_texto
            ORDER BY fecha_hora_ingreso DESC 
            LIMIT 8";

    $stmt = $db->prepare($sql);
    
    // Parámetros de búsqueda
    $termino_like = '%' . $termino . '%';
    $termino_texto = '%' . $termino . '%';
    
    $stmt->execute([
        ':termino' => $termino_like,
        ':termino_texto' => $termino_texto
    ]);

    $expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Escapar datos para seguridad
    foreach ($expedientes as &$exp) {
        $exp['numero'] = e($exp['numero']);
        $exp['letra'] = e($exp['letra']);
        $exp['folio'] = e($exp['folio']);
        $exp['libro'] = e($exp['libro']);
        $exp['anio'] = e($exp['anio']);
        $exp['extracto_corto'] = e($exp['extracto_corto']);
        $exp['iniciador'] = e($exp['iniciador']);
        $exp['lugar'] = e($exp['lugar']);
        $exp['fecha_ingreso'] = e($exp['fecha_ingreso']);
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'expedientes' => $expedientes,
        'total' => count($expedientes),
        'termino' => $termino
    ]);

} catch (PDOException $e) {
    // Error de base de datos
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Error general
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>