<?php
// Test específico para búsqueda rápida - expediente 025 A 209
// URL: https://expedientescde.online/expedientes/test_busqueda_rapida.php?pass=test2025

if (!isset($_GET['pass']) || $_GET['pass'] !== 'test2025') {
    die('Acceso denegado. Use: ?pass=test2025');
}

$host = 'localhost';
$dbname = 'c2810161_iniciad';
$username = 'c2810161_iniciad';
$password = 'li62veMAdu';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h2>🔍 Test Búsqueda Rápida: '025 A 209'</h2>";
    echo "<div style='font-family: Arial; background: #f8f9fa; padding: 20px; margin: 10px 0; border-radius: 8px;'>";
    
    // Simular exactamente lo que hace busqueda_rapida.php
    $termino = "025 A 209";
    
    // 1. Test: Detectar patrón de código completo
    echo "<h3>1. Detección de Patrón</h3>";
    $esCodigoCompleto = preg_match('/^(\d+)\s*([A-Za-z])\s*(\d+)/', $termino, $matches);
    
    if ($esCodigoCompleto) {
        echo "✅ <strong>Patrón detectado como código de expediente</strong><br>";
        echo "• Número: '{$matches[1]}'<br>";
        echo "• Letra: '" . strtoupper($matches[2]) . "'<br>";
        echo "• Folio: '{$matches[3]}'<br><br>";
        
        // Búsqueda específica
        $numero = $matches[1];
        $letra = strtoupper($matches[2]);
        $folio = $matches[3];
        
        echo "<h3>2. Búsqueda Específica por Código</h3>";
        $sql = "SELECT numero, letra, folio, libro, anio, 
                       DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y') as fecha_ingreso,
                       iniciador, lugar, extracto
                FROM expedientes 
                WHERE numero = :numero AND letra = :letra AND folio = :folio";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':numero' => $numero,
            ':letra' => $letra,
            ':folio' => $folio
        ]);
        $resultados_especificos = $stmt->fetchAll();
        
        if ($resultados_especificos) {
            echo "✅ <strong>Encontrado " . count($resultados_especificos) . " expediente(s)</strong><br>";
            foreach ($resultados_especificos as $exp) {
                echo "<div style='background: white; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
                echo "<strong>Expediente:</strong> {$exp['numero']}/{$exp['letra']}/{$exp['folio']}/{$exp['libro']}/{$exp['anio']}<br>";
                echo "<strong>Fecha:</strong> {$exp['fecha_ingreso']}<br>";
                echo "<strong>Iniciador:</strong> " . substr($exp['iniciador'], 0, 80) . "...<br>";
                echo "<strong>Ubicación:</strong> {$exp['lugar']}<br>";
                echo "</div>";
            }
        } else {
            echo "❌ <strong>No encontrado</strong><br>";
        }
        
    } else {
        echo "❌ <strong>Patrón NO detectado como código</strong><br>";
        echo "Se usaría búsqueda general<br><br>";
    }
    
    // 2. Test: Búsqueda general como alternativa
    echo "<h3>3. Búsqueda General (LIKE)</h3>";
    $sql = "SELECT numero, letra, folio, libro, anio, 
                   DATE_FORMAT(fecha_hora_ingreso, '%d/%m/%Y') as fecha_ingreso,
                   iniciador, lugar
            FROM expedientes 
            WHERE numero LIKE :termino
               OR letra LIKE :termino
               OR folio LIKE :termino
               OR CONCAT(numero, ' ', letra, ' ', folio) LIKE :termino
            LIMIT 5";
    
    $termino_like = '%' . $termino . '%';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':termino' => $termino_like
    ]);
    $resultados_generales = $stmt->fetchAll();
    
    if ($resultados_generales) {
        echo "✅ <strong>Búsqueda general encontró " . count($resultados_generales) . " resultado(s)</strong><br>";
        foreach ($resultados_generales as $exp) {
            echo "<div style='background: white; padding: 8px; margin: 5px 0; border-left: 4px solid #007bff;'>";
            echo "<strong>Expediente:</strong> {$exp['numero']}/{$exp['letra']}/{$exp['folio']}/{$exp['libro']}/{$exp['anio']}<br>";
            echo "<strong>Fecha:</strong> {$exp['fecha_ingreso']}<br>";
            echo "</div>";
        }
    } else {
        echo "❌ <strong>Búsqueda general tampoco encontró resultados</strong><br>";
    }
    
    // 3. Test: Verificar si existe el expediente
    echo "<h3>4. Verificación Manual</h3>";
    
    // Buscar con diferentes variantes
    $variantes = [
        ['025', 'A', '209'],
        ['25', 'A', '209'],
        ['025', 'a', '209'],
        ['25', 'a', '209']
    ];
    
    foreach ($variantes as $i => $v) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM expedientes WHERE numero = ? AND letra = ? AND folio = ?");
        $stmt->execute($v);
        $total = $stmt->fetch()['total'];
        
        echo "Variante " . ($i+1) . " ('{$v[0]}', '{$v[1]}', '{$v[2]}'): <strong>$total</strong> resultado(s)<br>";
    }
    
    // 4. Test: Mostrar expedientes similares
    echo "<h3>5. Expedientes Similares</h3>";
    $stmt = $pdo->prepare("SELECT numero, letra, folio, libro, anio 
                          FROM expedientes 
                          WHERE (numero IN ('025', '25') OR letra = 'A' OR folio = '209') 
                          AND anio >= 2024
                          ORDER BY numero, letra, folio 
                          LIMIT 10");
    $stmt->execute();
    $similares = $stmt->fetchAll();
    
    if ($similares) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #e9ecef;'><th>Número</th><th>Letra</th><th>Folio</th><th>Libro</th><th>Año</th></tr>";
        foreach ($similares as $exp) {
            $highlight = ($exp['numero'] == '025' || $exp['numero'] == '25') && $exp['letra'] == 'A' && $exp['folio'] == '209' ? " style='background: yellow;'" : "";
            echo "<tr$highlight>";
            echo "<td>'{$exp['numero']}'</td>";
            echo "<td>{$exp['letra']}</td>";
            echo "<td>{$exp['folio']}</td>";
            echo "<td>{$exp['libro']}</td>";
            echo "<td>{$exp['anio']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}

echo "<br><p><a href='?pass=test2025&refresh=1'>🔄 Recargar</a></p>";
echo "<p><a href='investigar_025.php?pass=search025'>🔍 Test Completo</a></p>";
?>