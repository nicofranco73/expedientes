<?php
// Script de investigación específica para expediente 025 A 209 2025
// URL: https://expedientescde.online/expedientes/investigar_025.php?pass=search025

if (!isset($_GET['pass']) || $_GET['pass'] !== 'search025') {
    die('Acceso denegado. Use: ?pass=search025');
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
    
    echo "<h2>🔍 Investigación Específica: Expediente 025 A 209 2025</h2>";
    echo "<div style='font-family: Arial; background: #f8f9fa; padding: 20px; margin: 10px 0; border-radius: 8px;'>";
    
    // 1. Verificar si el expediente existe EXACTAMENTE como se busca
    echo "<h3>1. ¿Existe el expediente 025 A 209 2025?</h3>";
    $stmt = $pdo->prepare("SELECT * FROM expedientes WHERE numero = '025' AND letra = 'A' AND folio = '209' AND anio = 2025");
    $stmt->execute();
    $expediente_025 = $stmt->fetch();
    
    if ($expediente_025) {
        echo "✅ <strong>SÍ EXISTE</strong> el expediente 025 A 209 2025<br>";
        echo "📋 Detalles:<br>";
        echo "&nbsp;&nbsp;• ID: {$expediente_025['id']}<br>";
        echo "&nbsp;&nbsp;• Número: '{$expediente_025['numero']}'<br>";
        echo "&nbsp;&nbsp;• Letra: '{$expediente_025['letra']}'<br>";
        echo "&nbsp;&nbsp;• Folio: '{$expediente_025['folio']}'<br>";
        echo "&nbsp;&nbsp;• Libro: '{$expediente_025['libro']}'<br>";
        echo "&nbsp;&nbsp;• Año: {$expediente_025['anio']}<br>";
        echo "&nbsp;&nbsp;• Fecha: {$expediente_025['fecha_hora_ingreso']}<br>";
        echo "&nbsp;&nbsp;• Iniciador: " . substr($expediente_025['iniciador'], 0, 100) . "...<br>";
    } else {
        echo "❌ <strong>NO EXISTE</strong> el expediente 025 A 209 2025<br>";
    }
    
    // 2. Verificar si existe como "25" (sin cero inicial)
    echo "<h3>2. ¿Existe como número 25 (sin cero)?</h3>";
    $stmt = $pdo->prepare("SELECT * FROM expedientes WHERE numero = '25' AND letra = 'A' AND folio = '209' AND anio = 2025");
    $stmt->execute();
    $expediente_25 = $stmt->fetch();
    
    if ($expediente_25) {
        echo "✅ <strong>SÍ EXISTE</strong> como 25 A 209 2025<br>";
        echo "📋 Detalles:<br>";
        echo "&nbsp;&nbsp;• Número almacenado: '{$expediente_25['numero']}'<br>";
        echo "&nbsp;&nbsp;• Iniciador: " . substr($expediente_25['iniciador'], 0, 100) . "...<br>";
    } else {
        echo "❌ <strong>NO EXISTE</strong> como 25 A 209 2025<br>";
    }
    
    // 3. Buscar expedientes similares con letra A y año 2025
    echo "<h3>3. Expedientes con letra A del 2025</h3>";
    $stmt = $pdo->prepare("SELECT numero, letra, folio, libro, anio, iniciador FROM expedientes 
                          WHERE letra = 'A' AND anio = 2025 
                          ORDER BY CAST(numero AS UNSIGNED) LIMIT 10");
    $stmt->execute();
    $expedientes_A = $stmt->fetchAll();
    
    if ($expedientes_A) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #e9ecef;'><th>Número</th><th>Letra</th><th>Folio</th><th>Libro</th><th>Iniciador</th></tr>";
        foreach ($expedientes_A as $exp) {
            $highlight = ($exp['numero'] == '025' || $exp['numero'] == '25') ? " style='background: yellow;'" : "";
            echo "<tr$highlight>";
            echo "<td>'{$exp['numero']}'</td>";
            echo "<td>{$exp['letra']}</td>";
            echo "<td>{$exp['folio']}</td>";
            echo "<td>{$exp['libro']}</td>";
            echo "<td>" . substr($exp['iniciador'], 0, 50) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay expedientes con letra A en 2025<br>";
    }
    
    // 4. Simular el problema del sistema actual
    echo "<h3>4. 🐛 Simulación del Bug en el Sistema</h3>";
    echo "<strong>Problema detectado:</strong><br>";
    echo "• El usuario busca: <code>025</code><br>";
    echo "• El sistema hace: <code>filter_var('025', FILTER_VALIDATE_INT)</code><br>";
    echo "• Resultado: <code>" . filter_var('025', FILTER_VALIDATE_INT) . "</code> (se pierden los ceros)<br>";
    echo "• Entonces busca en BD: número = 25<br>";
    echo "• Pero en BD está como: número = '025'<br>";
    echo "• ❌ <strong>NO COINCIDEN</strong><br><br>";
    
    // 5. Prueba de la consulta que hace el sistema actual
    echo "<h3>5. ¿Qué encuentra el sistema actual?</h3>";
    $numero_filtrado = filter_var('025', FILTER_VALIDATE_INT); // Simula lo que hace el sistema
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM expedientes 
                          WHERE numero = :numero AND letra = 'A' AND folio = '209' AND anio = 2025");
    $stmt->execute([':numero' => $numero_filtrado]);
    $resultado_actual = $stmt->fetch();
    
    echo "Consulta del sistema actual con número = $numero_filtrado: ";
    echo "<strong>" . $resultado_actual['total'] . " resultados</strong><br>";
    
    // 6. Prueba de la consulta corregida
    echo "<h3>6. ¿Qué encontraría con la corrección?</h3>";
    $numero_correcto = '025'; // Mantenemos como string
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM expedientes 
                          WHERE numero = :numero AND letra = 'A' AND folio = '209' AND anio = 2025");
    $stmt->execute([':numero' => $numero_correcto]);
    $resultado_corregido = $stmt->fetch();
    
    echo "Consulta corregida con número = '$numero_correcto': ";
    echo "<strong>" . $resultado_corregido['total'] . " resultados</strong><br>";
    
    // 7. Mostrar otros números que empiezan con 0
    echo "<h3>7. Otros expedientes con ceros iniciales</h3>";
    $stmt = $pdo->prepare("SELECT DISTINCT numero FROM expedientes 
                          WHERE numero LIKE '0%' AND anio = 2025 
                          ORDER BY CAST(numero AS UNSIGNED) LIMIT 10");
    $stmt->execute();
    $con_ceros = $stmt->fetchAll();
    
    if ($con_ceros) {
        echo "Números con ceros iniciales en 2025: ";
        foreach ($con_ceros as $num) {
            echo "<strong>'{$num['numero']}'</strong> ";
        }
        echo "<br><br>";
        echo "⚠️ <strong>Todos estos expedientes tienen el mismo problema de búsqueda</strong><br>";
    } else {
        echo "No hay números con ceros iniciales en 2025<br>";
    }
    
    echo "</div>";
    
    // Solución propuesta
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>💡 Solución Identificada</h3>";
    echo "<p><strong>Problema:</strong> El código usa <code>filter_var(\$_POST['numero'], FILTER_VALIDATE_INT)</code> que elimina ceros iniciales.</p>";
    echo "<p><strong>Solución:</strong> Cambiar a <code>trim(\$_POST['numero'])</code> para mantener el formato original.</p>";
    echo "<p><strong>Archivo a modificar:</strong> <code>/vistas/resultados.php</code> línea ~64</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}

echo "<br><p><a href='?pass=search025&refresh=1'>🔄 Recargar</a></p>";
?>