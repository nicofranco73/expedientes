
<?php
// Usamos __DIR__ para asegurar que las rutas sean absolutas, 
// independientemente de dónde se ejecute el script.

// 1. Cargar el entorno, la seguridad, la sesión y las utilidades (como e() y $captcha).
require_once __DIR__ . '/core/bootstrap.php'; 

// 2. Incluir y renderizar la vista del formulario público.
// Las variables preparadas en bootstrap.php (ej. $captcha) están disponibles aquí.
require_once __DIR__ . '/vistas/consulta_publica.php';