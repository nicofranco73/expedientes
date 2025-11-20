<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar que se proporcionó el ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de iniciador no válido']);
    exit;
}

$id = intval($_GET['id']);

try {
    // -----------------------------------------------------------------
    // CONEXIÓN CORREGIDA: Usar el archivo de conexión centralizado.
    require_once('../db/connection.php'); 
    // La variable $db (conexión PDO) ahora está disponible.
    // -----------------------------------------------------------------

    // Consultar los detalles del iniciador
    $sql = "SELECT * FROM persona_fisica WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $iniciador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($iniciador) {
        // Formatear algunos campos para mejor presentación
        if ($iniciador['fecha_nacimiento']) {
            $fecha = DateTime::createFromFormat('Y-m-d', $iniciador['fecha_nacimiento']);
            if ($fecha) {
                $iniciador['fecha_nacimiento'] = $fecha->format('d/m/Y');
            }
        }
        
        if ($iniciador['fecha_creacion']) {
            $fecha = DateTime::createFromFormat('Y-m-d H:i:s', $iniciador['fecha_creacion']);
            if ($fecha) {
                $iniciador['fecha_creacion'] = $fecha->format('d/m/Y H:i');
            }
        }
        
        echo json_encode([
            'success' => true, 
            'iniciador' => $iniciador
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Iniciador no encontrado'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>