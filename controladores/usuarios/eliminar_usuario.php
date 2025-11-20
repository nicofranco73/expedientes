<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

if (empty($_GET['id'])) { header('Location: listar_usuarios.php'); exit; }
$id = (int)$_GET['id'];

// Prevent deleting self
if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
    $_SESSION['mensaje'] = 'No puede eliminar su propia cuenta mientras esté autenticado.';
    $_SESSION['tipo_mensaje'] = 'warning';
    header('Location: listar_usuarios.php'); exit;
}

try {
    // Verificar si el usuario a eliminar es un super usuario
    $stmt = $pdo->prepare('SELECT is_superuser, username FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['is_superuser'] == 1) {
        $_SESSION['mensaje'] = 'Error: No se puede eliminar o desactivar al Super Usuario del sistema.';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: listar_usuarios.php'); exit;
    }
    
    // Proceder con la desactivación si no es super usuario
    $stmt = $pdo->prepare('UPDATE usuarios SET is_active = 0 WHERE id = ? AND is_superuser = 0');
    $result = $stmt->execute([$id]);
    
    if ($result && $stmt->rowCount() > 0) {
        $_SESSION['mensaje'] = 'Usuario desactivado exitosamente';
        $_SESSION['tipo_mensaje'] = 'info';
        
        // Registrar la acción en logs de seguridad
        try {
            $log_stmt = $pdo->prepare('INSERT INTO logs_seguridad (user_id, accion, descripcion, ip_address, fecha) VALUES (?, ?, ?, ?, NOW())');
            $log_stmt->execute([
                $_SESSION['user_id'] ?? null,
                'DESACTIVAR_USUARIO',
                'Usuario desactivado: ' . ($user['username'] ?? 'ID:' . $id),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Si falla el log, no es crítico
        }
    } else {
        $_SESSION['mensaje'] = 'Error al desactivar el usuario';
        $_SESSION['tipo_mensaje'] = 'warning';
    }
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error del sistema: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
}
header('Location: listar_usuarios.php'); exit;
