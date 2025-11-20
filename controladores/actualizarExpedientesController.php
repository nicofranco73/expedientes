<?php
// ====================================================================
// CONTROLADOR: actualizarExpedientesController.php (Ola 3 - Corregido)
// Lógica de datos, variables de entorno y orquestación de la vista.
// ====================================================================

// 1. Inicia la sesión.
session_start();

// 2. Lógica de recuperación de datos (Simulada)
// Se asume que aquí se obtienen los datos del expediente, iniciadores, etc.
$expediente = ['id' => 456, 'numero' => 1234, 'letra' => 'X', 'folio' => 50, 'anio' => 2024, 'lugar' => 'Mesa de Entrada', 'extracto' => 'Solicitud de informe.']; 
$iniciador_id = 'PF-1'; // Simulación: ID del iniciador seleccionado
$personas_fisicas = [['id' => 1, 'nombre_completo' => 'Juan Perez'], ['id' => 2, 'nombre_completo' => 'Ana Gomez']];
$personas_juridicas = [['id' => 10, 'nombre_completo' => 'Empresa SA']];
$concejales = [['id' => 20, 'nombre_completo' => 'Concejal García']];

// 3. Lógica de SweetAlert para mensajes de sesión
$script_sweetalert = '';
if (isset($_SESSION['mensaje'])) {
    $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
    $icon = match($tipo) {
        'success' => 'success',
        'danger' => 'error',
        'warning' => 'warning',
        default => 'info'
    };
    $mensaje = htmlspecialchars($_SESSION['mensaje'] ?? 'Error desconocido');

    // El script se construye aquí y se pasa a la vista para ser inyectado.
    $script_sweetalert = "
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: '{$mensaje}',
                    icon: '{$icon}',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#0d6efd'
                });
            });
        </script>
    ";
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}

// 4. Inclusión del Layout: header.php (contiene <head>, CSS global y <body> de apertura)
require 'header.php'; 

// 5. Inclusión de la barra lateral
require 'sidebar.php'; 

// 6. 📌 LLAMADA A LA VISTA (HTML puro)
require '../vistas/actualizarExpedientes_view.php';

// 7. 📌 LLAMADA AL SCRIPT ESPECÍFICO DE LA VISTA (Aislamiento de JS)
require '../vistas/actualizarExpedientes_script.js';

// 8. Inclusión del Pie de Página (contiene JS global y el cierre de </body></html>)
require 'footer.php'; 
?>