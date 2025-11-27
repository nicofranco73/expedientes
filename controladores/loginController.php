<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../db/Database.php'; 
require_once '../models/UsuarioRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vistas/login.php');
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$password= $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    $_SESSION['mensaje'] = 'Completa todos los campos.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../vistas/login.php');
    exit;
}

try {
    $db = Database::conectar();
    $repo = new UsuarioRepository($db);
    $usuarioEncontrado = $repo->buscarPorUsuario($usuario);

    if ($usuarioEncontrado && password_verify($password, $usuarioEncontrado['password_hash'])) {
        
        unset($usuarioEncontrado['password_hash']);

        $_SESSION['usuario'] = $usuarioEncontrado;
        
        header('Location: ../vistas/dashboard.php'); 
        exit;

    } else {
        $_SESSION['mensaje'] = 'Usuario o contraseña incorrectos.';
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: ../vistas/login.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error fatal en el login: " . $e->getMessage());
    $_SESSION['mensaje'] = 'Error al intentar conectar con la base de datos.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: ../vistas/login.php');
    exit;
}