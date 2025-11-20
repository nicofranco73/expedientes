<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Acceso denegado');
}

// Verificar permisos (solo admin y superuser)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superuser'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para esta acción']);
    exit;
}

header('Content-Type: application/json');

try {
    // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $accion = $_POST['accion'] ?? $_GET['accion'] ?? 'agregar';
    
    switch ($accion) {
        case 'agregar':
            agregarVista($db);
            break;
            
        case 'editar':
            editarVista($db);
            break;
            
        case 'eliminar':
            eliminarVista($db);
            break;
            
        case 'eliminar_por_nombre':
            eliminarVistaPorNombre($db);
            break;
            
        case 'explorar':
            explorarArchivos($db);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en gestionar_vistas_sistema.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()]);
}

function agregarVista($db) {
    $nombre_vista = trim($_POST['nombre_vista'] ?? '');
    $nombre_amigable = trim($_POST['nombre_amigable'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'General');
    $es_critica = isset($_POST['es_critica']) ? 1 : 0;
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre_vista) || empty($nombre_amigable)) {
        echo json_encode(['success' => false, 'message' => 'Nombre del archivo y nombre amigable son obligatorios']);
        return;
    }
    
    if (!str_ends_with($nombre_vista, '.php')) {
        $nombre_vista .= '.php';
    }
    
    // Verificar que no exista ya
    $stmt = $db->prepare('SELECT id FROM vistas_sistema WHERE nombre_vista = ?');
    $stmt->execute([$nombre_vista]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una vista con ese nombre de archivo']);
        return;
    }
    
    // Insertar la nueva vista
    $stmt = $db->prepare('
        INSERT INTO vistas_sistema (nombre_vista, nombre_amigable, descripcion, categoria, es_critica, activa) 
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([$nombre_vista, $nombre_amigable, $descripcion, $categoria, $es_critica, $activa]);
    
    // Asignar permisos por defecto a usuarios existentes
    asignarPermisosDefecto($db, $nombre_vista, $es_critica);
    
    // Registrar en logs
    registrarAccion('AGREGAR_VISTA', "Vista agregada: $nombre_vista ($nombre_amigable)", $db);
    
    echo json_encode(['success' => true, 'message' => 'Vista agregada exitosamente']);
}

