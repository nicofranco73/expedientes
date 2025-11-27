<?php
// Aseguramos que la variable $base_url esté definida antes de su uso.
if (!isset($base_url)) {
    // 1. Obtener la ruta de la aplicación.
    // $_SERVER['SCRIPT_NAME'] puede ser: /expedientes/controladores/accionesExpedientesController.php
    $script_path = $_SERVER['SCRIPT_NAME'];
    
    // 2. Definir la raíz del proyecto.
    // Buscamos la carpeta 'expedientes' y la usamos como raíz. 
    // Si tu proyecto se llama diferente, ajusta la constante.
    $project_name = '/expedientes'; 
    
    // Buscamos dónde termina el nombre del proyecto en la ruta del script
    $pos = strpos($script_path, $project_name);
    
    if ($pos !== false) {
        // Extraemos solo el nombre del proyecto como base URL: /expedientes
        $base_url = substr($script_path, 0, $pos + strlen($project_name));
    } else {
        // Fallback: Si no se encuentra el nombre del proyecto, usamos la ruta del directorio
        $base_url = dirname(dirname($script_path));
    }
    
    // 3. Limpieza final de BASE_URL (siempre quitamos la barra final)
    $base_url = rtrim($base_url, '/');
}

// Nota: El archivo que llama a head.php (el controlador) debe definir $base_url antes de incluir head.php
// para una ruta más fiable, pero este fallback es robusto.

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Expedientes</title>

    <!-- BOOTSTRAP CSS (v. 5.3.3) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- ESTILOS PERSONALIZADOS (Ruta correcta ahora: /expedientes/vistas/publico/css/custom.css) -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/vistas/publico/css/custom.css">
    
    <!-- SCRIPT DE FIREBASE (Ruta correcta ahora: /expedientes/vistas/publico/js/firebase-init.js) -->
    <script type="module" src="<?php echo $base_url; ?>/vistas/publico/js/firebase-init.js"></script>

</head>
<body>
<!-- 
    INICIO DE LA ESTRUCTURA PRINCIPAL:
    El <body> se cierra en vistas/footer.php.
-->
    <div class="container-fluid">
        
        <!-- INICIO DE LA FILA QUE CONTIENE EL SIDEBAR Y EL CONTENIDO PRINCIPAL -->
        <div class="row flex-nowrap">
            <!-- NOTA: Aquí se incluirán sidebar.php (col-3) y el contenido (col-9) -->