
<?php
session_start();

require_once '../middleware/AuthMiddleware.php';
require_once '../db/Database.php';
require_once '../models/ExpedienteRepository.php';

// --- MIDDLEWARE ---
// 2. Control de Permisos
AuthMiddleware::verificarPermiso('procesar_pase.php'); 
// ------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php'); // Si no es POST, salir
    exit;
}

try {
    // 3. Validación y Saneamiento de Datos del Formulario
    $id_expediente = filter_input(INPUT_POST, 'id_expediente', FILTER_VALIDATE_INT);
    $fecha_pase    = filter_input(INPUT_POST, 'fecha_pase', FILTER_SANITIZE_SPECIAL_CHARS);
    $destino       = filter_input(INPUT_POST, 'destino', FILTER_SANITIZE_SPECIAL_CHARS);
    $observaciones = filter_input(INPUT_POST, 'observaciones', FILTER_SANITIZE_SPECIAL_CHARS);
    $usuario_id    = $_SESSION['usuario_id'] ?? 0; // Obtener de la sesión

    // Validación mínima
    if (!$id_expediente || empty($fecha_pase) || empty($destino) || $usuario_id === 0) {
        $_SESSION['mensaje'] = 'Datos de pase incompletos o inválidos.';
        $_SESSION['tipo_mensaje'] = 'danger';
        // Redirigir de vuelta al expediente
        header('Location: ../pases_expediente.php?id=' . $id_expediente);
        exit;
    }

    // 4. Preparar el array para el Repositorio (Modelo)
    $datos_pase = [
        'id_expediente' => $id_expediente,
        'fecha_pase'    => $fecha_pase,
        'destino'       => $destino,
        'observaciones' => $observaciones,
        'usuario_id'    => $usuario_id
    ];

    // 5. Conexión e Inyección del Repositorio
    $db = Database::conectar();
    $repo = new ExpedienteRepository($db);
    
    // 6. Ejecución de la Acción (El Controlador usa el Modelo)
    if ($repo->crearPase($datos_pase)) {
        $_SESSION['mensaje'] = 'Pase registrado exitosamente.';
        $_SESSION['tipo_mensaje'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al registrar el pase. Intente nuevamente.';
        $_SESSION['tipo_mensaje'] = 'danger';
    }

    // 7. Redirección final al listado de pases del expediente
    header('Location: ../pases_expediente.php?id=' . $id_expediente);
    exit;

} catch (Exception $e) {
    // Manejo de errores genérico
    $_SESSION['mensaje'] = 'Error interno del sistema.';
    $_SESSION['tipo_mensaje'] = 'danger';
    error_log("Error en procesar_pase: " . $e->getMessage());
    header('Location: ../dashboard.php');
    exit;
}
?>