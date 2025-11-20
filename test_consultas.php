<?php
/**
 * Script de prueba rápida para consultas de expedientes
 * Fecha: 22 de octubre de 2025
 */

// Validar acceso
$password = $_GET['pass'] ?? '';
if ($password !== 'test2025') {
    die('❌ Acceso denegado. Use: ?pass=test2025');
}

// Mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Prueba Rápida - Consultas Expedientes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        .query { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<h1>🧪 Prueba Rápida de Consultas de Expedientes</h1>";

try {
    // Conexión a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div class='test success'>✅ Conexión exitosa a la base de datos</div>";
    
    // Obtener algunos expedientes de muestra para pruebas
    echo "<h2>📋 Expedientes de Muestra Disponibles</h2>";
    $stmt = $db->query("SELECT numero, letra, folio, libro, anio, 
                               DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y') as fecha,
                               LEFT(iniciador, 30) as iniciador_corto,
                               LEFT(extracto, 50) as extracto_corto
                        FROM expedientes 
                        ORDER BY fecha_hora_ingreso DESC 
                        LIMIT 10");
    $expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($expedientes)) {
        echo "<div class='test error'>❌ No hay expedientes en la base de datos</div>";
    } else {
        echo "<div class='test info'>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Número</th><th>Letra</th><th>Folio</th><th>Libro</th><th>Año</th><th>Fecha</th><th>Iniciador</th><th>Extracto</th></tr>";
        
        foreach ($expedientes as $exp) {
            echo "<tr>";
            echo "<td>{$exp['numero']}</td>";
            echo "<td>{$exp['letra']}</td>";
            echo "<td>{$exp['folio']}</td>";
            echo "<td>{$exp['libro']}</td>";
            echo "<td>{$exp['anio']}</td>";
            echo "<td>{$exp['fecha']}</td>";
            echo "<td>{$exp['iniciador_corto']}...</td>";
            echo "<td>{$exp['extracto_corto']}...</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // Probar consultas específicas con los datos reales
        echo "<h2>🎯 Pruebas de Consulta con Datos Reales</h2>";
        
        foreach (array_slice($expedientes, 0, 3) as $index => $exp) {
            echo "<h3>Test " . ($index + 1) . ": Expediente {$exp['numero']}-{$exp['letra']}-{$exp['folio']}-{$exp['libro']}/{$exp['anio']}</h3>";
            
            // Consulta exacta
            $sql = "SELECT COUNT(*) as encontrado FROM expedientes 
                    WHERE numero = :numero 
                      AND letra = :letra 
                      AND folio = :folio 
                      AND libro = :libro 
                      AND anio = :anio";
            
            echo "<div class='query'>$sql</div>";
            
            $stmt = $db->prepare($sql);
            $start_time = microtime(true);
            
            $stmt->execute([
                ':numero' => $exp['numero'],
                ':letra' => $exp['letra'],
                ':folio' => $exp['folio'],
                ':libro' => $exp['libro'],
                ':anio' => $exp['anio']
            ]);
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $tiempo = round((microtime(true) - $start_time) * 1000, 2);
            
            if ($resultado['encontrado'] > 0) {
                echo "<div class='test success'>✅ Expediente encontrado correctamente (tiempo: {$tiempo}ms)</div>";
            } else {
                echo "<div class='test error'>❌ No se encontró el expediente que debería existir</div>";
                
                // Diagnóstico adicional
                echo "<h4>🔍 Diagnóstico detallado:</h4>";
                
                // Verificar cada campo individualmente
                $campos = ['numero', 'letra', 'folio', 'libro', 'anio'];
                foreach ($campos as $campo) {
                    $sql_campo = "SELECT COUNT(*) as count FROM expedientes WHERE $campo = :valor";
                    $stmt_campo = $db->prepare($sql_campo);
                    $stmt_campo->execute([':valor' => $exp[$campo]]);
                    $count = $stmt_campo->fetch()['count'];
                    
                    $tipo_valor = gettype($exp[$campo]);
                    $valor_mostrar = is_string($exp[$campo]) ? "'{$exp[$campo]}'" : $exp[$campo];
                    
                    echo "<div class='test info'>";
                    echo "Campo '$campo' = $valor_mostrar (tipo: $tipo_valor): $count registros encontrados";
                    echo "</div>";
                }
            }
        }
        
        // Prueba de búsqueda por texto
        echo "<h2>📝 Prueba de Búsqueda por Texto</h2>";
        
        $sql_texto = "SELECT COUNT(*) as total FROM expedientes 
                      WHERE extracto LIKE :termino 
                         OR iniciador LIKE :termino";
        
        $terminos = ['a', 'con', 'para', 'del', 'que'];
        
        foreach ($terminos as $termino) {
            $stmt = $db->prepare($sql_texto);
            $stmt->execute([':termino' => "%$termino%"]);
            $count = $stmt->fetch()['total'];
            
            echo "<div class='test " . ($count > 0 ? 'success' : 'error') . "'>";
            echo "Término '$termino': $count expedientes encontrados";
            echo "</div>";
        }
        
        // Prueba de consulta con diferentes tipos de datos
        echo "<h2>🔢 Prueba de Tipos de Datos</h2>";
        
        if (!empty($expedientes)) {
            $primer_exp = $expedientes[0];
            
            // Probar número como string vs integer
            echo "<h3>Comparación String vs Integer para número '{$primer_exp['numero']}':</h3>";
            
            // Como string
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM expedientes WHERE numero = :numero");
            $stmt->execute([':numero' => (string)$primer_exp['numero']]);
            $count_string = $stmt->fetch()['count'];
            
            // Como integer
            $stmt->execute([':numero' => (int)$primer_exp['numero']]);
            $count_int = $stmt->fetch()['count'];
            
            echo "<div class='test info'>Como string: $count_string resultados</div>";
            echo "<div class='test info'>Como integer: $count_int resultados</div>";
            
            if ($count_string !== $count_int) {
                echo "<div class='test error'>⚠️ Diferencia en resultados según tipo de dato</div>";
            }
        }
    }
    
    // Información adicional del sistema
    echo "<h2>ℹ️ Información del Sistema</h2>";
    echo "<div class='test info'>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "MySQL Version: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    echo "Charset de conexión: " . $db->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "<br>";
    echo "Zona horaria: " . date_default_timezone_get() . "<br>";
    echo "Fecha/hora actual: " . date('Y-m-d H:i:s') . "<br>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='test error'>";
    echo "<h2>❌ Error de Base de Datos</h2>";
    echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='test error'>";
    echo "<h2>❌ Error General</h2>";
    echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "</div>";
}

echo "</body></html>";
?>