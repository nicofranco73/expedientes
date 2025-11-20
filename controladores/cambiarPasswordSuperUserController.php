<?php
// ====================================================================
// CONTROLADOR: cambiarPasswordSuperUserController.php (Ola 4 - Corregido)
// Lógica de datos, variables de entorno y orquestación de la vista.
// ====================================================================

// 1. Inicia la sesión.
session_start();

// 2. Definición de la función de escape (e) para la vista
// NOTA: Se asume que esta función existe globalmente, pero la definimos aquí
// para simular su uso en la vista (ej. e($superuser['email']))
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

// 3. Asumimos que $superuser está definido
$superuser = $superuser ?? [
    'apellido' => 'Super', 
    'nombre' => 'Usuario', 
    'username' => 'admin', 
    'email' => 'admin@system.com'
];

// 4. Lógica de CSRF (Si es necesario, se genera aquí el token)
$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));


// 5. Preparamos las variables de sesión para la vista.
$mensaje_sesion = $_SESSION['mensaje'] ?? null;
$tipo_mensaje_sesion = $_SESSION['tipo_mensaje'] ?? 'info';

// Limpiamos las variables de sesión después de recuperarlas.
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);


// 6. Inclusión del Layout: header.php (contiene <head>, CSS global, y <body> de apertura)
// NOTA: Los CSS específicos de esta vista (como superuser_security.css) deberían 
// estar incluidos en header.php también si son necesarios.
require 'header.php'; 

// 7. Inclusión de la barra lateral (si aplica a esta página, aunque el HTML original no la usa en el layout principal)
// Dado que el HTML original no tiene la inclusión de sidebar, asumiremos que esta vista es de pantalla completa
// como una página de seguridad, y quitamos la división de main y row, pero por coherencia MVC lo mantenemos comentado.
// require 'sidebar.php'; 


// 8. 📌 LLAMADA A LA VISTA (HTML puro)
require '../vistas/cambiarPasswordSuperUser_view.php';

// 9. 📌 LLAMADA AL SCRIPT ESPECÍFICO DE LA VISTA (Aislamiento de JS)
require '../vistas/cambiarPasswordSuperUser_script.js';

// 10. Inclusión del Pie de Página (contiene JS global y el cierre de </body></html>)
require 'footer.php';
?>