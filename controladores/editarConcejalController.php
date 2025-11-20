<?php
session_start();

// Incluir el modelo
require_once __DIR__ . '/../models/ConcejalModel.php';

// Redireccionar si el ID es inválido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensaje'] = "ID de concejal no válido.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_concejales.php");
    exit;
}

$id = intval($_GET['id']);

try {
    // 1. Obtener datos del Concejal
    $concejalModel = new ConcejalModel();
    $concejal = $concejalModel->getConcejalById($id);

    // 2. Verificar si el concejal existe
    if (!$concejal) {
        $_SESSION['mensaje'] = "El concejal no existe.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: listar_concejales.php");
        exit;
    }

} catch (Exception $e) {
    // 3. Manejo de error de base de datos/conexión
    $_SESSION['mensaje'] = "Error al cargar el concejal: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: listar_concejales.php");
    exit;
}

// 4. Preparar datos para la Vista: Usar datos del POST si hubo un error previo, sino usar datos de la BD.
$form_data = $_SESSION['form_data'] ?? $concejal;
$id = $concejal['id']; // Asegurar que el ID es el correcto.

// Limpiar datos del POST que pudieron causar un error
unset($_SESSION['form_data']);

// 5. Cargar la vista de edición
require_once __DIR__ . '/../vista/editar_concejal_view.php';