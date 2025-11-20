<?php
session_start();

// Incluir la conexión PDO. Asumo que la variable $db (objeto PDO) está disponible.
require_once('../../db/connection.php'); 

try {
    // Verificar que se recibió el ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['mensaje'] = "ID de entidad no válido";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

    $id = intval($_GET['id']);

    // Verificar que la entidad existe
    $sql_verificar = "SELECT razon_social FROM persona_juri_entidad WHERE id = :id";
    $stmt = $db->prepare($sql_verificar);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $entidad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entidad) {
        $_SESSION['mensaje'] = "La entidad no existe";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

    // Verificar si la entidad tiene expedientes asociados
    // Obtener datos completos de la entidad para formar el nombre como se almacena en expedientes
    $sql_entidad_completa = "SELECT razon_social, cuit FROM persona_juri_entidad WHERE id = :id";
    $stmt_completa = $db->prepare($sql_entidad_completa);
    $stmt_completa->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_completa->execute();
    $entidad_completa = $stmt_completa->fetch(PDO::FETCH_ASSOC);
    
    if ($entidad_completa) {
        // Formar el nombre como se almacena en expedientes
        if (!empty($entidad_completa['cuit'])) {
            $nombre_en_expedientes = $entidad_completa['razon_social'] . ' (' . $entidad_completa['cuit'] . ')';
        } else {
            $nombre_en_expedientes = $entidad_completa['razon_social'];
        }
        
        // Verificar expedientes asociados
        $sql_expedientes = "SELECT COUNT(*) as total FROM expedientes WHERE iniciador LIKE :iniciador_nombre";
        $stmt_exp = $db->prepare($sql_expedientes);
        $stmt_exp->execute([':iniciador_nombre' => '%' . $nombre_en_expedientes . '%']);
        $expedientes_asociados = $stmt_exp->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($expedientes_asociados > 0) {
            $_SESSION['mensaje'] = "No se puede eliminar la entidad '{$entidad_completa['razon_social']}' porque tiene {$expedientes_asociados} expediente(s) asociado(s)";
            $_SESSION['tipo_mensaje'] = "warning";
            header('Location: listar_persona_juri_entidad.php');
            exit;
        }
    }

    // Eliminar la entidad
    $sql_eliminar = "DELETE FROM persona_juri_entidad WHERE id = :id";
    $stmt_eliminar = $db->prepare($sql_eliminar);
    $stmt_eliminar->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt_eliminar->execute()) {
        $_SESSION['mensaje'] = "Entidad '" . htmlspecialchars($entidad['razon_social']) . "' eliminada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la entidad";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error de base de datos: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir de vuelta al listado
header('Location: listar_persona_juri_entidad.php');
exit;
?>