function editarVista($db) {
    $vista_id = (int)$_POST['vista_id'];
    $nombre_amigable = trim($_POST['nombre_amigable'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'General');
    $es_critica = isset($_POST['es_critica']) ? 1 : 0;
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    if (empty($nombre_amigable)) {
        echo json_encode(['success' => false, 'message' => 'El nombre amigable es obligatorio']);
        return;
    }
    
    // Verificar que la vista existe
    $stmt = $db->prepare('SELECT nombre_vista FROM vistas_sistema WHERE id = ?');
    $stmt->execute([$vista_id]);
    $vista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vista) {
        echo json_encode(['success' => false, 'message' => 'Vista no encontrada']);
        return;
    }
    
    // Actualizar la vista
    $stmt = $db->prepare('
        UPDATE vistas_sistema 
        SET nombre_amigable = ?, descripcion = ?, categoria = ?, es_critica = ?, activa = ?
        WHERE id = ?
    ');
    
    $stmt->execute([$nombre_amigable, $descripcion, $categoria, $es_critica, $activa, $vista_id]);
    
    // Registrar en logs
    registrarAccion('EDITAR_VISTA', "Vista editada: {$vista['nombre_vista']} ($nombre_amigable)", $db);
    
    echo json_encode(['success' => true, 'message' => 'Vista actualizada exitosamente']);
}

function eliminarVista($db) {
    $vista_id = (int)$_POST['vista_id'];
    
    // Verificar que la vista existe y no es crítica
    $stmt = $db->prepare('SELECT nombre_vista, es_critica FROM vistas_sistema WHERE id = ?');
    $stmt->execute([$vista_id]);
    $vista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vista) {
        echo json_encode(['success' => false, 'message' => 'Vista no encontrada']);
        return;
    }
    
    if ($vista['es_critica']) {
        echo json_encode(['success' => false, 'message' => 'No se pueden eliminar vistas críticas']);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Eliminar permisos asociados
        $stmt = $db->prepare('DELETE FROM permisos_usuario WHERE vista = ?');
        $stmt->execute([$vista['nombre_vista']]);
        
        // Eliminar la vista
        $stmt = $db->prepare('DELETE FROM vistas_sistema WHERE id = ?');
        $stmt->execute([$vista_id]);
        
        $db->commit();
        
        // Registrar en logs
        registrarAccion('ELIMINAR_VISTA', "Vista eliminada: {$vista['nombre_vista']}", $db);
        
        echo json_encode(['success' => true, 'message' => 'Vista eliminada exitosamente']);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function eliminarVistaPorNombre($db) {
    $nombre_vista = trim($_POST['nombre_vista'] ?? '');
    
    if (empty($nombre_vista)) {
        echo json_encode(['success' => false, 'message' => 'Nombre de vista no especificado']);
        return;
    }
    
    // Verificar que la vista existe y no es crítica
    $stmt = $db->prepare('SELECT id, es_critica FROM vistas_sistema WHERE nombre_vista = ?');
    $stmt->execute([$nombre_vista]);
    $vista = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vista) {
        echo json_encode(['success' => false, 'message' => 'Vista no encontrada']);
        return;
    }
    
    if ($vista['es_critica']) {
        echo json_encode(['success' => false, 'message' => 'No se pueden eliminar vistas críticas']);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Eliminar permisos asociados
        $stmt = $db->prepare('DELETE FROM permisos_usuario WHERE vista = ?');
        $stmt->execute([$nombre_vista]);
        
        // Eliminar la vista
        $stmt = $db->prepare('DELETE FROM vistas_sistema WHERE nombre_vista = ?');
        $stmt->execute([$nombre_vista]);
        
        $db->commit();
        
        // Registrar en logs
        registrarAccion('ELIMINAR_VISTA', "Vista eliminada: $nombre_vista", $db);
        
        echo json_encode(['success' => true, 'message' => 'Vista eliminada exitosamente']);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function explorarArchivos($db) {
    $directorio_vistas = __DIR__;
    
    // Obtener archivos PHP del directorio
    $archivos = [];
    if (is_dir($directorio_vistas)) {
        $files = scandir($directorio_vistas);
        foreach ($files as $file) {
            if (str_ends_with($file, '.php') && $file !== '.' && $file !== '..') {
                $archivos[] = $file;
            }
        }
    }
    
    // Obtener archivos ya registrados
    $stmt = $db->query('SELECT nombre_vista FROM vistas_sistema');
    $archivos_registrados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    sort($archivos);
    
    echo json_encode([
        'success' => true, 
        'archivos' => $archivos,
        'archivos_registrados' => $archivos_registrados,
        'total_archivos' => count($archivos),
        'total_registrados' => count($archivos_registrados)
    ]);
}

function asignarPermisosDefecto($db, $nombre_vista, $es_critica) {
    // Obtener todos los usuarios activos
    $stmt = $db->query('SELECT id, role, is_superuser FROM usuarios WHERE is_active = 1');
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_permiso = $db->prepare('
        INSERT INTO permisos_usuario (user_id, vista, puede_acceder) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE puede_acceder = VALUES(puede_acceder)
    ');
    
    foreach ($usuarios as $usuario) {
        $puede_acceder = true;
        
        // Lógica de permisos por defecto
        if ($usuario['is_superuser'] == 1) {
            $puede_acceder = true; // Super usuario siempre tiene acceso
        } elseif ($usuario['role'] === 'admin') {
            $puede_acceder = true; // Admin tiene acceso a todo
        } elseif ($usuario['role'] === 'editor') {
            $puede_acceder = !$es_critica; // Editor no puede acceder a vistas críticas
        } elseif ($usuario['role'] === 'usuario') {
            $puede_acceder = !$es_critica; // Usuario normal no puede acceder a vistas críticas
        } elseif ($usuario['role'] === 'viewer') {
            $puede_acceder = !$es_critica; // Solo lectura no puede acceder a vistas críticas
        }
        
        $stmt_permiso->execute([$usuario['id'], $nombre_vista, $puede_acceder ? 1 : 0]);
    }
}

function registrarAccion($accion, $descripcion, $db) {
    try {
        $stmt = $db->prepare('INSERT INTO logs_seguridad (user_id, accion, descripcion, ip_address, fecha) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $accion,
            $descripcion,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Si falla el log, no es crítico
        error_log("Error registrando acción: " . $e->getMessage());
    }
}
?>
