<?php
session_start();

// Incluir la conexión PDO. Asumo que la variable $db está disponible.
require_once('../../db/connection.php'); 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "ID de concejal no válido.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_concejales.php");
    exit;
}

$id = intval($_GET['id']);

try {
    // Verificar que el concejal existe
    $stmt = $db->prepare("SELECT apellido, nombre FROM concejales WHERE id = ?");
    $stmt->execute([$id]);
    $concejal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$concejal) {
        throw new Exception("El concejal no existe.");
    }

    // Verificar si el concejal tiene expedientes asociados
    // Primero necesitamos determinar el nombre correcto de la columna
    $stmt = $db->query("SHOW COLUMNS FROM expedientes LIKE '%concejal%'");
    $concejal_column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $expedientes_asociados = 0;
    
    if ($concejal_column) {
        // Si existe una columna relacionada con concejal
        $column_name = $concejal_column['Field'];
        $stmt = $db->prepare("SELECT COUNT(*) FROM expedientes WHERE $column_name = ?");
        $stmt->execute([$id]);
        $expedientes_asociados = $stmt->fetchColumn();
    } else {
        // Buscar otras posibles relaciones (iniciador, autor, etc.)
        $possible_columns = ['iniciador_id', 'autor_id', 'responsable_id'];
        foreach ($possible_columns as $col) {
            $stmt = $db->query("SHOW COLUMNS FROM expedientes LIKE '$col'");
            if ($stmt->rowCount() > 0) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM expedientes WHERE $col = ?");
                $stmt->execute([$id]);
                $expedientes_asociados += $stmt->fetchColumn();
            }
        }
    }

    if ($expedientes_asociados > 0) {
        throw new Exception("No se puede eliminar el concejal {$concejal['apellido']}, {$concejal['nombre']} porque tiene $expedientes_asociados expediente(s) asociado(s). Primero debe reasignar o eliminar los expedientes relacionados.");
    }

    // Eliminar concejal
    $stmt = $db->prepare("DELETE FROM concejales WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['mensaje'] = "Concejal {$concejal['apellido']}, {$concejal['nombre']} eliminado exitosamente.";
    $_SESSION['tipo_mensaje'] = "success";

} catch (Exception $e) {
    $_SESSION['mensaje'] = $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: listar_concejales.php");
exit;
?>