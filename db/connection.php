<?php
// Conexión PDO reutilizable
// Ajustar host/usuario/clave/db según entorno XAMPP.
try {
    $pdo = new PDO("mysql:host=localhost;dbname=proyectoexpedientes;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Exception $e) {
    // En producción loggear el error en archivo y mostrar mensaje genérico
    die('Error de conexión. ' . $e->getMessage());
}
