<?php
session_start();

// Debug temporal - verificar que el script se ejecuta
error_log("Procesando carga de iniciador - " . date('Y-m-d H:i:s'));

try {
    // Validar datos recibidos
    $requeridos = ['apellido', 'nombre', 'dni'];
    foreach ($requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo $campo es obligatorio");
        }
    }

    // Sanitizar datos básicos
    $datos_basicos = [
        'apellido' => filter_var(trim($_POST['apellido']), FILTER_SANITIZE_STRING),
        'nombre' => filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING),
        'dni' => filter_var(trim($_POST['dni']), FILTER_SANITIZE_STRING),
        'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : '',
        'tel' => filter_var(trim($_POST['tel'] ?? ''), FILTER_SANITIZE_STRING),
        'cel' => filter_var(trim($_POST['cel'] ?? ''), FILTER_SANITIZE_STRING),
        'observaciones' => filter_var(trim($_POST['observaciones'] ?? ''), FILTER_SANITIZE_STRING)
    ];

    // Construir dirección básica
    $direccion_partes = array_filter([
        trim($_POST['calle'] ?? ''),
        trim($_POST['numero'] ?? ''),
        trim($_POST['piso'] ?? '') ? "Piso: " . trim($_POST['piso']) : null,
        trim($_POST['depto'] ?? '') ? "Depto: " . trim($_POST['depto']) : null,
        trim($_POST['localidad'] ?? ''),
        trim($_POST['cp'] ?? '') ? "CP: " . trim($_POST['cp']) : null
    ]);
    $direccion_completa = implode(', ', $direccion_partes);
    
    $datos_basicos['direccion'] = $direccion_completa;

    // Validar email si se proporcionó
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del email no es válido");
    }

    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $pdo = $pdo; // Mantenemos la variable $pdo ya que el código original la usaba

    // Verificar si el DNI ya existe
    $stmt = $pdo->prepare("SELECT id FROM persona_fisica WHERE dni = ?");
    $stmt->execute([$datos_basicos['dni']]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe una persona registrada con ese DNI");
    }

    // Verificar qué columnas existen en la tabla
    $stmt = $pdo->query("DESCRIBE persona_fisica");
    $columnas_existentes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columnas_existentes[] = $row['Field'];
    }

    // Determinar qué campo usar para observaciones
    $campo_observaciones = in_array('observaciones', $columnas_existentes) ? 'observaciones' : 'observacion';

    // Preparar la consulta SQL con campos básicos
    $sql = "INSERT INTO persona_fisica (
                apellido, nombre, dni, direccion, tel, cel, email, $campo_observaciones
            ) VALUES (
                :apellido, :nombre, :dni, :direccion, :tel, :cel, :email, :observaciones
            )";

    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute($datos_basicos);
    
    if ($resultado) {
        // Guardar mensaje de éxito
        $_SESSION['mensaje'] = "Iniciador registrado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
        
        // Debug temporal
        error_log("Mensaje de sesión establecido: " . $_SESSION['mensaje']);
    } else {
        throw new Exception("Error al insertar el registro en la base de datos");
    }

    // Redireccionar de vuelta al formulario para mostrar SweetAlert
    header("Location: carga_iniciador.php");
    exit;

} catch (Exception $e) {
    // Guardar mensaje de error
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    
    // Guardar datos del formulario para recuperarlos
    $_SESSION['form_data'] = $_POST;
    
    // Debug temporal
    error_log("Error en procesamiento: " . $e->getMessage());
    
    // Redireccionar
    header("Location: carga_iniciador.php");
    exit;
}
?>