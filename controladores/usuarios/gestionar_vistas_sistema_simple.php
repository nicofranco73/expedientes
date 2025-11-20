<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Intentar cargar la conexión a BD
try {
    require_once('../db/connection.php');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar si el usuario es admin o superuser (usando user_role como está en login.php)
$user_role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
$is_superuser = $_SESSION['is_superuser'] ?? false;

// Ser más permisivo: permitir si es superuser O si es admin
if (!$is_superuser && !in_array($user_role, ['admin', 'superuser'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Acceso denegado. Se requieren permisos de administrador.'
    ]);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listarArchivos':
            listarArchivos();
            break;
        case 'agregarVista':
            agregarVista();
            break;
        case 'eliminarVista':
            eliminarVista();
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listarArchivos() {
    global $pdo;
    
    // Obtener el directorio actual de forma más robusta
    $directorio = dirname(__FILE__);
    $archivos = [];
    
    try {
        // Obtener todos los archivos PHP del directorio
        $files = glob($directorio . DIRECTORY_SEPARATOR . '*.php');
        
        if ($files === false) {
            throw new Exception('No se pudo leer el directorio');
        }
        
        // Obtener vistas ya registradas
        $stmt = $pdo->prepare("SELECT archivo, nombre, categoria FROM vistas_sistema");
        $stmt->execute();
        $vistas_registradas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vistas_registradas[$row['archivo']] = $row;
        }
        
        foreach ($files as $file) {
            $nombre_archivo = basename($file);
            
            // Excluir archivos del sistema y de depuración
            if (in_array($nombre_archivo, [
                'gestionar_vistas_sistema.php', 
                'gestionar_vistas_sistema_simple.php',
                'verificar_permisos.php',
                'debug_vistas.php'
            ])) {
                continue;
            }
            
            $registrado = isset($vistas_registradas[$nombre_archivo]);
            $info_vista = $vistas_registradas[$nombre_archivo] ?? null;
            
            $archivos[] = [
                'archivo' => $nombre_archivo,
                'registrado' => $registrado,
                'nombre' => $info_vista['nombre'] ?? '',
                'categoria' => $info_vista['categoria'] ?? ''
            ];
        }
        
        // Ordenar por estado (no registrados primero) y luego por nombre
        usort($archivos, function($a, $b) {
            if ($a['registrado'] != $b['registrado']) {
                return $a['registrado'] - $b['registrado'];
            }
            return strcmp($a['archivo'], $b['archivo']);
        });
        
        echo json_encode(['success' => true, 'archivos' => $archivos]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al listar archivos: ' . $e->getMessage()]);
    }
}

function agregarVista() {
    global $pdo;
    
    $archivo = $_POST['archivo'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $categoria = $_POST['categoria'] ?? 'General';
    
    // Validaciones básicas
    if (empty($archivo)) {
        throw new Exception('Archivo es obligatorio');
    }
    
    // Generar nombre automático si no se proporciona
    if (empty($nombre)) {
        $nombre = ucfirst(str_replace(['_', '.php'], [' ', ''], $archivo));
    }
    
    // Verificar que no exista ya
    $stmt = $pdo->prepare("SELECT id FROM vistas_sistema WHERE archivo = ?");
    $stmt->execute([$archivo]);
    if ($stmt->fetch()) {
        throw new Exception('El archivo ya está registrado');
    }
    
    $pdo->beginTransaction();
    
    try {
        // Insertar la nueva vista
        $stmt = $pdo->prepare("
            INSERT INTO vistas_sistema (archivo, nombre, categoria, es_critica, activa) 
            VALUES (?, ?, ?, 0, 1)
        ");
        $stmt->execute([$archivo, $nombre, $categoria]);
        
        $vista_id = $pdo->lastInsertId();
        
        // Asignar permisos a todos los usuarios (excepto solo_lectura para ciertas categorías)
        $stmt_usuarios = $pdo->prepare("SELECT id, role FROM usuarios");
        $stmt_usuarios->execute();
        $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt_permiso = $pdo->prepare("
            INSERT INTO permisos_usuario (user_id, vista_id, puede_acceder) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($usuarios as $usuario) {
            $puede_acceder = 1; // Por defecto todos tienen acceso
            if ($usuario['role'] === 'solo_lectura' && in_array($categoria, ['Administración', 'API'])) {
                $puede_acceder = 0;
            }
            $stmt_permiso->execute([$usuario['id'], $vista_id, $puede_acceder]);
        }
        
        // Log de seguridad
        $stmt_log = $pdo->prepare("
            INSERT INTO logs_seguridad (user_id, accion, detalles, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $detalles = "Vista agregada: $archivo ($nombre)";
        $stmt_log->execute([$_SESSION['user_id'], 'AGREGAR_VISTA', $detalles, $_SERVER['REMOTE_ADDR']]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Vista agregada exitosamente']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function eliminarVista() {
    global $pdo;
    
    $archivo = $_POST['archivo'] ?? '';
    
    if (empty($archivo)) {
        throw new Exception('Archivo es obligatorio');
    }
    
    // Obtener información de la vista
    $stmt = $pdo->prepare("SELECT id, archivo, nombre, es_critica FROM vistas_sistema WHERE archivo = ?");
    $stmt->execute([$archivo]);
    $vista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vista) {
        throw new Exception('Vista no encontrada');
    }
    
    if ($vista['es_critica']) {
        throw new Exception('No se puede eliminar una vista crítica');
    }
    
    $pdo->beginTransaction();
    
    try {
        // Eliminar permisos asociados
        $stmt = $pdo->prepare("DELETE FROM permisos_usuario WHERE vista_id = ?");
        $stmt->execute([$vista['id']]);
        
        // Eliminar la vista
        $stmt = $pdo->prepare("DELETE FROM vistas_sistema WHERE id = ?");
        $stmt->execute([$vista['id']]);
        
        // Log de seguridad
        $stmt_log = $pdo->prepare("
            INSERT INTO logs_seguridad (user_id, accion, detalles, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $detalles = "Vista eliminada: {$vista['archivo']} ({$vista['nombre']})";
        $stmt_log->execute([$_SESSION['user_id'], 'ELIMINAR_VISTA', $detalles, $_SERVER['REMOTE_ADDR']]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Vista eliminada exitosamente']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>
