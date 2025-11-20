<?php
// ====================================================================
// CONTROLADOR DE VISTA: Prepara datos para la edición (GET request)
// ====================================================================

session_start();

// 1. REQUERIR LA CONEXIÓN CENTRALIZADA PDO
// Usamos la ruta y nombre de archivo que usted me proporcionó.
require_once('../../db/connection.php'); 

// Validar que el ID exista y sea un número
// Usamos filter_input para mayor seguridad al leer GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    // Redireccionar si no hay ID válido
    header("Location: listar_expedientes.php");
    exit;
}

// Variables iniciales
$expediente = null;
$personas_fisicas = [];
$personas_juridicas = [];
$concejales = [];
$iniciador_id = '';

try {
    // --- CONSULTA DEL EXPEDIENTE (USANDO LA VARIABLE $db del connection.php) ---
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE id = ?");
    $stmt->execute([$id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        $_SESSION['mensaje'] = "Expediente no encontrado";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: listar_expedientes.php");
        exit;
    }

    // --- CONSULTAR LISTA DE INICIADORES (USANDO $db) ---
    // (Consultas SQL omitidas por brevedad, pero usan $db->query y fetchAll)
    $stmt = $db->query("SELECT id, CONCAT(apellido, ', ', nombre, ' (', dni, ')') as nombre_completo FROM persona_fisica ORDER BY apellido, nombre");
    $personas_fisicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->query("SELECT id, CONCAT(razon_social, ' (', cuit, ')') as nombre_completo FROM persona_juri_entidad ORDER BY razon_social");
    $personas_juridicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->query("SELECT id, CONCAT(apellido, ', ', nombre, ' - ', bloque) as nombre_completo FROM concejales ORDER BY apellido, nombre");
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- LÓGICA DE MAPEO DEL INICIADOR (sin cambios) ---
    function normalizar_string($str) {
        return trim(strtolower(preg_replace('/\s+/', ' ', $str)));
    }
    
    $iniciador_normalizado = normalizar_string($expediente['iniciador'] ?? '');
    
    // ... Código de búsqueda de iniciador_id ...
    foreach ($personas_fisicas as $pf) {
        if (normalizar_string($pf['nombre_completo']) === $iniciador_normalizado) {
            $iniciador_id = 'PF-' . $pf['id'];
            break;
        }
    }
    
    if (!$iniciador_id) {
        foreach ($personas_juridicas as $pj) {
            if (normalizar_string($pj['nombre_completo']) === $iniciador_normalizado) {
                $iniciador_id = 'PJ-' . $pj['id'];
                break;
            }
        }
    }
    
    if (!$iniciador_id) {
        foreach ($concejales as $co) {
            if (normalizar_string($co['nombre_completo']) === $iniciador_normalizado) {
                $iniciador_id = 'CO-' . $co['id'];
                break;
            }
        }
    }

} catch (PDOException $e) {
    // Si hay un error de DB (fallo en la conexión o en alguna consulta)
    $_SESSION['mensaje'] = "Error al cargar el expediente: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_expedientes.php");
    exit;
}

// -------------------------------------------------------------------
// Incluimos la vista al final, pasándole todas las variables preparadas
// -------------------------------------------------------------------
require 'vistas/actualizar_expediente_vista.php';