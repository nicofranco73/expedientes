<?php
/**
 * Diagnóstico Rápido del Sistema de Bloques
 * Para verificar por qué no se muestran los bloques de concejales
 */

header('Content-Type: text/html; charset=utf-8');

try {
    // Conectar a la base de datos usando las mismas credenciales del sistema
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad", 
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Diagnóstico de Bloques - Sistema de Expedientes</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>";
    echo "</head>";
    echo "<body class='bg-light'>";
    echo "<div class='container mt-4'>";
    
    echo "<div class='row justify-content-center'>";
    echo "<div class='col-lg-10'>";
    
    // Título principal
    echo "<div class='card border-0 shadow-sm mb-4'>";
    echo "<div class='card-header bg-primary text-white'>";
    echo "<h1 class='h4 mb-0'><i class='bi bi-gear-fill me-2'></i>Diagnóstico del Sistema de Bloques</h1>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<p class='mb-0'>Verificando el estado del sistema de bloques para concejales...</p>";
    echo "</div>";
    echo "</div>";

    // 1. Verificar tabla de concejales
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-info text-white'>";
    echo "<h5 class='mb-0'><i class='bi bi-people-fill me-2'></i>1. Concejales en el Sistema</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM concejales");
    $total_concejales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div class='alert alert-info'>";
    echo "<strong>Total de concejales:</strong> {$total_concejales}";
    echo "</div>";
    
    if ($total_concejales > 0) {
        $stmt = $db->query("SELECT id, nombre, apellido, bloque FROM concejales ORDER BY apellido, nombre LIMIT 5");
        echo "<h6>Muestra de concejales:</h6>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-sm'>";
        echo "<thead><tr><th>ID</th><th>Nombre</th><th>Bloque Actual</th></tr></thead>";
        echo "<tbody>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['apellido']}, {$row['nombre']}</td>";
            echo "<td>" . ($row['bloque'] ?: '<em>Sin bloque</em>') . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";

    // 2. Verificar tabla de historial de bloques
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-warning text-dark'>";
    echo "<h5 class='mb-0'><i class='bi bi-clock-history me-2'></i>2. Tabla de Historial de Bloques</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    
    // Verificar si existe la tabla
    $stmt = $db->query("SHOW TABLES LIKE 'concejal_bloques_historial'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if ($tabla_existe) {
        echo "<div class='alert alert-success'>";
        echo "<i class='bi bi-check-circle-fill me-2'></i><strong>La tabla existe correctamente</strong>";
        echo "</div>";
        
        // Contar registros
        $stmt = $db->query("SELECT COUNT(*) as total FROM concejal_bloques_historial");
        $total_bloques = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<div class='alert alert-info'>";
        echo "<strong>Registros de bloques:</strong> {$total_bloques}";
        echo "</div>";
        
        if ($total_bloques > 0) {
            // Mostrar estructura de datos
            $stmt = $db->query("
                SELECT cbh.*, c.nombre, c.apellido 
                FROM concejal_bloques_historial cbh 
                LEFT JOIN concejales c ON c.id = cbh.concejal_id 
                ORDER BY cbh.es_actual DESC, cbh.fecha_inicio DESC 
                LIMIT 5
            ");
            
            echo "<h6>Muestra de datos de bloques:</h6>";
            echo "<div class='table-responsive'>";
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>Concejal</th><th>Bloque</th><th>Estado</th><th>Fechas</th></tr></thead>";
            echo "<tbody>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>{$row['apellido']}, {$row['nombre']}</td>";
                echo "<td>{$row['nombre_bloque']}</td>";
                echo "<td>";
                if ($row['es_actual']) {
                    echo "<span class='badge bg-success'>ACTUAL</span>";
                } else {
                    echo "<span class='badge bg-secondary'>HISTÓRICO</span>";
                }
                echo "</td>";
                echo "<td>";
                echo $row['fecha_inicio'] ? date('d/m/Y', strtotime($row['fecha_inicio'])) : 'Sin fecha';
                if ($row['fecha_fin']) {
                    echo " → " . date('d/m/Y', strtotime($row['fecha_fin']));
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger'>";
            echo "<i class='bi bi-exclamation-triangle-fill me-2'></i><strong>¡PROBLEMA ENCONTRADO!</strong><br>";
            echo "La tabla existe pero no tiene datos. Los bloques no se han migrado.";
            echo "</div>";
        }
        
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<i class='bi bi-x-circle-fill me-2'></i><strong>¡PROBLEMA ENCONTRADO!</strong><br>";
        echo "La tabla 'concejal_bloques_historial' no existe. Esto explica por qué no se muestran los bloques.";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";

    // 3. Solución
    echo "<div class='card mb-4'>";
    echo "<div class='card-header bg-success text-white'>";
    echo "<h5 class='mb-0'><i class='bi bi-tools me-2'></i>3. Solución</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    
    if (!$tabla_existe || ($tabla_existe && $total_bloques == 0)) {
        echo "<div class='alert alert-warning'>";
        echo "<h6><i class='bi bi-wrench-adjustable me-2'></i>Configuración Requerida</h6>";
        echo "<p>Para que funcione la selección de bloques de concejales, necesita ejecutar el script de configuración.</p>";
        echo "</div>";
        
        echo "<div class='d-grid gap-2'>";
        echo "<a href='setup_bloques.php' class='btn btn-primary btn-lg'>";
        echo "<i class='bi bi-play-fill me-2'></i>Ejecutar Configuración Automática";
        echo "</a>";
        echo "</div>";
        
        echo "<div class='mt-3'>";
        echo "<small class='text-muted'>";
        echo "<i class='bi bi-info-circle me-1'></i>";
        echo "El script creará la tabla necesaria y migrará los datos existentes automáticamente.";
        echo "</small>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-success'>";
        echo "<h6><i class='bi bi-check-circle-fill me-2'></i>Sistema Configurado</h6>";
        echo "<p>El sistema de bloques está correctamente configurado. Los bloques deberían mostrarse en el formulario.</p>";
        echo "</div>";
        
        echo "<div class='d-grid gap-2'>";
        echo "<a href='carga_expedientes.php?debug=1' class='btn btn-info btn-lg'>";
        echo "<i class='bi bi-bug-fill me-2'></i>Ver Modo Debug";
        echo "</a>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";

    // 4. Enlaces útiles
    echo "<div class='card'>";
    echo "<div class='card-header bg-secondary text-white'>";
    echo "<h5 class='mb-0'><i class='bi bi-link-45deg me-2'></i>Enlaces Útiles</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<a href='carga_expedientes.php' class='btn btn-outline-primary w-100 mb-2'>";
    echo "<i class='bi bi-file-earmark-plus me-2'></i>Volver a Carga de Expedientes";
    echo "</a>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    echo "<a href='listar_concejales.php' class='btn btn-outline-info w-100 mb-2'>";
    echo "<i class='bi bi-people me-2'></i>Ver Lista de Concejales";
    echo "</a>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "</div>"; // col
    echo "</div>"; // row
    echo "</div>"; // container
    echo "</body>";
    echo "</html>";

} catch (Exception $e) {
    echo "<!DOCTYPE html>";
    echo "<html><head><meta charset='UTF-8'><title>Error</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head>";
    echo "<body class='bg-light'>";
    echo "<div class='container mt-5'>";
    echo "<div class='alert alert-danger'>";
    echo "<h4><i class='bi bi-exclamation-triangle-fill'></i> Error de Conexión</h4>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<hr>";
    echo "<p><strong>Posibles causas:</strong></p>";
    echo "<ul>";
    echo "<li>Credenciales de base de datos incorrectas</li>";
    echo "<li>Servidor MySQL no disponible</li>";
    echo "<li>Base de datos no existe</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    echo "</body></html>";
}
?>