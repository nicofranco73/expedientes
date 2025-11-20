<?php
session_start();

// CONFIGURACIÓN DE BASE DE DATOS Y CONEXIÓN
// NOTA: Lo ideal es externalizar estas credenciales en un archivo de configuración/clase separada (ej. 'config/Database.php')
// y usar 'require_once' para evitar duplicación, pero por ahora lo dejamos aquí para la refactorización.
define('DB_HOST', 'localhost');
define('DB_NAME', 'c2810161_iniciad');
define('DB_USER', 'c2810161_iniciad');
define('DB_PASS', 'li62veMAdu');

// Inicialización de variables de datos
$usuarios = [];
$error_mensaje = null;
$tipo_mensaje_alerta = null;
$total_usuarios = 0;
$admin_count = 0;
$user_count = 0;
$new_today_count = 0;

// Lógica de Negocio (Controlador)
try {
    // 1. Conexión a la base de datos
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 2. Obtención de datos
    $stmt = $db->query("SELECT id, username, nombre, apellido, email, role, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Lógica de cálculo de estadísticas (Manejo de Datos)
    $total_usuarios = count($usuarios);
    $hoy = date('Y-m-d');

    // Usamos array_reduce para mayor eficiencia o claridad en lugar de múltiples array_filter
    $stats = array_reduce($usuarios, function($carry, $usuario) use ($hoy) {
        if ($usuario['role'] === 'admin') {
            $carry['admin_count']++;
        } elseif ($usuario['role'] === 'usuario') {
            $carry['user_count']++;
        }
        if (date('Y-m-d', strtotime($usuario['fecha_creacion'])) === $hoy) {
            $carry['new_today_count']++;
        }
        return $carry;
    }, ['admin_count' => 0, 'user_count' => 0, 'new_today_count' => 0]);
    
    $admin_count = $stats['admin_count'];
    $user_count = $stats['user_count'];
    $new_today_count = $stats['new_today_count'];

} catch (Exception $e) {
    // 4. Manejo de errores
    // Si la conexión falla, se establece el error y se asegura que la lista de usuarios esté vacía
    $_SESSION['mensaje'] = 'Error de conexión/consulta: No se pudieron obtener los usuarios. Detalles: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'danger';
    $usuarios = []; // Asegurar que la vista reciba un array vacío en caso de error
}

// Lógica de Mensaje de Sesión
// Si existe un mensaje en la sesión, se prepara para mostrarlo en la vista y se limpia.
if (!empty($_SESSION['mensaje'])) {
    $error_mensaje = $_SESSION['mensaje'];
    $tipo_mensaje_alerta = $_SESSION['tipo_mensaje'] ?? 'info';
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}

// Incluir la vista
require 'listar_usuarios_view.php';
// Nota: La función e() fue movida a la vista ya que solo se usa para escapar HTML.
?>