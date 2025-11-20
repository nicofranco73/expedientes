<?php
echo "<h2>🔍 Diagnóstico del Sistema de Bloques</h2>";

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>✅ Conexión a base de datos exitosa</h3>";
    echo "</div>";

    // 1. Verificar tabla concejales
    echo "<h3>📋 1. Verificando tabla concejales</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM concejales");
    $total_concejales = $stmt->fetchColumn();
    echo "<p>Total de concejales: <strong>$total_concejales</strong></p>";

    if ($total_concejales > 0) {
        $stmt = $db->query("SELECT id, apellido, nombre, bloque FROM concejales LIMIT 5");
        $concejales_sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Apellido</th><th>Nombre</th><th>Bloque</th></tr>";
        foreach ($concejales_sample as $c) {
            echo "<tr>";
            echo "<td>" . $c['id'] . "</td>";
            echo "<td>" . htmlspecialchars($c['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($c['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($c['bloque']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 2. Verificar si existe tabla de historial
    echo "<h3>📋 2. Verificando tabla concejal_bloques_historial</h3>";
    $stmt = $db->prepare("SHOW TABLES LIKE 'concejal_bloques_historial'");
    $stmt->execute();
    $tabla_existe = $stmt->rowCount() > 0;

    if ($tabla_existe) {
        echo "<p style='color: green;'>✅ La tabla concejal_bloques_historial existe</p>";
        
        // Verificar estructura
        $stmt = $db->query("DESCRIBE concejal_bloques_historial");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>Estructura de la tabla:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Contar registros
        $stmt = $db->query("SELECT COUNT(*) as total FROM concejal_bloques_historial");
        $total_historial = $stmt->fetchColumn();
        echo "<p>Total de registros en historial: <strong>$total_historial</strong></p>";

        if ($total_historial > 0) {
            $stmt = $db->query("SELECT * FROM concejal_bloques_historial LIMIT 10");
            $historial_sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h4>Muestra de datos en historial:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
            echo "<tr><th>ID</th><th>Concejal ID</th><th>Nombre Bloque</th><th>Es Actual</th><th>Fecha Inicio</th><th>Fecha Registro</th></tr>";
            foreach ($historial_sample as $h) {
                echo "<tr>";
                echo "<td>" . $h['id'] . "</td>";
                echo "<td>" . $h['concejal_id'] . "</td>";
                echo "<td>" . htmlspecialchars($h['nombre_bloque']) . "</td>";
                echo "<td>" . ($h['es_actual'] ? '✅ Sí' : '❌ No') . "</td>";
                echo "<td>" . ($h['fecha_inicio'] ?: 'NULL') . "</td>";
                echo "<td>" . $h['fecha_registro'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No hay datos en la tabla de historial</p>";
            echo "<p><a href='migrar_bloques_historial.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔄 Ejecutar Migración</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ La tabla concejal_bloques_historial NO existe</p>";
        echo "<p><a href='actualizar_estructura_historial.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Crear Estructura</a></p>";
    }

    // 3. Probar consulta de bloques para un concejal específico
    if ($tabla_existe && $total_concejales > 0) {
        echo "<h3>📋 3. Probando consulta de bloques para concejal</h3>";
        $stmt = $db->query("SELECT id FROM concejales LIMIT 1");
        $primer_concejal_id = $stmt->fetchColumn();
        
        echo "<p>Probando con concejal ID: <strong>$primer_concejal_id</strong></p>";
        
        $stmt = $db->prepare("
            SELECT nombre_bloque, es_actual, fecha_inicio, fecha_fin
            FROM concejal_bloques_historial 
            WHERE concejal_id = ? AND (eliminado IS NULL OR eliminado = FALSE)
            ORDER BY es_actual DESC, fecha_inicio DESC
        ");
        $stmt->execute([$primer_concejal_id]);
        $bloques_concejal = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($bloques_concejal)) {
            echo "<p style='color: orange;'>⚠️ No se encontraron bloques para este concejal</p>";
        } else {
            echo "<p style='color: green;'>✅ Encontrados " . count($bloques_concejal) . " bloques</p>";
            echo "<ul>";
            foreach ($bloques_concejal as $bloque) {
                echo "<li>" . htmlspecialchars($bloque['nombre_bloque']) . " - " . ($bloque['es_actual'] ? 'ACTUAL' : 'HISTÓRICO') . "</li>";
            }
            echo "</ul>";
        }
    }

} catch (Exception $e) {
    echo "<div style='background: #ffe8e8; padding: 15px; margin: 10px 0; border-radius: 5px; color: red;'>";
    echo "<h3>❌ Error de conexión</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<br><hr><br>";
echo "<p><a href='carga_expedientes.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Volver a Carga de Expedientes</a></p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Sistema Bloques</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px;">
</body>
</html>