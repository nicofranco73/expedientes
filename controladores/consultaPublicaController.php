<?php
session_start();

// **1. SEGURIDAD**: Verifica la autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../vistas/login.php");
    exit;
}

// **2. CONFIGURACIÓN DE LA VISTA**
// Define la variable para resaltar 'Consulta Pública' en el sidebar
$Vista_actual = 'consulta_publica'; 

// **3. CARGAR EL LAYOUT**
include_once '../vistas/head.php';
include_once '../vistas/header.php';
include_once '../vistas/sidebar.php';

// **4. CONTENIDO ESPECÍFICO**
// Aquí se incluiría la vista principal de Consulta Pública
include_once '../vistas/consulta_publica.php';

include_once '../vistas/footer.php';
?>