<?php
/**
 * Procesamiento de carga de expedientes
 */

session_start();

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Función para sanear inputs
function sanear_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

try {
    // Validar método POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido");
    }

    // Validar campos requeridos
    $campos_requeridos = [
        'numero', 'letra', 'folio', 'libro', 'anio', 
        'fecha_hora_ingreso', 'lugar', 'extracto', 'iniciador'
    ];

    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("Todos los campos son obligatorios");
        }
    }

    // Validar longitud del extracto
    if (strlen($_POST['extracto']) > 300) {
        throw new Exception("El extracto no puede superar los 300 caracteres");
    }

    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;

    // Iniciar transacción
    $db->beginTransaction();

    // Obtener nombre completo del iniciador desde la base Iniciadores
    $iniciador_id = sanear_input($_POST['iniciador'] ?? '');
    $nombre_iniciador = '';
    if (preg_match('/^(PF|PJ|CO)-(\d+)$/', $iniciador_id, $matches)) {
        $tipo = $matches[1];
        $id = (int)$matches[2];
        
        // Usamos la misma conexión $db ya que apunta a la misma base de datos
        
        if ($tipo === 'PF') {
            $stmt = $db->prepare("SELECT CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo FROM persona_fisica WHERE id = ?");
        } elseif ($tipo === 'PJ') {
            $stmt = $db->prepare("SELECT CONCAT(razon_social, ' (', cuit, ')') as nombre_completo FROM persona_juri_entidad WHERE id = ?");
        } elseif ($tipo === 'CO') {
            $stmt = $db->prepare("SELECT CONCAT(apellido, ', ', nombre, ' - ', bloque) as nombre_completo FROM concejales WHERE id = ?");
        }
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['nombre_completo'])) {
            $nombre_iniciador = $row['nombre_completo'];
        }
    }

    // Preparar datos
    $data = [
        'numero' => sanear_input($_POST['numero']),
        'letra' => sanear_input($_POST['letra']),
        'folio' => sanear_input($_POST['folio']),
        'libro' => sanear_input($_POST['libro']),
        'anio' => filter_var($_POST['anio'], FILTER_VALIDATE_INT),
        'fecha_hora_ingreso' => sanear_input($_POST['fecha_hora_ingreso']),
        'lugar' => sanear_input($_POST['lugar'] ?? ''),
        'extracto' => sanear_input($_POST['extracto'] ?? ''),
        'iniciador' => $nombre_iniciador
    ];

    // Validar datos numéricos (permitiendo ceros a la izquierda)
    if (!preg_match('/^[0-9]{1,6}$/', $data['numero']) || 
        !preg_match('/^[0-9]{1,6}$/', $data['folio']) || 
        !preg_match('/^[0-9]{1,6}$/', $data['libro']) || 
        !$data['anio']) {
        throw new Exception("Datos numéricos inválidos");
    }

    if (!preg_match('/^[A-Z]$/', $data['letra'])) {
        throw new Exception("Letra inválida");
    }

    // Insertar expediente
    $sql = "INSERT INTO expedientes (
                numero, letra, folio, libro, anio, 
                fecha_hora_ingreso, lugar, extracto, iniciador
            ) VALUES (
                :numero, :letra, :folio, :libro, :anio,
                :fecha_hora_ingreso, :lugar, :extracto, :iniciador
            )";

    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    
    // Obtener el ID del expediente insertado
    $expediente_id = $db->lastInsertId();

    // Confirmar transacción
    $db->commit();

    $_SESSION['mensaje'] = "Expediente guardado correctamente";
    $_SESSION['tipo_mensaje'] = "success";
    $_SESSION['expediente_id'] = $expediente_id; // Guardar ID para el PDF

} catch (PDOException $e) {
    // Revertir transacción
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Error DB: " . $e->getMessage());
    
    if ($e->getCode() == 23000) { // Error de duplicado
        $_SESSION['mensaje'] = "El expediente ya existe en el sistema";
    } else {
        $_SESSION['mensaje'] = "Error al guardar el expediente";
    }
    $_SESSION['tipo_mensaje'] = "danger";

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['mensaje'] = $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redireccionar
header("Location: carga_expedientes.php");
exit;
?>