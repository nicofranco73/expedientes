<?php
// pases_expediente.php (Anteriormente pasesEspediente.php)
session_start();

// 1. Headers (Se mantiene aquí por ser lógica de control inicial)
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// 2. Incluir Utilidades (Contiene la lógica de BD)

//  Incluir dependencias (Rutas ajustadas según tu estructura)
require_once '../middleware/AuthMiddleware.php';
require_once '../db/Database.php';
require_once '../models/ExpedienteRepository.php';

// --- MIDDLEWARE ---
// 2. Control de Permisos (la vista a proteger es 'pases_expediente.php')
AuthMiddleware::verificarPermiso('pases_expediente.php'); 
// ------------------

try {
    // 3. Validación de ID (asumo que tienes una función de validación en otra utilidad)
    $id = $_GET['id'] ?? null;
    
    // Si no hay ID, o el ID es inválido, redirigir
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        $_SESSION['mensaje'] = 'ID de expediente inválido.';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: ../listar_expedientes.php'); 
        exit;
    }

    // 4. Conexión e Inyección del Repositorio (Hablar con el Modelo)
    $db = Database::conectar(); // Usamos la clase estática
    $repo = new ExpedienteRepository($db);
    
    // 5. Obtención de datos (El Controlador solo coordina)
    $expediente = $repo->obtenerExpedientePorId((int)$id);
    
    if (!$expediente) {
        $_SESSION['mensaje'] = 'El expediente solicitado no existe.';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: ../listar_expedientes.php'); 
        exit;
    }

    $historial = $repo->obtenerHistorialPases((int)$id);

    // 6. Incluir la Vista (Pasar las variables limpias)
    // Asegúrate de que esta ruta sea correcta
    require '../vistas/pases_expediente_vista.php'; 

} catch (Exception $e) {
    // Manejo de errores genérico (p. ej., si falla la conexión)
    $_SESSION['mensaje'] = 'Error interno del sistema al cargar el expediente.';
    $_SESSION['tipo_mensaje'] = 'danger';
    error_log("Error en pasesExpedienteController: " . $e->getMessage());
    header('Location: ../listar_expedientes.php');
    exit;
}