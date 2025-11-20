<?php
/**
 * Resultados de búsqueda pública de expedientes
 */

// Iniciar sesión y configuración
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Función para escapar output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

try {
    // Validar campos requeridos
    $campos_requeridos = ['numero', 'letra', 'folio', 'libro', 'anio', 'captcha'];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo '$campo' es requerido");
        }
    }

    // Debug: mostrar valores recibidos (comentar en producción)
    error_log("Valores recibidos - Número: " . $_POST['numero'] . ", Letra: " . $_POST['letra'] . ", Folio: " . $_POST['folio'] . ", Libro: " . $_POST['libro'] . ", Año: " . $_POST['anio']);

    // Sanitizar y validar inputs - MANTENER CEROS A LA IZQUIERDA
    $numero_original = trim($_POST['numero']);
    $letra = strtoupper(trim($_POST['letra']));
    $folio_original = trim($_POST['folio']);
    $libro_original = trim($_POST['libro']);
    $anio = (int)trim($_POST['anio']);

    // Validar que sean solo números pero conservar formato original
    if (!preg_match('/^[0-9]{1,6}$/', $numero_original)) {
        throw new Exception("El número del expediente debe contener solo dígitos (1-6 caracteres).");
    }
    if (!preg_match('/^[0-9]{1,6}$/', $folio_original)) {
        throw new Exception("El folio debe contener solo dígitos (1-6 caracteres).");
    }
    if (!preg_match('/^[0-9]{1,6}$/', $libro_original)) {
        throw new Exception("El libro debe contener solo dígitos (1-6 caracteres).");
    }

    // Convertir a enteros para validaciones de rango, pero conservar strings originales para la consulta
    $numero_int = (int)$numero_original;
    $folio_int = (int)$folio_original;
    $libro_int = (int)$libro_original;

    // Debug: mostrar valores después de validación
    error_log("Después de validación - Número original: '$numero_original' (int: $numero_int), Folio original: '$folio_original' (int: $folio_int), Libro original: '$libro_original' (int: $libro_int), Año: $anio");

    // Validar datos con mejor manejo de errores
    if ($numero_int < 1 || $numero_int > 999999) {
        throw new Exception("El número del expediente debe estar entre 1 y 999999.");
    }
    if ($folio_int < 1 || $folio_int > 999999) {
        throw new Exception("El folio debe estar entre 1 y 999999.");
    }
    if ($libro_int < 1 || $libro_int > 999999) {
        throw new Exception("El libro debe estar entre 1 y 999999.");
    }
    if ($anio < 1973 || $anio > 2030) {
        throw new Exception("El año debe estar entre 1973 y 2030.");
    }
    
    if (!preg_match('/^[A-Z]$/', $letra)) {
        throw new Exception("La letra es inválida. Debe ser una letra de A a Z.");
    }

    // Validar CAPTCHA
    if (!isset($_SESSION['captcha_code'])) {
        throw new Exception("Sesión de CAPTCHA inválida. Recargue la página.");
    }
    
    $captcha_ingresado = strtoupper(trim($_POST['captcha']));
    $captcha_correcto = $_SESSION['captcha_code'];
    
    if ($captcha_ingresado !== $captcha_correcto) {
        throw new Exception("El código de verificación es incorrecto. Código ingresado: '$captcha_ingresado', esperado: '$captcha_correcto'");
    }

   // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Consultar expediente - Usar tanto valores originales como enteros para máxima compatibilidad
    $sql = "SELECT * FROM expedientes 
            WHERE (numero = :numero_original OR numero = :numero_int)
            AND letra = :letra 
            AND (folio = :folio_original OR folio = :folio_int)
            AND (libro = :libro_original OR libro = :libro_int)
            AND anio = :anio 
            LIMIT 1";

    $stmt = $db->prepare($sql);
    
    // Debug: mostrar la consulta y parámetros
    error_log("Consulta SQL: " . $sql);
    error_log("Parámetros: numero_original='$numero_original', numero_int=$numero_int, letra='$letra', folio_original='$folio_original', folio_int=$folio_int, libro_original='$libro_original', libro_int=$libro_int, anio=$anio");
    
    $stmt->execute([
        ':numero_original' => $numero_original,
        ':numero_int' => $numero_int,
        ':letra' => $letra,
        ':folio_original' => $folio_original,
        ':folio_int' => $folio_int,
        ':libro_original' => $libro_original,
        ':libro_int' => $libro_int,
        ':anio' => $anio
    ]);

    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug: mostrar resultado
    error_log("Expediente encontrado: " . ($expediente ? "SI" : "NO"));

    // Agregar después de obtener el expediente
    if ($expediente) {
    // Consultar historial de lugares
    $sql = "SELECT 
            fecha_cambio,
            DATE_FORMAT(fecha_cambio, '%d/%m/%Y %H:%i') as fecha_formateada,
            lugar_anterior,
            lugar_nuevo,
            tipo_movimiento
        FROM historial_lugares 
        WHERE expediente_id = :id
        ORDER BY fecha_cambio ASC";
                
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $expediente['id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar después de obtener el historial
    if ($expediente && !empty($historial)) {
        // Calcular días transcurridos
        $primera_fecha = strtotime($expediente['fecha_hora_ingreso']);
        $ultima_fecha = strtotime(end($historial)['fecha_cambio']);
        
        $diferencia = $ultima_fecha - $primera_fecha;
        $dias_transcurridos = floor($diferencia / (60 * 60 * 24));
        $horas_transcurridas = floor(($diferencia % (60 * 60 * 24)) / (60 * 60));
    }

    // Limpiar CAPTCHA usado
    unset($_SESSION['captcha_code']);

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
?>
