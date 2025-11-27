<?php
// controladores/test_busqueda_rapida.php

// 1. Incluir dependencias (Las rutas asumen que el archivo está en la carpeta 'controladores')
require_once '../middleware/AuthMiddleware.php';
require_once '../db/Database.php';
require_once '../models/ExpedienteRepository.php';

// --- MIDDLEWARE ---
// 2. Control de Permisos
AuthMiddleware::verificarPermiso('busqueda_rapida.php'); 
// ------------------

try {
    // 3. Recepción y Saneamiento del término de búsqueda (asumiendo método GET)
    $termino = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);
    $resultados = [];
    $mensaje_busqueda = '';

    if (!empty($termino)) {
        // 4. Conexión e Inyección del Repositorio
        $db = Database::conectar();
        $repo = new ExpedienteRepository($db);
        
        // 5. Ejecución de la Acción (El Controlador solo pide los datos al Modelo)
        $resultados = $repo->busquedaRapida($termino);
        
        $conteo = count($resultados);
        $mensaje_busqueda = "Se encontraron $conteo resultados para: \"$termino\"";
        
    } else {
        $mensaje_busqueda = "Ingrese un término para realizar la búsqueda rápida.";
    }

    // 6. Incluir la Vista (Pasar las variables limpias: $resultados y $mensaje_busqueda)
    // Se asume que la vista de resultados está en 'vistas/resultados_publicos.php'
    require '../vistas/resultados_publicos.php'; 

} catch (Exception $e) {
    // Manejo de errores genérico
    $_SESSION['mensaje'] = 'Error interno del sistema durante la búsqueda.';
    $_SESSION['tipo_mensaje'] = 'danger';
    error_log("Error en controlador de búsqueda: " . $e->getMessage());
    header('Location: ../dashboard.php');
    exit;
}
?>