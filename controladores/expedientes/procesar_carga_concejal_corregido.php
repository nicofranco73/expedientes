<?php
session_start();

// Activar manejo de errores mejorado
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = "Método de acceso no válido.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_concejal.php");
    exit;
}

try {
    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;

    // Verificar que la tabla existe, si no crearla
    $stmt = $db->query("SHOW TABLES LIKE 'concejales'");
    if ($stmt->rowCount() == 0) {
        $createTable = "CREATE TABLE concejales (
            id int(11) NOT NULL AUTO_INCREMENT,
            apellido varchar(100) NOT NULL,
            nombre varchar(100) NOT NULL,
            dni varchar(20) NOT NULL,
            direccion varchar(255) DEFAULT NULL,
            tel varchar(20) DEFAULT NULL,
            cel varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            bloque varchar(100) DEFAULT NULL,
            observacion text DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY dni (dni)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $db->exec($createTable);
    }

    // Validar campos requeridos
    $apellido = trim($_POST['apellido'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');

    if (empty($apellido)) {
        throw new Exception("El campo 'Apellido' es obligatorio.");
    }

    if (empty($nombre)) {
        throw new Exception("El campo 'Nombre' es obligatorio.");
    }

    if (empty($dni)) {
        throw new Exception("El campo 'DNI' es obligatorio.");
    }

    // Validar DNI único
    $stmt = $db->prepare("SELECT id FROM concejales WHERE dni = ?");
    $stmt->execute([$dni]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe un concejal registrado con el DNI: $dni");
    }

    // Validar email si se proporciona
    $email = trim($_POST['email'] ?? '');
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido.");
    }

    // Preparar los datos para insertar
    $datos = [
        'apellido' => $apellido,
        'nombre' => $nombre,
        'dni' => $dni,
        'direccion' => trim($_POST['direccion'] ?? ''),
        'tel' => trim($_POST['tel'] ?? ''),
        'cel' => trim($_POST['cel'] ?? ''),
        'email' => $email,
        'bloque' => trim($_POST['bloque'] ?? ''),
        'observacion' => trim($_POST['observacion'] ?? '')
    ];

    // Filtrar campos vacíos para la inserción
    $datos = array_filter($datos, function($valor) {
        return $valor !== '';
    });

    // Preparar la consulta SQL
    $campos = implode(', ', array_keys($datos));
    $valores = ':' . implode(', :', array_keys($datos));
    
    $sql = "INSERT INTO concejales ($campos) VALUES ($valores)";
    
    // Ejecutar la consulta
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($datos);
    
    if (!$result) {
        throw new Exception("Error al ejecutar la consulta de inserción.");
    }
    
    $id_insertado = $db->lastInsertId();
    
    if (!$id_insertado) {
        throw new Exception("Error: No se pudo obtener el ID del concejal insertado.");
    }

    // Verificar que se insertó correctamente
    $stmt = $db->prepare("SELECT apellido, nombre FROM concejales WHERE id = ?");
    $stmt->execute([$id_insertado]);
    $verificacion = $stmt->fetch();
    
    if (!$verificacion) {
        throw new Exception("Error: No se pudo verificar la inserción del concejal.");
    }

    // Mensaje de éxito con nombre del concejal
    $_SESSION['mensaje'] = "El concejal $apellido, $nombre ha sido registrado exitosamente.";
    $_SESSION['tipo_mensaje'] = "success";

    // Redireccionar
    header("Location: carga_concejal.php");
    exit;

} catch (PDOException $e) {
    // Error específico de base de datos
    $error_msg = "Error de base de datos: ";
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        $error_msg .= "No se puede conectar a la base de datos. Verifique que MySQL esté ejecutándose.";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $error_msg .= "Credenciales de base de datos incorrectas.";
    } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
        $error_msg .= "La base de datos o tabla no existe.";
    } else {
        $error_msg .= $e->getMessage();
    }
    
    $_SESSION['mensaje'] = $error_msg;
    $_SESSION['tipo_mensaje'] = "danger";
    $_SESSION['form_data'] = $_POST;
    header("Location: carga_concejal.php");
    exit;

} catch (Exception $e) {
    // Otros errores
    $_SESSION['mensaje'] = $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    $_SESSION['form_data'] = $_POST;
    header("Location: carga_concejal.php");
    exit;
}
?>