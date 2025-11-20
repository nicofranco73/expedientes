<?php
session_start();

// Incluir la conexión PDO. Asumo que la variable $db (objeto PDO) está disponible.
require_once('../db/connection.php');
// NOTA: Asumo que connection.php provee la variable $db o $pdo. 
// Usaremos $db = $pdo; si connection.php usa $pdo, o asumiremos que usa $db.
// Por consistencia con el código anterior, mantendré $db.
// Si connection.php usa $pdo, cambiar: $db = $pdo;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Validar ID
    if (empty($_POST['id'])) {
        throw new Exception('ID de expediente no proporcionado');
    }

    // Conectar a la base de datos
    /* CÓDIGO ELIMINADO: Eliminada la conexión new PDO(...) */
    
    // Iniciar transacción
    $db->beginTransaction();

    // Obtener lugar actual
    $stmt = $db->prepare("SELECT lugar FROM expedientes WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $lugar_anterior = $stmt->fetchColumn();

    // Obtener nombre completo del iniciador
    $iniciador_id = $_POST['iniciador'] ?? '';
    $nombre_iniciador = '';
    
    if (preg_match('/^(PF|PJ|CO)-(\d+)$/', $iniciador_id, $matches)) {
        $tipo = $matches[1];
        $id_iniciador = (int)$matches[2];
        
        // Usar la conexión $db centralizada para la consulta a Iniciadores, 
        // ya que el DBNAME es el mismo ('c2810161_iniciad').
        /* CÓDIGO ELIMINADO: Eliminada la segunda conexión new PDO(...) */
        
        if ($tipo === 'PF') {
            $stmt = $db->prepare("SELECT CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo FROM persona_fisica WHERE id = ?");
        } elseif ($tipo === 'PJ') {
            $stmt = $db->prepare("SELECT CONCAT(razon_social, ' (', cuit, ')') as nombre_completo FROM persona_juri_entidad WHERE id = ?");
        } elseif ($tipo === 'CO') {
            $stmt = $db->prepare("SELECT CONCAT(apellido, ', ', nombre, ' - ', bloque) as nombre_completo FROM concejales WHERE id = ?");
        } else {
             // Manejar caso donde el tipo no coincide, aunque el regex debería prevenirlo
             $stmt = null;
        }

        if ($stmt) {
             $stmt->execute([$id_iniciador]);
             $row = $stmt->fetch(PDO::FETCH_ASSOC);
             if ($row && !empty($row['nombre_completo'])) {
                 $nombre_iniciador = $row['nombre_completo'];
             }
        }
    }

    // Actualizar expediente
    $sql = "UPDATE expedientes 
            SET lugar = :lugar,
                extracto = :extracto,
                iniciador = :iniciador
            WHERE id = :id";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':lugar' => $_POST['lugar'],
        ':extracto' => $_POST['extracto'],
        ':iniciador' => $nombre_iniciador,
        ':id' => $_POST['id']
    ]);

    // Registrar cambio de lugar si cambió
    if ($lugar_anterior !== $_POST['lugar']) {
        // Registrar en historial
        $sql = "INSERT INTO historial_lugares (
                        expediente_id, 
                        lugar_anterior, 
                        lugar_nuevo
                    ) VALUES (?, ?, ?)";
                    
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_POST['id'],
            $lugar_anterior,
            $_POST['lugar']
        ]);
    }

    // Confirmar transacción
    $db->commit();

    $_SESSION['mensaje'] = "Expediente actualizado correctamente";
    $_SESSION['tipo_mensaje'] = "success";

} catch (Exception $e) {
    // Revertir cambios si hay error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    $_SESSION['mensaje'] = "Error al actualizar: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redireccionar
header("Location: listar_expedientes.php");
exit;
?>