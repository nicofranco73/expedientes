<?php
/**
 * Controlador para la página de Diagnóstico de Sesión.
 * Inicia la sesión y prepara todas las variables necesarias para la vista.
 */

session_start();

// 1. Obtener y preparar datos de la sesión
$session_data = $_SESSION;
$is_session_active = !empty($session_data);

// 2. Extracción de variables clave para la vista (con manejo de nulos)
$usuario_id = $session_data['usuario_id'] ?? null;
$username = $session_data['usuario'] ?? null;
$rol = $session_data['rol'] ?? null;

// 3. Lógica de permisos
$puedeCrearUsuarios = $rol === 'admin';

// 4. Incluir la Vista
require 'views/diagnosticoSesion.view.php';