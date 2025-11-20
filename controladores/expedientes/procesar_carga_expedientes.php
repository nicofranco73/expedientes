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

    // Debug: Registrar todos los datos POST recibidos
    error_log("=== INICIO PROCESAMIENTO EXPEDIENTE ===");
    error_log("POST recibido: " . print_r($_POST, true));

    // Validar campos requeridos
    $campos_requeridos = [
        'numero', 'letra', 'folio', 'libro', 'anio', 
        'fecha_hora_ingreso', 'lugar', 'extracto', 'iniciador'
    ];

    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            error_log("Campo faltante: " . $campo);
            throw new Exception("Todos los campos son obligatorios (falta: $campo)");
        }
    }
 
    // Conectar a la base de datos
    require_once '../db/connection.php'; // Incluir la conexión PDO
    $db = $pdo;

    // Iniciar transacción
    $db->beginTransaction();

    // Obtener nombre completo del iniciador desde la base Iniciadores
    $iniciador_id = sanear_input($_POST['iniciador'] ?? '');
    error_log("Iniciador ID recibido del formulario: '$iniciador_id'");
    
    $nombre_iniciador = '';
    if (preg_match('/^(PF|PJ|CO)-(\d+)$/', $iniciador_id, $matches)) {
        $tipo = $matches[1];
        $id = (int)$matches[2];
<<<<<<< HEAD:controladores/expedientes/procesar_carga_expedientes.php
        
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
=======
        error_log("Tipo detectado: $tipo, ID: $id");
        
        $db_iniciadores = new PDO(
            "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
            "c2810161_iniciad",
            "li62veMAdu",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        if ($tipo === 'PF') {
            $stmt = $db_iniciadores->prepare("SELECT CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo FROM persona_fisica WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Resultado PF query: " . print_r($row, true));
            if ($row && !empty($row['nombre_completo'])) {
                $nombre_iniciador = $row['nombre_completo'];
                error_log("Nombre PF asignado: $nombre_iniciador");
            }
        } elseif ($tipo === 'PJ') {
            $stmt = $db_iniciadores->prepare("SELECT CONCAT(razon_social, ' (', cuit, ')') as nombre_completo FROM persona_juri_entidad WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Resultado PJ query: " . print_r($row, true));
            if ($row && !empty($row['nombre_completo'])) {
                $nombre_iniciador = $row['nombre_completo'];
                error_log("Nombre PJ asignado: $nombre_iniciador");
            }
        } elseif ($tipo === 'CO') {
            // Para concejales, verificar si se seleccionó un bloque específico
            $bloque_seleccionado = sanear_input($_POST['bloque_concejal_seleccionado'] ?? '');
            error_log("Bloque concejal seleccionado: '$bloque_seleccionado'");
            
            if (!empty($bloque_seleccionado)) {
                // Usar el bloque específico seleccionado
                $stmt = $db_iniciadores->prepare("SELECT CONCAT(apellido, ', ', nombre) as nombre_completo FROM concejales WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Resultado CO con bloque específico: " . print_r($row, true));
                if ($row && !empty($row['nombre_completo'])) {
                    $nombre_iniciador = $row['nombre_completo'] . ' - ' . $bloque_seleccionado;
                    error_log("Nombre CO con bloque asignado: $nombre_iniciador");
                }
            } else {
                // Usar el bloque actual por defecto
                $stmt = $db_iniciadores->prepare("SELECT CONCAT(apellido, ', ', nombre, ' - ', bloque) as nombre_completo FROM concejales WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Resultado CO con bloque actual: " . print_r($row, true));
                if ($row && !empty($row['nombre_completo'])) {
                    $nombre_iniciador = $row['nombre_completo'];
                    error_log("Nombre CO asignado: $nombre_iniciador");
                }
            }
>>>>>>> 38a24694d49b8b11acc5ca3ec202ce34db8ed6ec:vistas/procesar_carga_expedientes.php
        }
    } else {
        error_log("ERROR: El formato del iniciador_id no coincide con el patrón esperado: $iniciador_id");
    }
    
    // Debug temporal - Remover después de verificar
    error_log("Nombre iniciador final antes de validación: '$nombre_iniciador'");
    
    // Verificar que se obtuvo un nombre de iniciador
    if (empty($nombre_iniciador)) {
        error_log("ERROR CRÍTICO: nombre_iniciador está vacío");
        throw new Exception("Error al obtener los datos del iniciador. Por favor, intente nuevamente.");
    }
    
    error_log("Iniciador validado correctamente: $nombre_iniciador");

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
    
    error_log("Expediente insertado exitosamente con iniciador: " . $data['iniciador']);
    
    // Obtener el ID del expediente insertado
    $expediente_id = $db->lastInsertId();
    error_log("ID del nuevo expediente: $expediente_id");

    // Confirmar transacción
    $db->commit();
    error_log("=== FIN PROCESAMIENTO EXITOSO ===");

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