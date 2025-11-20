<?php
session_start();

try {
    // Conectar a la base de datos
    require_once '../db/connection.php';
    $db = $pdo;

    // Validar datos obligatorios
    $campos_obligatorios = ['apellido', 'nombre', 'dni', 'bloque_actual'];
    foreach ($campos_obligatorios as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es obligatorio");
        }
    }

    // Verificar si el DNI ya existe
    $stmt = $db->prepare("SELECT id FROM concejales WHERE dni = ?");
    $stmt->execute([$_POST['dni']]);
    if ($stmt->fetch()) {
        throw new Exception("Ya existe un concejal con el DNI: " . $_POST['dni']);
    }

    // Crear tabla de historial de bloques si no existe (ANTES de la transacción)
    // IMPORTANTE: CREATE TABLE fuera de transacción evita problemas en servidores compartidos
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS concejal_bloques_historial (
                id INT AUTO_INCREMENT PRIMARY KEY,
                concejal_id INT NOT NULL,
                nombre_bloque VARCHAR(200) NOT NULL,
                fecha_inicio DATE,
                fecha_fin DATE NULL,
                es_actual BOOLEAN DEFAULT FALSE,
                observacion TEXT,
                eliminado BOOLEAN DEFAULT FALSE,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (concejal_id) REFERENCES concejales(id) ON DELETE CASCADE,
                INDEX idx_concejal_fecha (concejal_id, fecha_inicio)
            )
        ");
    } catch (Exception $e) {
        // Ignorar si la tabla ya existe
        if (strpos($e->getMessage(), 'already exists') === false) {
            throw $e;
        }
    }

    // Comenzar transacción
    $db->beginTransaction();

    // Insertar concejal principal
    $stmt = $db->prepare("
        INSERT INTO concejales (
            apellido, nombre, dni, direccion, email, tel, cel, 
            bloque, observacion, fecha_creacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_POST['apellido'],
        $_POST['nombre'],
        $_POST['dni'],
        $_POST['direccion'] ?? null,
        $_POST['email'] ?? null,
        $_POST['tel'] ?? null,
        $_POST['cel'] ?? null,
        $_POST['bloque_actual'], // El bloque actual se guarda en el campo principal
        $_POST['observacion'] ?? null
    ]);

    $concejal_id = $db->lastInsertId();

    // Insertar bloque actual en el historial
    $stmt = $db->prepare("
        INSERT INTO concejal_bloques_historial (
            concejal_id, nombre_bloque, fecha_inicio, es_actual, observacion
        ) VALUES (?, ?, ?, 1, ?)
    ");

    $fecha_inicio_actual = $_POST['fecha_inicio_bloque'] ?? date('Y-m-d');
    $stmt->execute([
        $concejal_id,
        $_POST['bloque_actual'],
        $fecha_inicio_actual,
        'Bloque actual al momento de la carga'
    ]);

    // Insertar bloques anteriores si existen
    if (!empty($_POST['bloques_anteriores'])) {
        $stmt_bloque = $db->prepare("
            INSERT INTO concejal_bloques_historial (
                concejal_id, nombre_bloque, fecha_inicio, fecha_fin, es_actual, observacion
            ) VALUES (?, ?, ?, ?, 0, ?)
        ");

        foreach ($_POST['bloques_anteriores'] as $bloque) {
            if (!empty($bloque['nombre'])) {
                $fecha_fin = !empty($bloque['fecha_fin']) ? $bloque['fecha_fin'] : null;
                
                $stmt_bloque->execute([
                    $concejal_id,
                    $bloque['nombre'],
                    $bloque['fecha_inicio'] ?? null,
                    $fecha_fin,
                    $bloque['observacion'] ?? null
                ]);
            }
        }
    }

    // Confirmar transacción
    $db->commit();

    $_SESSION['mensaje'] = "Concejal guardado exitosamente con historial de bloques";
    $_SESSION['tipo_mensaje'] = "success";
    $_SESSION['concejal_id'] = $concejal_id;
    
    // Redireccionar a la lista de concejales
    header("Location: listar_concejales.php");
    exit;

} catch (Exception $e) {
    // Revertir transacción en caso de error (si existe una activa)
    try {
        if (isset($db) && method_exists($db, 'inTransaction') && $db->inTransaction()) {
            $db->rollback();
        }
    } catch (Exception $rollbackError) {
        // Ignorar errores al hacer rollback
    }
    
    $_SESSION['mensaje'] = "Error al guardar el concejal: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    
    // Preservar datos del formulario
    $_SESSION['form_data'] = $_POST;
    
    // Redireccionar de vuelta al formulario
    header("Location: carga_concejal.php");
    exit;
}
?>