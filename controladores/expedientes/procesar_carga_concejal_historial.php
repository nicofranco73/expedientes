<?php
// ======================================================================
// Archivo: procesar_carga_concejal_historial.php (Controlador COMPLETO y MODIFICADO)
// Objetivo: Recibir el POST, validar, sanear y guardar Concejal y su historial.
// ======================================================================
session_start();

// 1. Redirección si no es POST y dependencias
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = "Método de acceso no válido.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: carga_concejal.php");
    exit;
}

// Incluir la conexión PDO. Asumo que $pdo está disponible tras incluirlo.
require_once '../db/connection.php';
$db = $pdo; // Usamos $db para claridad en el controlador

try {
    // 2. Extracción, Sanitización y Validación de Datos
    
    // Sanitización (trim) y manejo de valores opcionales (NULL si está vacío)
    $datosConcejal = [
        'apellido' => trim($_POST['apellido'] ?? ''),
        'nombre' => trim($_POST['nombre'] ?? ''),
        'dni' => trim($_POST['dni'] ?? ''),
        'bloque_actual' => trim($_POST['bloque_actual'] ?? ''),
        // Los campos opcionales que pueden ser NULL en la BD
        'direccion' => trim($_POST['direccion'] ?? '') ?: null,
        'email' => trim($_POST['email'] ?? '') ?: null,
        'tel' => trim($_POST['tel'] ?? '') ?: null,
        'cel' => trim($_POST['cel'] ?? '') ?: null,
        'observacion' => trim($_POST['observacion'] ?? '') ?: null,
        'fecha_inicio_bloque' => $_POST['fecha_inicio_bloque'] ?? date('Y-m-d')
    ];

    $bloquesHistorial = $_POST['bloques_anteriores'] ?? [];
    
    // Validación obligatoria
    $campos_obligatorios = ['apellido', 'nombre', 'dni', 'bloque_actual'];
    foreach ($campos_obligatorios as $campo) {
        if (empty($datosConcejal[$campo])) {
            throw new Exception("El campo **" . ucfirst($campo) . "** es obligatorio.");
        }
    }
    
    // Opcional: Validación de Email (Si se proporciona)
    if (!empty($datosConcejal['email']) && !filter_var($datosConcejal['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido.");
    }

    // 3. Verificar Unicidad de DNI
    $stmt = $db->prepare("SELECT id FROM concejales WHERE dni = ?");
    $stmt->execute([$datosConcejal['dni']]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe un concejal registrado con el DNI: **" . $datosConcejal['dni'] . "**");
    }

    // 4. Comenzar Transacción
    $db->beginTransaction();

    // 5. Insertar concejal principal
    $stmt = $db->prepare("
        INSERT INTO concejales (
            apellido, nombre, dni, direccion, email, tel, cel, 
            bloque, observacion, fecha_creacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $datosConcejal['apellido'],
        $datosConcejal['nombre'],
        $datosConcejal['dni'],
        $datosConcejal['direccion'],
        $datosConcejal['email'],
        $datosConcejal['tel'],
        $datosConcejal['cel'],
        $datosConcejal['bloque_actual'],
        $datosConcejal['observacion']
    ]);

    $concejal_id = $db->lastInsertId();

    if (!$concejal_id) {
        throw new Exception("Fallo al obtener el ID del concejal. Transacción abortada.");
    }
    
    // NOTA: Se asume que la tabla `concejal_bloques_historial` ya existe o se crea fuera del proceso de POST.

    // 6. Insertar bloque actual en el historial (es_actual = 1)
    $stmt_actual = $db->prepare("
        INSERT INTO concejal_bloques_historial (
            concejal_id, nombre_bloque, fecha_inicio, es_actual, observacion
        ) VALUES (?, ?, ?, 1, ?)
    ");

    $stmt_actual->execute([
        $concejal_id,
        $datosConcejal['bloque_actual'],
        $datosConcejal['fecha_inicio_bloque'],
        'Bloque actual al momento de la carga'
    ]);

    // 7. Insertar bloques anteriores si existen (es_actual = 0)
    if (!empty($bloquesHistorial)) {
        $stmt_bloque = $db->prepare("
            INSERT INTO concejal_bloques_historial (
                concejal_id, nombre_bloque, fecha_inicio, fecha_fin, es_actual, observacion
            ) VALUES (?, ?, ?, ?, 0, ?)
        ");

        foreach ($bloquesHistorial as $bloque) {
            // Sanitización y uso de NULL para la BD
            $nombre_bloque = trim($bloque['nombre'] ?? '') ?: null;
            
            // Solo insertamos si el nombre del bloque anterior existe
            if ($nombre_bloque) {
                $fecha_inicio = trim($bloque['fecha_inicio'] ?? '') ?: null;
                $fecha_fin = trim($bloque['fecha_fin'] ?? '') ?: null;
                $observacion_bloque = trim($bloque['observacion'] ?? '') ?: null;
                
                // Opción de validación de fechas (para evitar errores lógicos)
                if ($fecha_inicio && $fecha_fin && (strtotime($fecha_fin) < strtotime($fecha_inicio))) {
                     // Podríamos registrar un error, pero por ahora solo saltamos el bloque.
                     continue; 
                }

                $stmt_bloque->execute([
                    $concejal_id,
                    $nombre_bloque,
                    $fecha_inicio,
                    $fecha_fin,
                    $observacion_bloque
                ]);
            }
        }
    }

    // 8. Confirmar transacción
    $db->commit();

    $_SESSION['mensaje'] = "El concejal **{$datosConcejal['apellido']}, {$datosConcejal['nombre']}** ha sido registrado exitosamente.";
    $_SESSION['tipo_mensaje'] = "success";
    $_SESSION['concejal_id'] = $concejal_id;

} catch (Exception $e) {
    // 9. Revertir transacción en caso de error
    if ($db && $db->inTransaction()) {
        $db->rollback();
    }
    
    $_SESSION['mensaje'] = "Error al guardar el concejal: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    
    // Preservar datos del formulario para que no se pierdan
    $_SESSION['form_data'] = $_POST;
}

// 10. Redireccionar de vuelta al formulario
header("Location: carga_concejal.php");
exit;
?>