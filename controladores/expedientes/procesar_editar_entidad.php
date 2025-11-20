<?php
session_start();

try {
    // Verificar que se recibieron los datos necesarios
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $_SESSION['mensaje'] = "ID de entidad no válido";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

    // Guardar datos del formulario en sesión por si hay error
    $_SESSION['form_data'] = $_POST;

    $id = intval($_POST['id']);

    // Validar campos requeridos
    $errores = [];

    if (empty($_POST['tipo_entidad'])) {
        $errores[] = "El tipo de entidad es requerido";
    }

    if (empty($_POST['email'])) {
        $errores[] = "El email es requerido";
    }

    if (empty($_POST['domicilio'])) {
        $errores[] = "El domicilio es requerido";
    }

    if (empty($_POST['localidad'])) {
        $errores[] = "La localidad es requerida";
    }

    if (empty($_POST['provincia'])) {
        $errores[] = "La provincia es requerida";
    }

    if (!empty($errores)) {
        $_SESSION['mensaje'] = "Errores en el formulario:<br>• " . implode("<br>• ", $errores);
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: editar_persona_juri_entidad.php?id=' . $id);
        exit;
    }

    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;

    // Verificar que la entidad existe
    $sql_verificar = "SELECT id FROM persona_juri_entidad WHERE id = :id";
    $stmt_verificar = $db->prepare($sql_verificar);
    $stmt_verificar->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_verificar->execute();
    
    if (!$stmt_verificar->fetch()) {
        $_SESSION['mensaje'] = "La entidad no existe";
        $_SESSION['tipo_mensaje'] = "danger";
        header('Location: listar_persona_juri_entidad.php');
        exit;
    }

    // Preparar datos para actualizar
    $tipo_entidad = $_POST['tipo_entidad'] ?? '';
    
    // Validar tipo_entidad sin truncar (soporta códigos de hasta 10 caracteres)
    if ($tipo_entidad) {
        $tipo_entidad = strtoupper(trim($tipo_entidad));
        
        // Validar longitud máxima (VARCHAR(10))
        if (strlen($tipo_entidad) > 10) {
            throw new Exception("El código de tipo de entidad no puede exceder 10 caracteres");
        }
        
        // Si está vacío después del trim, establecer como cadena vacía
        if (empty($tipo_entidad)) {
            $tipo_entidad = '';
        }
    }
    
    // Validar y truncar cargo del representante si es necesario  
    $rep_cargo = $_POST['rep_cargo'] ?? '';
    if ($rep_cargo) {
        // Truncar a 2 caracteres (límite original)
        $rep_cargo = strtoupper(substr(trim($rep_cargo), 0, 2));
        
        // Si está vacío después del trim, establecer como cadena vacía
        if (empty($rep_cargo)) {
            $rep_cargo = '';
        }
    }
    
    $datos = [
        'razon_social' => $_POST['razon_social'] ?? null,
        'cuit' => $_POST['cuit'],
        'personeria' => $_POST['personeria'],
        'tipo_entidad' => $tipo_entidad,
        'otro_tipo' => trim($_POST['otro_tipo'] ?? ''),
        'web' => $_POST['web'] ?? null,
        'email' => $_POST['email'],
        'tel_fijo' => $_POST['tel_fijo'] ?? null,
        'tel_celular' => $_POST['tel_celular'] ?? null,
        'domicilio' => $_POST['domicilio'],
        'localidad' => $_POST['localidad'],
        'provincia' => $_POST['provincia'],
        'rep_nombre' => $_POST['rep_nombre'] ?? null,
        'rep_documento' => $_POST['rep_documento'] ?? null,
        'rep_cargo' => $rep_cargo,
        'rep_tel_fijo' => $_POST['rep_tel_fijo'] ?? null,
        'rep_tel_celular' => $_POST['rep_tel_celular'] ?? null,
        'rep_domicilio' => $_POST['rep_domicilio'] ?? null,
        'rep_email' => $_POST['rep_email'] ?? null
    ];

    // Actualizar la entidad
    $sql = "UPDATE persona_juri_entidad SET 
            razon_social = :razon_social,
            cuit = :cuit,
            personeria = :personeria,
            tipo_entidad = :tipo_entidad,
            otro_tipo = :otro_tipo,
            web = :web,
            email = :email,
            tel_fijo = :tel_fijo,
            tel_celular = :tel_celular,
            domicilio = :domicilio,
            localidad = :localidad,
            provincia = :provincia,
            rep_nombre = :rep_nombre,
            rep_documento = :rep_documento,
            rep_cargo = :rep_cargo,
            rep_tel_fijo = :rep_tel_fijo,
            rep_tel_celular = :rep_tel_celular,
            rep_domicilio = :rep_domicilio,
            rep_email = :rep_email
            WHERE id = :id";

    $stmt = $db->prepare($sql);
    
    // Bind de parámetros
    foreach ($datos as $campo => $valor) {
        $stmt->bindValue(":$campo", $valor);
    }
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Limpiar datos del formulario en sesión
        unset($_SESSION['form_data']);
        
        $_SESSION['mensaje'] = "Entidad actualizada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar la entidad";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error de base de datos: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir según el resultado
if (isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] === 'success') {
    header('Location: editar_persona_juri_entidad.php?id=' . $id);
} else {
    header('Location: editar_persona_juri_entidad.php?id=' . $id);
}
exit;
?>