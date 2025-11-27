<?php
// controladores/listarUsuarioController.php

// 1. Incluir dependencias
require_once '../middleware/AuthMiddleware.php';
require_once '../db/Database.php';
require_once '../models/UsuarioRepository.php'; // Nuevo Repositorio

// --- MIDDLEWARE ---
// 2. Control de Permisos
AuthMiddleware::verificarPermiso('listar_usuarios.php'); // Nombre de la vista en tu matriz de permisos
// ------------------

try {
    // 3. Conexión e Inyección del Repositorio
    $db = Database::conectar();
    $repo = new UsuarioRepository($db); // Usamos el Repositorio de Usuarios
    
    // 4. Obtención de datos
    $usuarios = $repo->obtenerTodos(); // Llama al nuevo método
    
    // 5. Incluir la Vista (Asegúrate que el nombre de la vista sea correcto)
    require '../vistas/listarUsuarioVista.php'; 

} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error interno al cargar el listado de usuarios.';
    $_SESSION['tipo_mensaje'] = 'danger';
    error_log("Error en listarUsuarioController: " . $e->getMessage());
    header('Location: ../dashboard.php');
    exit;
}
?>