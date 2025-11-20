<?php
/**
 * Script de diagnóstico para verificar consultas de expedientes en DonWeb
 * Fecha: 22 de octubre de 2025
 */

// Evitar timeout en consultas largas
set_time_limit(60);
ini_set('memory_limit', '256M');

// Mostrar errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validar acceso con password
$password_debug = $_GET['pass'] ?? '';
if ($password_debug !== 'debug2025') {
    die('❌ Acceso denegado. Use: ?pass=debug2025');
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico Expedientes - DonWeb</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #007bff; color: white; }
        .highlight { background: #fff3cd; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔍 Diagnóstico Completo de Expedientes - Servidor DonWeb</h1>
<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. VERIFICAR CONEXIÓN A BASE DE DATOS
    echo "<div class='section'>
    <h2>🔌 1. Verificación de Conexión a Base de Datos</h2>";
    
    $start_time = microtime(true);
    
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 30
        ]
    );
    
    $connection_time = round((microtime(true) - $start_time) * 1000, 2);
    
    echo "✅ <span class='success'>Conexión exitosa a la base de datos c2810161_iniciad</span><br>";
    echo "⏱️ Tiempo de conexión: {$connection_time}ms<br>";
    echo "🖥️ Servidor MySQL: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    echo "</div>";
    
    // 2. VERIFICAR ESTRUCTURA DE TABLA EXPEDIENTES
    echo "<div class='section'>
    <h2>🗃️ 2. Estructura de Tabla 'expedientes'</h2>";
    
    $stmt = $db->query("DESCRIBE expedientes");
    $columns = $stmt->fetchAll();
    
    echo "<table>
    <tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>
        <td>" . htmlspecialchars($col['Field']) . "</td>
        <td>" . htmlspecialchars($col['Type']) . "</td>
        <td>" . htmlspecialchars($col['Null']) . "</td>
        <td>" . htmlspecialchars($col['Key']) . "</td>
        <td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>
        </tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 3. CONTAR EXPEDIENTES TOTALES
    echo "<div class='section'>
    <h2>📊 3. Estadísticas de Expedientes</h2>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM expedientes");
    $total = $stmt->fetch()['total'];
    echo "📁 <strong>Total de expedientes:</strong> {$total}<br>";
    
    if ($total > 0) {
        // Expedientes por año
        $stmt = $db->query("SELECT anio, COUNT(*) as cantidad FROM expedientes GROUP BY anio ORDER BY anio DESC");
        $por_anio = $stmt->fetchAll();
        
        echo "<h4>📅 Expedientes por año:</h4>";
        echo "<table><tr><th>Año</th><th>Cantidad</th></tr>";
        foreach ($por_anio as $anio) {
            echo "<tr><td>{$anio['anio']}</td><td>{$anio['cantidad']}</td></tr>";
        }
        echo "</table>";
        
        // Últimos 5 expedientes ingresados
        $stmt = $db->query("SELECT numero, letra, folio, libro, anio, DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y %H:%i') as fecha_ingreso, iniciador 
                           FROM expedientes 
                           ORDER BY fecha_hora_ingreso DESC 
                           LIMIT 5");
        $ultimos = $stmt->fetchAll();
        
        echo "<h4>📝 Últimos 5 expedientes ingresados:</h4>";
        echo "<table>
        <tr><th>Número</th><th>Letra</th><th>Folio</th><th>Libro</th><th>Año</th><th>Fecha Ingreso</th><th>Iniciador</th></tr>";
        foreach ($ultimos as $exp) {
            echo "<tr>
            <td>{$exp['numero']}</td>
            <td>{$exp['letra']}</td>
            <td>{$exp['folio']}</td>
            <td>{$exp['libro']}</td>
            <td>{$exp['anio']}</td>
            <td>{$exp['fecha_ingreso']}</td>
            <td>" . substr($exp['iniciador'], 0, 30) . "...</td>
            </tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // 4. PROBAR CONSULTAS DE BÚSQUEDA
    echo "<div class='section'>
    <h2>🔍 4. Pruebas de Consultas de Búsqueda</h2>";
    
    // Test 1: Búsqueda por año reciente
    echo "<h4>Test 1: Búsqueda por año 2024</h4>";
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM expedientes WHERE anio = ?");
    $stmt->execute([2024]);
    $result = $stmt->fetch();
    echo "📋 Expedientes del 2024: {$result['total']}<br>";
    
    // Test 2: Búsqueda por letra
    echo "<h4>Test 2: Búsqueda por letra 'A'</h4>";
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM expedientes WHERE letra = ?");
    $stmt->execute(['A']);
    $result = $stmt->fetch();
    echo "📋 Expedientes con letra 'A': {$result['total']}<br>";
    
    // Test 3: Búsqueda rápida (texto libre)
    echo "<h4>Test 3: Búsqueda rápida con término 'expediente'</h4>";
    $termino = '%expediente%';
    $sql = "SELECT COUNT(*) as total FROM expedientes 
            WHERE numero LIKE ? 
               OR letra LIKE ? 
               OR extracto LIKE ? 
               OR iniciador LIKE ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$termino, $termino, $termino, $termino]);
    $result = $stmt->fetch();
    echo "📋 Expedientes que contienen 'expediente': {$result['total']}<br>";
    
    // Test 4: Consulta compleja combinada
    if ($total > 0) {
        echo "<h4>Test 4: Consulta combinada (letra + año)</h4>";
        $stmt = $db->prepare("SELECT numero, letra, folio, libro, anio, iniciador 
                             FROM expedientes 
                             WHERE letra IN ('A', 'B', 'C') AND anio >= 2020 
                             ORDER BY anio DESC, numero ASC 
                             LIMIT 3");
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        
        if (!empty($resultados)) {
            echo "<table>
            <tr><th>Número</th><th>Letra</th><th>Folio</th><th>Libro</th><th>Año</th><th>Iniciador</th></tr>";
            foreach ($resultados as $exp) {
                echo "<tr>
                <td>{$exp['numero']}</td>
                <td>{$exp['letra']}</td>
                <td>{$exp['folio']}</td>
                <td>{$exp['libro']}</td>
                <td>{$exp['anio']}</td>
                <td>" . substr($exp['iniciador'], 0, 25) . "...</td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "⚠️ <span class='warning'>No se encontraron resultados</span><br>";
        }
    }
    echo "</div>";
    
    // 5. PROBAR RENDIMIENTO DE CONSULTAS
    echo "<div class='section'>
    <h2>⚡ 5. Pruebas de Rendimiento</h2>";
    
    // Consulta simple
    $start = microtime(true);
    $stmt = $db->query("SELECT COUNT(*) FROM expedientes");
    $stmt->fetch();
    $time_simple = round((microtime(true) - $start) * 1000, 2);
    echo "🕒 Consulta COUNT simple: {$time_simple}ms<br>";
    
    // Consulta con JOIN (si existen tablas relacionadas)
    $start = microtime(true);
    $stmt = $db->query("SELECT * FROM expedientes ORDER BY fecha_hora_ingreso DESC LIMIT 1");
    $stmt->fetch();
    $time_order = round((microtime(true) - $start) * 1000, 2);
    echo "🕒 Consulta con ORDER BY: {$time_order}ms<br>";
    
    // Consulta con LIKE
    $start = microtime(true);
    $stmt = $db->prepare("SELECT * FROM expedientes WHERE extracto LIKE ? LIMIT 5");
    $stmt->execute(['%con%']);
    $stmt->fetchAll();
    $time_like = round((microtime(true) - $start) * 1000, 2);
    echo "🕒 Consulta con LIKE: {$time_like}ms<br>";
    
    if ($time_like > 1000) {
        echo "⚠️ <span class='warning'>Consultas LIKE lentas. Considere agregar índices.</span><br>";
    }
    echo "</div>";
    
    // 6. VERIFICAR ÍNDICES
    echo "<div class='section'>
    <h2>🗂️ 6. Índices de la Tabla</h2>";
    
    $stmt = $db->query("SHOW INDEX FROM expedientes");
    $indices = $stmt->fetchAll();
    
    if (!empty($indices)) {
        echo "<table>
        <tr><th>Nombre</th><th>Columna</th><th>Único</th><th>Tipo</th></tr>";
        foreach ($indices as $index) {
            $unique = $index['Non_unique'] == 0 ? 'Sí' : 'No';
            echo "<tr>
            <td>{$index['Key_name']}</td>
            <td>{$index['Column_name']}</td>
            <td>{$unique}</td>
            <td>{$index['Index_type']}</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ <span class='warning'>No se encontraron índices específicos</span><br>";
    }
    echo "</div>";
    
    // 7. VERIFICAR OTRAS TABLAS RELACIONADAS
    echo "<div class='section'>
    <h2>🔗 7. Tablas Relacionadas</h2>";
    
    $tablas_relacionadas = ['persona_fisica', 'concejales', 'persona_juri_entidad', 'historial_lugares'];
    
    foreach ($tablas_relacionadas as $tabla) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM $tabla");
            $count = $stmt->fetch()['total'];
            echo "✅ <span class='success'>$tabla: $count registros</span><br>";
        } catch (Exception $e) {
            echo "❌ <span class='error'>$tabla: No existe o no accesible</span><br>";
        }
    }
    echo "</div>";
    
    // 8. DIAGNÓSTICO DE PROBLEMAS COMUNES
    echo "<div class='section'>
    <h2>🚨 8. Diagnóstico de Problemas Comunes</h2>";
    
    $problemas = [];
    
    // Problema 1: Datos vacíos o nulos
    $stmt = $db->query("SELECT COUNT(*) as total FROM expedientes WHERE numero IS NULL OR numero = ''");
    $nulos = $stmt->fetch()['total'];
    if ($nulos > 0) {
        $problemas[] = "⚠️ Hay $nulos expedientes con número vacío o nulo";
    }
    
    // Problema 2: Fechas inválidas
    $stmt = $db->query("SELECT COUNT(*) as total FROM expedientes WHERE fecha_hora_ingreso IS NULL");
    $fechas_nulas = $stmt->fetch()['total'];
    if ($fechas_nulas > 0) {
        $problemas[] = "⚠️ Hay $fechas_nulas expedientes sin fecha de ingreso";
    }
    
    // Problema 3: Caracteres especiales
    $stmt = $db->query("SELECT COUNT(*) as total FROM expedientes WHERE iniciador LIKE '%ñ%' OR iniciador LIKE '%á%' OR iniciador LIKE '%é%'");
    $especiales = $stmt->fetch()['total'];
    if ($especiales > 0) {
        echo "ℹ️ <span class='info'>$especiales expedientes contienen caracteres especiales (correcto)</span><br>";
    }
    
    if (empty($problemas)) {
        echo "✅ <span class='success'>No se detectaron problemas comunes en los datos</span><br>";
    } else {
        foreach ($problemas as $problema) {
            echo "$problema<br>";
        }
    }
    echo "</div>";
    
    // 9. CONSULTA EJEMPLO SIMILAR A LA BÚSQUEDA REAL
    echo "<div class='section'>
    <h2>🎯 9. Simulación de Búsqueda Real</h2>";
    
    echo "<h4>Ejemplo: Búsqueda de expediente número 1, letra A del año 2024</h4>";
    
    $sql = "SELECT numero, letra, folio, libro, anio, 
                   DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y %H:%i') as fecha_ingreso,
                   lugar, extracto, iniciador
            FROM expedientes 
            WHERE numero = :numero 
              AND letra = :letra 
              AND anio = :anio";
    
    echo "<div class='code'>SQL: " . htmlspecialchars($sql) . "</div>";
    
    $stmt = $db->prepare($sql);
    $params = [':numero' => 1, ':letra' => 'A', ':anio' => 2024];
    
    $start = microtime(true);
    $stmt->execute($params);
    $resultado = $stmt->fetchAll();
    $tiempo = round((microtime(true) - $start) * 1000, 2);
    
    echo "⏱️ Tiempo de ejecución: {$tiempo}ms<br>";
    
    if (!empty($resultado)) {
        echo "✅ <span class='success'>Encontrado " . count($resultado) . " resultado(s)</span><br>";
        foreach ($resultado as $exp) {
            echo "<div class='highlight'>
            <strong>Expediente {$exp['numero']}-{$exp['letra']}-{$exp['folio']}-{$exp['libro']}/{$exp['anio']}</strong><br>
            📅 Ingreso: {$exp['fecha_ingreso']}<br>
            📍 Lugar: {$exp['lugar']}<br>
            👤 Iniciador: " . substr($exp['iniciador'], 0, 50) . "...<br>
            📝 Extracto: " . substr($exp['extracto'], 0, 100) . "...
            </div>";
        }
    } else {
        echo "⚠️ <span class='warning'>No se encontraron resultados para estos criterios</span><br>";
        echo "💡 <strong>Sugerencia:</strong> Verifique que existan expedientes con estos datos específicos<br>";
    }
    echo "</div>";
    
    echo "<div class='section'>
    <h2>✅ Resumen Final</h2>
    <p><strong>Conexión a DonWeb:</strong> ✅ Exitosa</p>
    <p><strong>Total expedientes:</strong> $total</p>
    <p><strong>Rendimiento general:</strong> " . ($time_like < 500 ? '✅ Bueno' : '⚠️ Mejorable') . "</p>
    <p><strong>Integridad de datos:</strong> " . (empty($problemas) ? '✅ Correcta' : '⚠️ Requiere atención') . "</p>
    </div>";
    
} catch (PDOException $e) {
    echo "<div class='section'>
    <h2>❌ Error de Base de Datos</h2>
    <p class='error'>Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>
    <p><strong>Código:</strong> " . $e->getCode() . "</p>
    <p><strong>Verificar:</strong></p>
    <ul>
    <li>Credenciales de base de datos</li>
    <li>Servidor MySQL activo en DonWeb</li>
    <li>Permisos de usuario c2810161_iniciad</li>
    <li>Red y conectividad</li>
    </ul>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='section'>
    <h2>❌ Error General</h2>
    <p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>";
}

echo "<div class='section'>
<h2>🔧 Acciones Recomendadas</h2>
<ol>
<li><strong>Si no encuentra expedientes:</strong>
   <ul>
   <li>Verifique que los datos existen con los criterios exactos</li>
   <li>Use búsqueda rápida con términos parciales</li>
   <li>Revise mayúsculas/minúsculas en letras</li>
   </ul>
</li>
<li><strong>Si las consultas son lentas:</strong>
   <ul>
   <li>Considere agregar índices en columnas de búsqueda frecuente</li>
   <li>Limite los resultados con LIMIT</li>
   </ul>
</li>
<li><strong>Para optimizar:</strong>
   <ul>
   <li>Agregue índice compuesto en (numero, letra, anio)</li>
   <li>Considere índice de texto completo en extracto</li>
   </ul>
</li>
</ol>
</div>";

echo "</div></body></html>";
?>