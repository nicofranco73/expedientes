<?php
// ====================================================================
// CONTROLADOR: accionesExpedientesController.php (Ola 1 - Corregido)
// Lógica de inicialización y control de flujo.
// ====================================================================

// Inicia la sesión.
session_start();

// 1. Inclusión del Encabezado (contiene <head>, CSS global y <body> de apertura)
// NOTA: Se ha quitado 'head.php' asumiendo que ya está dentro de 'header.php' o no es necesario.
require 'header.php'; 

// 2. Inclusión de la barra lateral (si aplica a la vista)
require 'sidebar.php'; 

// Lógica de datos (en este caso, no hay lógica compleja)
// ...

// 3. 📌 LLAMADA A LA VISTA
require '../vistas/accionesExpedientes_view.php';

// 4. Inclusión del Pie de Página (contiene JS global y el cierre de </body></html>)
require 'footer.php';
?>