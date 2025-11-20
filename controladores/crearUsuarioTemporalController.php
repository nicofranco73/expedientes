<?php

/**
 * Controlador para crear usuarios temporalmente.
 * NO DEBE USARSE EN PRODUCCIÓN NORMAL SIN VERIFICACIÓN DE PERMISOS.
 */

session_start();

// Incluir el Modelo
require_once 'UserModel.php';

// Función utilitaria para escapar HTML (seguridad en la Vista)
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$tipo_mensaje = '';
$post_data = $_POST; // Guardar datos para rellenar el formulario

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Inicializar el Modelo
        $userModel = new UserModel();

        // 1. Recolección y saneamiento de datos
        $username = trim($post_data['username'] ?? '');
        $password = $post_data['password'] ?? '';
        $nombre = trim($post_data['nombre'] ?? '');
        $apellido = trim($post_data['apellido'] ?? '');
        $email = trim($post_data['email'] ?? '');
        $role = $post_data['role'] ?? 'usuario';
        
        // 2. Validaciones
        if (empty($username) || empty($password) || empty($nombre) || empty($apellido) || empty($email)) {
            throw new Exception('Todos los campos son obligatorios');
        }

        if (strlen($password) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email no es válido');
        }

        // 3. Verificación de unicidad (Usa el Modelo)
        if ($userModel->checkUserExists($username)) {
            throw new Exception('El nombre de usuario ya existe');
        }

        if ($userModel->checkEmailExists($email)) {
            throw new Exception('El email ya está registrado');
        }

        // 4. Lógica de negocio (Hashing de la contraseña)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 5. Creación del usuario (Usa el Modelo)
        $userData = [
            'username' => $username,
            'password_hash' => $passwordHash,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'role' => $role,
        ];
        
        $userModel->createUser($userData);

        $mensaje = "Usuario '{$username}' creado exitosamente con rol '{$role}'";
        $tipo_mensaje = 'success';
        
        // Limpiar datos del formulario después de un éxito
        $post_data = [];

    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
        // Mantener $post_data para rellenar el formulario
    }
}

// 6. Incluir la Vista (Paso de variables a la vista)
// La vista tendrá acceso a $mensaje, $tipo_mensaje, $post_data y la función e()
require 'views/crearUsuarioTemporal.view.php';