<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: listar_usuarios.php'); exit; }

// CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['mensaje'] = 'Token CSRF inválido';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: listar_usuarios.php'); exit;
}

$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
$username = trim($_POST['username']);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? 'viewer');
$password = $_POST['password'] ?? '';

// Obtener información del usuario actual de la sesión
$current_user_role = $_SESSION['user_role'] ?? '';
$current_user_id = $_SESSION['user_id'] ?? 0;

try {
    // Validaciones de seguridad para super usuario
    if ($id) {
        // Verificar si se está editando un super usuario
        $stmt = $pdo->prepare('SELECT role, is_superuser FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_user && $existing_user['is_superuser'] == 1) {
            // Solo el propio super usuario puede editarse a sí mismo
            if ($current_user_id != $id && $current_user_role !== 'superuser') {
                $_SESSION['mensaje'] = 'No tiene permisos para modificar este usuario';
                $_SESSION['tipo_mensaje'] = 'danger';
                header('Location: listar_usuarios.php'); exit;
            }
            
            // El rol de superuser no puede ser cambiado
            $role = 'superuser';
        }
    }
    
    // Validar que solo un superuser pueda crear otros superusers
    if ($role === 'superuser' && $current_user_role !== 'superuser') {
        $_SESSION['mensaje'] = 'Solo un Super Usuario puede crear otros Super Usuarios';
        $_SESSION['tipo_mensaje'] = 'danger';
        header('Location: listar_usuarios.php'); exit;
    }
    if ($id) {
        // update
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Determinar si es un superuser para establecer los campos apropiados
            $is_superuser = ($role === 'superuser') ? 1 : 0;
            
            $stmt = $pdo->prepare('UPDATE usuarios SET username=?, nombre=?, apellido=?, email=?, role=?, password_hash=?, is_superuser=?, fecha_actualizacion = NOW() WHERE id = ?');
            $stmt->execute([$username,$nombre,$apellido,$email,$role,$hash,$is_superuser,$id]);
        } else {
            // Determinar si es un superuser para establecer los campos apropiados
            $is_superuser = ($role === 'superuser') ? 1 : 0;
            
            $stmt = $pdo->prepare('UPDATE usuarios SET username=?, nombre=?, apellido=?, email=?, role=?, is_superuser=?, fecha_actualizacion = NOW() WHERE id = ?');
            $stmt->execute([$username,$nombre,$apellido,$email,$role,$is_superuser,$id]);
        }
        $_SESSION['mensaje'] = 'Usuario actualizado';
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: listar_usuarios.php'); exit;
    } else {
        // create
        if (empty($password)) { throw new Exception('Contraseña requerida'); }
        
        // Verificar que no existe ya un superuser si se está creando uno
        if ($role === 'superuser') {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM usuarios WHERE is_superuser = 1');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0 && $current_user_role !== 'superuser') {
                $_SESSION['mensaje'] = 'Ya existe un Super Usuario en el sistema';
                $_SESSION['tipo_mensaje'] = 'danger';
                header('Location: listar_usuarios.php'); exit;
            }
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $is_superuser = ($role === 'superuser') ? 1 : 0;
        
        $stmt = $pdo->prepare('INSERT INTO usuarios (username,nombre,apellido,email,role,password_hash,is_superuser,fecha_creacion) VALUES (?,?,?,?,?,?,?,NOW())');
        $stmt->execute([$username,$nombre,$apellido,$email,$role,$hash,$is_superuser]);
        $_SESSION['mensaje'] = 'Usuario creado';
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: listar_usuarios.php'); exit;
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: listar_usuarios.php'); exit;
}
