<?php
// CargaJuriEntidadController.php

session_start();

// 1. Lógica de Negocio y Control de Sesión

// La inclusión de header.php y head.php (que probablemente contienen la conexión a la base de datos, 
// la verificación de sesión y la estructura HTML inicial) se deja en la vista 
// si contienen HTML, o se refactoriza aquí si son puramente lógicos. 
// Asumiendo que 'header.php' y 'head.php' son componentes de la estructura global,
// los incluiremos aquí si son de LÓGICA o en la Vista si son de UI/HEADERS.
// Para mantener la consistencia con el archivo original, que los requiere antes de la vista,
// los requerimos aquí.

// **NOTA IMPORTANTE:** // - El archivo original requiere 'header.php' y 'head.php'. Si estos archivos contienen 
//   código HTML/estructura (como parece por el `require 'sidebar.php'` posterior), 
//   es mejor moverlos a la Vista.
// - Asumo que contienen Lógica esencial para iniciar la página y la sesión.
//   Si contienen HTML, debe mover el HTML a la vista.

require 'header.php';
// require 'head.php'; // Lo muevo a la vista si contiene etiquetas HTML

// 2. Recuperación de Datos de Sesión (Post-error/validación)
// Esta es lógica de presentación de datos que debe estar en el controlador.
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$mensaje = $_SESSION['mensaje'] ?? null;
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info';
if (isset($_SESSION['mensaje'])) {
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// 3. Pasar los datos a la Vista
// El controlador solo prepara los datos que la vista necesita.
$data = [
    'form_data' => $form_data,
    'mensaje' => $mensaje,
    'tipo_mensaje' => $tipo_mensaje,
    'title' => 'Nueva Persona Jurídica / Entidad',
    // Aquí se podrían cargar listas de la base de datos (Ej: Tipos de Entidad, Cargos)
    // Para el ejemplo, usamos los datos duros que estaban en el HTML.
];


// 4. Incluir la Vista
require 'view/CargaJuriEntidad.view.php';
?>