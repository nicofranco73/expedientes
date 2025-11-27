<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class AuthMiddleware {
    private static $permisos = [
        // Páginas administrativas - Solo admin y superuser
        'crear_usuario.php' => ['admin', 'superuser'],
        'listar_usuarios.php' => ['admin', 'superuser'],
        'eliminar_usuario.php' => ['admin', 'superuser'],
        
        // Páginas exclusivas de superuser
        'gestionar_roles_usuarios.php' => ['superuser'],
        'procesar_cambio_rol.php' => ['superuser'],
        'configuracion_permisos.php' => ['admin', 'superuser'],
        'verificacion_permisos.php' => ['admin', 'superuser'],
        
        // Páginas de gestión de expedientes - Admin, superuser y usuario
        'carga_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'listar_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'actualizar_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'eliminar_expediente.php' => ['admin', 'superuser'],
        'pases_expediente.php' => ['admin', 'superuser', 'usuario'],
        'editar_pase.php' => ['admin', 'superuser', 'usuario'],
        'eliminar_pase.php' => ['admin', 'superuser', 'usuario'],
        'historial_expediente.php' => ['admin', 'superuser', 'usuario'],
        
        // Páginas de gestión de iniciadores
        'carga_iniciador.php' => ['admin', 'superuser', 'usuario'],
        'listar_iniciadores.php' => ['admin', 'superuser', 'usuario'],
        'carga_concejal.php' => ['admin', 'superuser', 'usuario'],
        'listar_concejales.php' => ['admin', 'superuser', 'usuario'],
        'carga_persona_juri_entidad.php' => ['admin', 'superuser', 'usuario'],
        'listar_persona_juri_entidad.php' => ['admin', 'superuser', 'usuario'],
        
        // Búsquedas - Todos los roles
        'buscar_expediente.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        'busqueda_rapida.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        'consulta.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        'resultados.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        
        // Dashboard - Todos los usuarios logueados
        'dashboard.php' => ['admin', 'superuser', 'usuario', 'consulta'],
        
        // Páginas de procesamiento (Controladores)
        'procesar_carga_expedientes.php' => ['admin', 'superuser', 'usuario'],
        'procesar_carga_iniciador.php' => ['admin', 'superuser', 'usuario'],
        'procesar_carga_concejal.php' => ['admin', 'superuser', 'usuario'],
        'procesar_carga_entidad.php' => ['admin', 'superuser', 'usuario'],
        'procesar_usuario.php' => ['admin', 'superuser'],
        'procesar_pase.php' => ['admin', 'superuser', 'usuario'],
        'procesar_actualizacion.php' => ['admin', 'superuser', 'usuario'],
        
        // PDFs y obtención de datos
        'pdf_auto_descarga.php' => ['admin', 'superuser', 'usuario'],
        'generar_pdf_expediente.php' => ['admin', 'superuser', 'usuario'],
        'obtener_expediente.php' => ['admin', 'superuser', 'usuario'],
        'obtener_historial.php' => ['admin', 'superuser', 'usuario'],
        'obtener_historial_pases.php' => ['admin', 'superuser', 'usuario']
    ];

    public static function verificarPermiso($vista) {
        // 1. Verificación de Autenticación
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
            $_SESSION['mensaje'] = 'Debe iniciar sesión para acceder a esta página';
            $_SESSION['tipo_mensaje'] = 'warning';
            // Asumiendo que el login está en la raíz
            header('Location: /login.php'); 
            exit;
        }

        $rol = $_SESSION['rol'];

        // Si la vista no está definida, permitir acceso (comportamiento original)
        if (!isset(self::$permisos[$vista])) {
            return true;
        }

        // 2. Verificación de Autorización
        if (!in_array($rol, self::$permisos[$vista])) {
            $_SESSION['mensaje'] = 'No tiene permisos para acceder a esta página';
            $_SESSION['tipo_mensaje'] = 'danger';
            
            // Redirigir según el rol (lógica copiada y simplificada)
            if (in_array($rol, ['superuser', 'admin', 'usuario'])) {
                header('Location: /dashboard.php');
            } elseif ($rol === 'consulta') {
                header('Location: /consulta.php');
            } else {
                header('Location: /login.php');
            }
            exit;
        }
        return true;
    }
}
?>