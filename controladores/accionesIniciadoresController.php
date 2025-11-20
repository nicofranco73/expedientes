<?php
// ====================================================================
// CONTROLADOR: accionesIniciadoresController.php (Ola 2 - Corregido)
// Lógica de inicialización y control de flujo.
// ====================================================================

// 1. Inicia la sesión.
session_start();

// 2. Inclusión del Encabezado (contiene <head>, CSS global y <body> de apertura)
require 'header.php'; 

// 3. Inclusión de la barra lateral
require 'sidebar.php'; 

// Lógica de datos (en este caso, no hay lógica compleja)
// ...

// 4. 📌 LLAMADA A LA VISTA
require '../vistas/accionesIniciadores_view.php'; // Cambiado el nombre para simplificar: _view

// 5. Inclusión del Pie de Página (contiene JS global y el cierre de </body></html>)
require 'footer.php';
?>