<?php
session_start();

// Incluir la conexión PDO. Asumo que la variable $db (objeto PDO) está disponible.
require_once('../../db/connection.php'); 

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['mensaje'] = "Debe iniciar sesión para acceder a esta función";
    $_SESSION['tipo_mensaje'] = "danger";
    header('Location: login.php');
    exit;
}

// Verificar que se proporcionó el ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "ID de iniciador no válido";
    $_SESSION['tipo_mensaje'] = "danger";
    header('Location: listar_iniciadores.php');
    exit;
}

$id = intval($_GET['id']);

try {
    // Primero verificar que el iniciador existe
    $sql = "SELECT apellido, nombre FROM persona_fisica WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $iniciador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$iniciador) {
        $_SESSION['mensaje'] = "Iniciador no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_iniciadores.php');
        exit;
    }

    // Verificar si el iniciador está siendo usado en expedientes
    // La tabla expedientes tiene una columna 'iniciador' con el nombre completo
    $nombre_completo = $iniciador['apellido'] . ', ' . $iniciador['nombre'];
    $sql_check = "SELECT COUNT(*) as total FROM expedientes WHERE iniciador LIKE :iniciador_nombre";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute([':iniciador_nombre' => '%' . $nombre_completo . '%']);
    $expedientes_asociados = $stmt_check->fetch(PDO::FETCH_ASSOC)['total'];

    if ($expedientes_asociados > 0) {
        $_SESSION['mensaje'] = "No se puede eliminar el iniciador '{$iniciador['apellido']}, {$iniciador['nombre']}' porque tiene $expedientes_asociados expediente(s) asociado(s)";
        $_SESSION['tipo_mensaje'] = "warning";
        header('Location: listar_iniciadores.php');
        exit;
    }

    // Eliminar el iniciador
    $sql_delete = "DELETE FROM persona_fisica WHERE id = :id";
    $stmt_delete = $db->prepare($sql_delete);
    $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt_delete->execute()) {
        $_SESSION['mensaje'] = "Iniciador '{$iniciador['apellido']}, {$iniciador['nombre']}' eliminado exitosamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el iniciador";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error de base de datos: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error interno: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir de vuelta al listado
header('Location: listar_iniciadores.php');
exit;
?>