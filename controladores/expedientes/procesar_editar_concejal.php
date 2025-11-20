<?php
session_start();

// Activar manejo de errores mejorado
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log de debugging para edición
function debug_log($message) {
    $log_file = __DIR__ . '/debug_editar_concejal.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

debug_log("=== INICIO EDICIÓN CONCEJAL ===");
debug_log("Método: " . $_SERVER['REQUEST_METHOD']);
debug_log("Datos POST: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log("Error: Método no válido");
    $_SESSION['mensaje'] = "Método de acceso no válido.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_concejales.php");
    exit;
}

try {
    debug_log("Intentando conectar a la base de datos...");
    
    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;
    
    debug_log("Conexión exitosa");

    // Validar datos requeridos
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $apellido = trim($_POST['apellido'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $cel = trim($_POST['cel'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bloque = trim($_POST['bloque'] ?? '');
    $observacion = trim($_POST['observacion'] ?? '');

    debug_log("Datos validados: ID=$id, Apellido='$apellido', Nombre='$nombre', DNI='$dni'");

    if ($id <= 0) {
        debug_log("Error: ID no válido");
        throw new Exception("ID de concejal no válido.");
    }

    if (empty($apellido) || empty($nombre) || empty($dni)) {
        debug_log("Error: Campos obligatorios vacíos");
        throw new Exception("Los campos Apellido, Nombre y DNI son obligatorios.");
    }

    // Validar email si se proporciona
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        debug_log("Error: Email inválido - $email");
        throw new Exception("El formato del correo electrónico no es válido.");
    }

    // Verificar que el concejal existe
    debug_log("Verificando existencia del concejal...");
    $stmt = $db->prepare("SELECT apellido, nombre FROM concejales WHERE id = ?");
    $stmt->execute([$id]);
    $concejal_actual = $stmt->fetch();
    
    if (!$concejal_actual) {
        debug_log("Error: Concejal no existe");
        throw new Exception("El concejal no existe.");
    }
    
    debug_log("Concejal encontrado: " . $concejal_actual['apellido'] . ", " . $concejal_actual['nombre']);

    // Verificar DNI único (excluyendo el registro actual)
    debug_log("Verificando DNI único...");
    $stmt = $db->prepare("SELECT id FROM concejales WHERE dni = ? AND id != ?");
    $stmt->execute([$dni, $id]);
    if ($stmt->fetch()) {
        debug_log("Error: DNI duplicado - $dni");
        throw new Exception("Ya existe un concejal con el DNI: $dni");
    }

    // Actualizar concejal
    debug_log("Preparando actualización...");
    $sql = "UPDATE concejales SET 
            apellido = ?,
            nombre = ?,
            dni = ?,
            direccion = ?,
            tel = ?,
            cel = ?,
            email = ?,
            bloque = ?,
            observacion = ?
            WHERE id = ?";

    $stmt = $db->prepare($sql);
    $params = [
        $apellido,
        $nombre,
        $dni,
        $direccion,
        $tel,
        $cel,
        $email,
        $bloque,
        $observacion,
        $id
    ];
    
    debug_log("Ejecutando actualización con parámetros: " . print_r($params, true));
    
    $result = $stmt->execute($params);
    
    if (!$result) {
        debug_log("Error: Fallo en la ejecución de la actualización");
        throw new Exception("Error al actualizar los datos del concejal.");
    }
    
    $rows_affected = $stmt->rowCount();
    debug_log("Filas afectadas: $rows_affected");

    // Verificar que se actualizó
    $stmt = $db->prepare("SELECT apellido, nombre FROM concejales WHERE id = ?");
    $stmt->execute([$id]);
    $concejal_actualizado = $stmt->fetch();
    
    if ($concejal_actualizado) {
        debug_log("Verificación exitosa: " . $concejal_actualizado['apellido'] . ", " . $concejal_actualizado['nombre']);
    } else {
        debug_log("Error: No se pudo verificar la actualización");
        throw new Exception("Error al verificar la actualización del concejal.");
    }

    $_SESSION['mensaje'] = "Los datos del concejal $apellido, $nombre han sido actualizados exitosamente.";
    $_SESSION['tipo_mensaje'] = "success";
    
    debug_log("Éxito: Redirigiendo a editar_concejal.php?id=$id");
    header("Location: editar_concejal.php?id=$id");
    exit;

} catch (PDOException $e) {
    debug_log("Error PDO: " . $e->getMessage());
    debug_log("Línea: " . $e->getLine());
    
    // Error específico de base de datos
    $error_msg = "Error de base de datos: ";
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        $error_msg .= "No se puede conectar a la base de datos.";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $error_msg .= "Credenciales de base de datos incorrectas.";
    } else {
        $error_msg .= $e->getMessage();
    }
    
    $_SESSION['form_data'] = $_POST;
    $_SESSION['mensaje'] = $error_msg;
    $_SESSION['tipo_mensaje'] = "danger";
    
    if (isset($_POST['id']) && $_POST['id'] > 0) {
        header("Location: editar_concejal.php?id=" . $_POST['id']);
    } else {
        header("Location: listar_concejales.php");
    }
    exit;

} catch (Exception $e) {
    debug_log("Error general: " . $e->getMessage());
    debug_log("Línea: " . $e->getLine());
    debug_log("Stack trace: " . $e->getTraceAsString());
    
    // Guardar datos del formulario para repoblar
    $_SESSION['form_data'] = $_POST;
    $_SESSION['mensaje'] = $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    
    if (isset($_POST['id']) && $_POST['id'] > 0) {
        header("Location: editar_concejal.php?id=" . $_POST['id']);
    } else {
        header("Location: listar_concejales.php");
    }
    exit;
}

debug_log("=== FIN EDICIÓN CONCEJAL ===");
?>