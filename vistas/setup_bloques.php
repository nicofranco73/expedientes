<?php
session_start();

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>🔧 Configuración Automática del Sistema de Bloques</h2>";
    echo "<div style='max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif;'>";

    // Paso 1: Verificar tabla concejales
    echo "<h3>📋 Paso 1: Verificando concejales</h3>";
    $stmt = $db->query("SELECT COUNT(*) FROM concejales");
    $total_concejales = $stmt->fetchColumn();
    echo "<p>✅ Encontrados <strong>$total_concejales</strong> concejales</p>";

    // Paso 2: Crear tabla de historial si no existe
    echo "<h3>🔧 Paso 2: Creando tabla de historial</h3>";
    $stmt = $db->prepare("SHOW TABLES LIKE 'concejal_bloques_historial'");
    $stmt->execute();
    $tabla_existe = $stmt->rowCount() > 0;

    if (!$tabla_existe) {
        echo "<p>⚠️ Creando tabla concejal_bloques_historial...</p>";
        
        $create_table_sql = "
            CREATE TABLE concejal_bloques_historial (
                id INT AUTO_INCREMENT PRIMARY KEY,
                concejal_id INT NOT NULL,
                nombre_bloque VARCHAR(255) NOT NULL,
                fecha_inicio DATE,
                fecha_fin DATE,
                es_actual BOOLEAN DEFAULT FALSE,
                observacion TEXT,
                fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
                eliminado BOOLEAN DEFAULT FALSE,
                fecha_eliminacion DATETIME NULL,
                motivo_eliminacion TEXT NULL,
                FOREIGN KEY (concejal_id) REFERENCES concejales(id) ON DELETE CASCADE,
                INDEX idx_concejal_id (concejal_id),
                INDEX idx_es_actual (es_actual),
                INDEX idx_fecha_inicio (fecha_inicio),
                INDEX idx_eliminado (eliminado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $db->exec($create_table_sql);
        echo "<p style='color: green;'>✅ Tabla creada exitosamente</p>";
    } else {
        echo "<p style='color: green;'>✅ La tabla ya existe</p>";
    }

    // Paso 3: Migrar datos existentes
    echo "<h3>📦 Paso 3: Migrando datos de bloques</h3>";
    $stmt = $db->query("SELECT COUNT(*) FROM concejal_bloques_historial");
    $registros_historial = $stmt->fetchColumn();

    if ($registros_historial == 0 && $total_concejales > 0) {
        echo "<p>📥 Migrando bloques actuales al historial...</p>";
        
        $stmt = $db->query("
            SELECT id, nombre, apellido, bloque 
            FROM concejales 
            WHERE bloque IS NOT NULL AND bloque != ''
        ");
        $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $migrados = 0;
        foreach ($concejales as $concejal) {
            $stmt = $db->prepare("
                INSERT INTO concejal_bloques_historial 
                (concejal_id, nombre_bloque, es_actual, observacion, fecha_registro) 
                VALUES (?, ?, 1, 'Bloque migrado desde datos existentes', NOW())
            ");
            
            $stmt->execute([
                $concejal['id'],
                $concejal['bloque']
            ]);
            $migrados++;
        }
        
        echo "<p style='color: green;'>✅ Migrados <strong>$migrados</strong> bloques al historial</p>";
    } else {
        echo "<p style='color: green;'>✅ Ya existen <strong>$registros_historial</strong> registros en el historial</p>";
    }

    // Paso 4: Verificar funcionamiento
    echo "<h3>🧪 Paso 4: Probando funcionalidad</h3>";
    
    if ($total_concejales > 0) {
        $stmt = $db->query("SELECT id, apellido, nombre FROM concejales LIMIT 1");
        $concejal_prueba = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM concejal_bloques_historial 
            WHERE concejal_id = ? AND (eliminado IS NULL OR eliminado = FALSE)
        ");
        $stmt->execute([$concejal_prueba['id']]);
        $bloques_concejal = $stmt->fetchColumn();
        
        echo "<p>🧪 Probando con: <strong>" . $concejal_prueba['apellido'] . ", " . $concejal_prueba['nombre'] . "</strong></p>";
        echo "<p>✅ Este concejal tiene <strong>$bloques_concejal</strong> bloque(s) disponible(s)</p>";
    }

    // Paso 5: Mostrar estado final
    echo "<h3>📊 Resumen Final</h3>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Estado del sistema:</strong></p>";
    echo "<ul>";
    echo "<li>Concejales registrados: <strong>$total_concejales</strong></li>";
    
    $stmt = $db->query("SELECT COUNT(*) FROM concejal_bloques_historial");
    $total_historial = $stmt->fetchColumn();
    echo "<li>Registros en historial: <strong>$total_historial</strong></li>";
    
    $stmt = $db->query("SELECT COUNT(*) FROM concejal_bloques_historial WHERE es_actual = 1");
    $bloques_actuales = $stmt->fetchColumn();
    echo "<li>Bloques marcados como actuales: <strong>$bloques_actuales</strong></li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='carga_expedientes.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🚀 Probar Sistema de Bloques</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #ffe8e8; padding: 15px; border-radius: 5px; color: red; margin: 20px 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Sistema Bloques</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        h3 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
        }
        p {
            line-height: 1.6;
        }
        a {
            display: inline-block;
            transition: transform 0.2s;
        }
        a:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
</body>
</html>