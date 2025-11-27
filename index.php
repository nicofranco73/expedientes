<?php
// Define la variable $vista para cargar la vista por defecto
$vista = 'dashboard'; 

// Verifica si se ha pasado un parámetro 'vista' en la URL
if (isset($_GET['vista'])) {
    $vista = $_GET['vista'];
}

// Ruta completa al archivo de la vista
$ruta_vista = 'vistas/' . $vista . '.php';

// Carga la vista si existe, si no, carga la vista de error 404
if (!file_exists($ruta_vista)) {
    $ruta_vista = 'vistas/404.php';
}

// Definir la ruta base del proyecto para el navegador (ej: /expedientes/)
// Asegúrate de que esta ruta coincida con el nombre de la carpeta en tu localhost.
$base_url = '/expedientes/'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Expedientes</title>

    <!-- Cargando Bootstrap CSS y JS desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Estilos Personalizados: Cargados desde archivo local usando PHP require_once -->
    <style>
        /* Variables y estilos generales del nuevo diseño */
        :root {
            --sidebar-width: 15rem;
            --color-sidebar-bg: #014073; /* Azul oscuro */
            --color-navbar-bg: #00609b; /* Azul medio */
            --color-active: #ffffff; /* Blanco para el texto activo */
        }
        
        body {
            overflow-x: hidden;
            background-color: #f8f9fa; /* Fondo blanco/gris claro */
        }

        #wrapper {
            display: flex;
        }

        /* Estilos del Sidebar */
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            margin-left: calc(-1 * var(--sidebar-width));
            transition: margin .25s ease-out;
            background-color: var(--color-sidebar-bg) !important;
            border-right: none;
            flex-shrink: 0; 
        }

        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0;
        }
        
        /* Estilos del contenido principal */
        #page-content-wrapper {
            min-width: 100vw;
            flex-grow: 1;
        }
        
        #wrapper.toggled #page-content-wrapper {
            min-width: calc(100vw - var(--sidebar-width));
        }

        /* Navbar */
        .navbar-custom {
            background-color: var(--color-navbar-bg) !important;
            color: white;
            padding: 0.5rem 1rem;
        }
        
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .btn {
            color: white !important;
        }

        /* Items del Sidebar */
        .list-group-item {
            background-color: var(--color-sidebar-bg) !important;
            color: #ccc !important;
            border: none;
            padding: 1rem 1.25rem;
            font-size: 1.05rem;
            border-radius: 0;
        }
        
        .list-group-item.active,
        .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: var(--color-active) !important;
            border-left: 4px solid white !important; /* Indicador activo en la imagen */
        }

        .list-group-item.active {
            border-left: 4px solid white !important;
        }
        
        /* Ocultar el menu-toggle en desktop */
        #menu-toggle {
            display: none;
        }

        /* Responsive adjustments */
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                min-width: 0;
            }
            
            #wrapper.toggled #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            #menu-toggle {
                display: block; /* Mostrar solo el toggle en pantallas grandes si el sidebar está oculto */
            }
            
            #wrapper:not(.toggled) #menu-toggle {
                display: none; /* Ocultar el toggle cuando el sidebar está visible (comportamiento por defecto) */
            }
            
            /* En desktop, siempre visible por defecto */
            #sidebar-wrapper {
                margin-left: 0;
            }
            #page-content-wrapper {
                min-width: 0;
                width: calc(100% - var(--sidebar-width));
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #wrapper.toggled #page-content-wrapper {
                width: 100%;
            }
        }
        
        /* Asegurar que el contenido no quede pegado al borde */
        .container-fluid {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        /* Estilos específicos para la navbar que se parece a la imagen */
        .header-brand {
            font-size: 1.5rem;
            font-weight: 500;
            margin-left: 1rem;
        }
        
        /* Incluir contenido de custom.css */
        <?php 
            // Ruta del archivo custom.css desde el index.php
            $css_path = 'vistas/publico/css/custom.css';
            
            if (file_exists($css_path)) {
                // Incluye el contenido del archivo CSS
                require_once $css_path;
            } else {
                echo "/* Error: No se pudo cargar el archivo CSS personalizado: $css_path */";
            }
        ?>
    </style>
</head>
<body>

    <!-- Contenedor principal del Dashboard -->
    <div class="d-flex" id="wrapper">
        
        <!-- Sidebar (Menú de Navegación) -->
        <div class="text-white" id="sidebar-wrapper">
            <div class="list-group list-group-flush pt-4">
                
                <a href="<?= $base_url ?>?vista=dashboard" class="list-group-item list-group-item-action <?= ($vista === 'dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-3"></i> Dashboard
                </a>
                
                <a href="<?= $base_url ?>?vista=acciones_expedientes" class="list-group-item list-group-item-action <?= ($vista === 'acciones_expedientes') ? 'active' : '' ?>">
                    <i class="bi bi-folder-fill me-3"></i> Expedientes
                </a>
                
                <a href="<?= $base_url ?>?vista=iniciadores" class="list-group-item list-group-item-action <?= ($vista === 'iniciadores') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill me-3"></i> Iniciadores
                </a>
                
                <a href="<?= $base_url ?>?vista=consulta" class="list-group-item list-group-item-action <?= ($vista === 'consulta') ? 'active' : '' ?>">
                    <i class="bi bi-search me-3"></i> Consulta de Expedientes
                </a>
            </div>
        </div>
        
        <!-- Contenido de la Página -->
        <div id="page-content-wrapper">
            
            <!-- Navbar (Barra Superior) -->
            <nav class="navbar navbar-expand-lg navbar-light navbar-custom shadow-sm">
                <div class="container-fluid">
                    
                    <!-- Botón para ocultar/mostrar sidebar en móvil o desktop -->
                    <button class="btn btn-primary" id="menu-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <!-- Logo y Título: CDE Expedientes -->
                    <a class="navbar-brand d-flex align-items-center" href="<?= $base_url ?>">
                        <!-- Placeholder del Logo -->
                        <span class="d-inline-block me-3" style="width: 30px; height: 30px; background-color: white; border-radius: 50%;"></span>
                        <span class="header-brand text-white">Expedientes</span>
                    </a>
                    
                    <!-- Usuario y Botón de Salir (Alineado a la derecha) -->
                    <div class="d-flex align-items-center ms-auto">
                        <span class="me-3 text-white">Usuario: **nicolás**</span>
                        <a href="#" class="btn btn-outline-light d-flex align-items-center">
                            <i class="bi bi-box-arrow-right me-1"></i> Salir
                        </a>
                    </div>
                </div>
            </nav>
            
            <!-- Contenido de la Vista Dinámica -->
            <div class="container-fluid">
                <?php include $ruta_vista; ?>
            </div>
            
        </div>
    </div>
    
    <!-- Script de Bootstrap (Cargado por CDN para asegurar el funcionamiento) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Script para la funcionalidad del Sidebar Toggle -->
    <script>
        document.getElementById('menu-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('wrapper').classList.toggle('toggled');
        });
        
        // Manejo del estado de la sidebar en desktop y móvil
        function handleSidebarToggle() {
            if (window.innerWidth >= 768) {
                // En desktop, la sidebar está visible por defecto
                document.getElementById('wrapper').classList.remove('toggled');
            } else {
                // En móvil, la sidebar está oculta por defecto
                document.getElementById('wrapper').classList.add('toggled');
            }
        }

        window.addEventListener('load', handleSidebarToggle);
        window.addEventListener('resize', handleSidebarToggle);
    </script>
</body>
</html>