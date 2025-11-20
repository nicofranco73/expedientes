<?php
/**
 * Script de emergencia para crear usuario administrador
 * Úsalo cuando no puedas acceder al sistema normal
 */

echo "<h2>Creando Usuario Administrador de Emergencia</h2>";

// Usar la conexión centralizada
try {
    require_once 'db/connection.php'; // Asegúrate que esta ruta sea correcta
    $db = $pdo; 
    
    echo "<p>✅ Conexión a base de datos exitosa.</p>";

    // Verificar si la tabla usuarios existe
    $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() == 0) {
        echo "<p>⚠️ La tabla 'usuarios' no existe. Creándola...</p>";
        
        $createTable = "
            CREATE TABLE usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                email VARCHAR(150) UNIQUE NOT NULL,
                role ENUM('admin', 'usuario', 'consulta') DEFAULT 'usuario',
                is_active TINYINT(1) DEFAULT 1,
                is_superuser TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createTable);
        echo "<p>✅ Tabla 'usuarios' creada exitosamente.</p>";
    } else {
        echo "<p>✅ La tabla 'usuarios' existe.</p>";
    }

    // Verificar usuarios existentes
    $stmt = $db->query("SELECT username, role, is_active FROM usuarios");
    $usuarios = $stmt->fetchAll();
    
    if (count($usuarios) > 0) {
        echo "<h3>Usuarios existentes:</h3>";
        echo "<ul>";
        foreach ($usuarios as $usuario) {
            $status = $usuario['is_active'] ? 'Activo' : 'Inactivo';
            echo "<li><strong>{$usuario['username']}</strong> - Rol: {$usuario['role']} - Estado: {$status}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>⚠️ No hay usuarios en la base de datos.</p>";
    }

    // Crear o actualizar usuario admin
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);

    // Verificar si admin ya existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt->execute([$adminUsername]);
    $adminExists = $stmt->fetch();

    if ($adminExists) {
        // Actualizar usuario admin existente
        $stmt = $db->prepare("
            UPDATE usuarios 
            SET password_hash = ?, role = 'admin', is_active = 1, is_superuser = 1 
            WHERE username = ?
        ");
        $stmt->execute([$passwordHash, $adminUsername]);
        echo "<p>✅ Usuario administrador actualizado exitosamente.</p>";
    } else {
        // Crear nuevo usuario admin
        $stmt = $db->prepare("
            INSERT INTO usuarios (username, password_hash, nombre, apellido, email, role, is_active, is_superuser) 
            VALUES (?, ?, ?, ?, ?, 'admin', 1, 1)
        ");
        $stmt->execute([
            $adminUsername,
            $passwordHash,
            'Administrador',
            'Sistema',
            'admin@expedientescde.online'
        ]);
        echo "<p>✅ Usuario administrador creado exitosamente.</p>";
    }

    echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>🔐 Credenciales de Acceso:</h3>";
    echo "<p><strong>Usuario:</strong> {$adminUsername}</p>";
    echo "<p><strong>Contraseña:</strong> {$adminPassword}</p>";
    echo "<p><strong>Rol:</strong> Administrador</p>";
    echo "</div>";

    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>📋 Próximos Pasos:</h3>";
    echo "<ol>";
    echo "<li><a href='login.php' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Ir al Login</a></li>";
    echo "<li>Usar las credenciales mostradas arriba</li>";
    echo "<li>Una vez logueado, ir a <a href='crear_usuario.php'>Crear Usuario</a></li>";
    echo "<li>Cambiar la contraseña por defecto</li>";
    echo "</ol>";
    echo "</div>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // El catch ahora maneja errores de la conexión centralizada también
    if (strpos($e->getMessage(), 'SQLSTATE') === false) {
        echo "<h3>🔧 Posibles Soluciones (Error de Conexión):</h3>";
        echo "<ul>";
        echo "<li>Verificar que el servidor de base de datos esté activo.</li>";
        echo "<li>Verificar las credenciales en el archivo `db/connection.php`.</li>";
        echo "<li>Verificar que la ruta `'db/connection.php'` sea correcta.</li>";
        echo "</ul>";
    }

    echo "<h3>📝 Información de Diagnóstico:</h3>";
    // Nota: Eliminamos la información sensible de host/db/user/pass, ya que debe estar centralizada
    echo "<p><strong>Base de datos:</strong> Revisar `db/connection.php`</p>";
}
?>

<hr>
<div style="text-align: center; margin: 20px 0;">
    <a href="diagnostico_sesion.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">Verificar Sesión</a>
    <a href="login.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">Ir al Login</a>
    <a href="dashboard.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">Dashboard</a>
</div>