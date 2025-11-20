<?php
// Iniciar sesión y cargar configuración y permisos
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php'; 
require_once __DIR__ . '/verificar_permisos.php';

// Verificar permisos para gestionar concejales antes de procesar
verificarPermisoVista('manage_concejales'); 

// Redirigir si no es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_concejales.php');
    exit;
}

// 1. Verificación de CSRF
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    setSessionMessage('Error de seguridad (CSRF). Intente de nuevo.', 'danger');
    header('Location: listar_concejales.php'); 
    exit;
}

// Limpiar el token después de usarlo para evitar reenvíos
unset($_SESSION['csrf_token']);

// 2. Recolección y Sanitización de Datos
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? null;
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
$dni = filter_input(INPUT_POST, 'dni', FILTER_SANITIZE_STRING); // Se asume que DNI es un string
$partido = filter_input(INPUT_POST, 'partido', FILTER_SANITIZE_STRING);
$circunscripcion = filter_input(INPUT_POST, 'circunscripcion', FILTER_SANITIZE_STRING);

$isEdit = !empty($id);

// 3. Validación de Datos (básica)
if (empty($nombre) || empty($apellido) || empty($dni) || empty($partido)) {
    setSessionMessage('Todos los campos obligatorios deben ser completados.', 'warning');
    // Redirige al formulario con el ID si es modo edición, o sin ID si es nuevo
    $redirect_url = $isEdit ? "cargaConcejal.php?id=" . $id : "cargaConcejal.php";
    header("Location: " . $redirect_url); 
    exit;
}

try {
    // 4. Conexión a la base de datos
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = "";
    $params = [
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':dni' => $dni,
        ':partido' => $partido,
        ':circunscripcion' => $circunscripcion
    ];
    $success_message = "";

    if ($isEdit) {
        // Modo Edición (UPDATE)
        $sql = "UPDATE concejales SET nombre = :nombre, apellido = :apellido, dni = :dni, partido = :partido, circunscripcion = :circunscripcion WHERE id = :id";
        $params[':id'] = $id;
        $success_message = 'Concejal actualizado exitosamente.';
    } else {
        // Modo Creación (INSERT)
        $sql = "INSERT INTO concejales (nombre, apellido, dni, partido, circunscripcion) VALUES (:nombre, :apellido, :dni, :partido, :circunscripcion)";
        $success_message = 'Concejal creado exitosamente.';
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    setSessionMessage($success_message, 'success');
    
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // 23000 es el código para violación de unicidad (p.ej., DNI duplicado)
        setSessionMessage('Error: El DNI ingresado ya existe en el sistema.', 'danger');
    } else {
        error_log("Error DB al procesar concejal: " . $e->getMessage());
        setSessionMessage('Error al guardar los datos en la base de datos.', 'danger');
    }

    // Mantener al usuario en el formulario si hubo un error.
    $redirect_url = $isEdit ? "cargaConcejal.php?id=" . $id : "cargaConcejal.php";
    header("Location: " . $redirect_url); 
    exit;
} catch (Exception $e) {
    error_log("Error General al procesar concejal: " . $e->getMessage());
    setSessionMessage('Ocurrió un error inesperado al procesar la solicitud.', 'danger');
}

// 5. Redirección final
header('Location: listar_concejales.php'); 
exit;
?>