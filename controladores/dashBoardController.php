<?php
/**
 * Controlador principal para la página del Dashboard.
 * Maneja la verificación de sesión y la obtención de datos para la vista.
 */

// 1. Incluir verificación de sesión (y arranque de sesión)
require_once 'utils/session_check.php';
require_once 'DashboardModel.php';

// 2. Inicializar Modelo
try {
    $dashboardModel = new DashboardModel();
    $stats = $dashboardModel->getStats();
} catch (Exception $e) {
    // Manejo de error de modelo/BD (si el modelo lo lanza)
    $stats = [
        'expedientes_totales' => 'N/A',
        'expedientes_hoy' => 'N/A',
        'pendientes' => 'N/A',
    ];
    // Enviar mensaje de error a la vista o loguear
    error_log("Error al cargar estadísticas: " . $e->getMessage());
}

// 3. Incluir la Vista (le pasamos $stats, $is_superuser, $username, $role)
require 'views/dashboard.view.php';