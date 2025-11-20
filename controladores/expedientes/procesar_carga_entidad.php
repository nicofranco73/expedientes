<?php
session_start();

try {
    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;

    // Validar email si se proporciona
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido");
    }

    // Validar URL de la web si se proporciona
    if (!empty($_POST['web']) && !filter_var($_POST['web'], FILTER_VALIDATE_URL)) {
        throw new Exception("El formato de la URL no es válido");
    }

    // Verificar si ya existe el CUIT (solo si se proporciona)
    if (!empty($_POST['cuit'])) {
        $stmt = $db->prepare("SELECT id FROM persona_juri_entidad WHERE cuit = ?");
        $stmt->execute([$_POST['cuit']]);
        if ($stmt->fetch()) {
            throw new Exception("Ya existe una entidad registrada con ese CUIT");
        }
    }

    // Preparar los datos para insertar
    $tipo_entidad = $_POST['tipo_entidad'] ?? null;
    
    // Validar tipo_entidad sin truncar (soporta códigos de hasta 10 caracteres)
    if ($tipo_entidad) {
        $tipo_entidad = strtoupper(trim($tipo_entidad));
        
        // Validar longitud máxima (VARCHAR(10))
        if (strlen($tipo_entidad) > 10) {
            throw new Exception("El código de tipo de entidad no puede exceder 10 caracteres");
        }
        
        // Si está vacío después del trim, establecer como null
        if (empty($tipo_entidad)) {
            $tipo_entidad = null;
        }
    }
    
    // Validar y truncar cargo del representante si es necesario
    $rep_cargo = $_POST['rep_cargo'] ?? null;
    if ($rep_cargo) {
        // Truncar a 2 caracteres (límite original)
        $rep_cargo = strtoupper(substr(trim($rep_cargo), 0, 2));
        
        // Si está vacío después del trim, establecer como null
        if (empty($rep_cargo)) {
            $rep_cargo = null;
        }
    }
    
    $datos = [
        'razon_social' => trim($_POST['razon_social'] ?? ''),
        'cuit' => trim($_POST['cuit'] ?? ''),
        'tipo_entidad' => $tipo_entidad,
        'otro_tipo' => trim($_POST['otro_tipo'] ?? ''),
        'personeria' => trim($_POST['personeria'] ?? ''),
        'domicilio' => trim($_POST['domicilio'] ?? ''),
        'localidad' => trim($_POST['localidad'] ?? ''),
        'provincia' => trim($_POST['provincia'] ?? ''),
        'tel_fijo' => trim($_POST['tel_fijo'] ?? ''),
        'tel_celular' => trim($_POST['tel_celular'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'web' => trim($_POST['web'] ?? ''),
        'rep_nombre' => trim($_POST['rep_nombre'] ?? ''),
        'rep_cargo' => $rep_cargo,
        'rep_documento' => trim($_POST['rep_documento'] ?? ''),
        'rep_domicilio' => trim($_POST['rep_domicilio'] ?? ''),
        'rep_tel_fijo' => trim($_POST['rep_tel_fijo'] ?? ''),
        'rep_tel_celular' => trim($_POST['rep_tel_celular'] ?? ''),
        'rep_email' => trim($_POST['rep_email'] ?? '')
    ];

    // Filtrar campos vacíos
    $datos = array_filter($datos, function($valor) {
        return $valor !== '' && $valor !== null;
    });

    // Si no hay datos para insertar
    if (empty($datos)) {
        throw new Exception("Debe proporcionar al menos un dato");
    }

    // Preparar la consulta SQL
    $campos = implode(', ', array_keys($datos));
    $valores = ':' . implode(', :', array_keys($datos));
    
    $sql = "INSERT INTO persona_juri_entidad ($campos) VALUES ($valores)";
    
    // Ejecutar la consulta
    $stmt = $db->prepare($sql);
    $stmt->execute($datos);

    // Mensaje de éxito
    $_SESSION['mensaje'] = "Entidad registrada correctamente";
    $_SESSION['tipo_mensaje'] = "success";

    // Redireccionar
    header("Location: carga_persona_juri_entidad.php");
    exit;

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    $_SESSION['form_data'] = $_POST;
    header("Location: carga_persona_juri_entidad.php");
    exit;
}