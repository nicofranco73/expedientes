<?php 
// **NOTA IMPORTANTE:** La lógica de sesión y redirección (session_start(), if (isset($_SESSION['usuario'])) ...) DEBE ejecutarse
// ANTES de incluir esta vista, generalmente en un archivo router/controlador principal.
// Por el momento, dejaremos la lógica de mensajes tal cual, pero la limpiaremos más adelante.

// Asumiendo que $mensaje y $tipo_mensaje vienen definidos por el controlador que llama a esta vista.
$mensaje = $mensaje ?? '';
$tipo_mensaje = $tipo_mensaje ?? '';

// **NOTA:** La captura 1 (Original) muestra un logo. Es crucial agregarlo.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expedientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-box {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-container {
            height: 100vh;
        }
        .logo-img {
            max-width: 150px; /* Ajustar según sea necesario */
            height: auto;
        }
        /* Estilos para replicar el color del botón del original (Captura 1) */
        .btn-primary {
            background-color: #3b7bbf; /* Color azul del original */
            border-color: #3b7bbf;
        }
        .btn-primary:hover {
            background-color: #2b5c90;
            border-color: #2b5c90;
        }
        
        /* Estilo para el título del sistema */
        .system-title {
            color: #3b7bbf; /* Color del título en el original */
            font-weight: 600;
        }
        
        /* Estilo para los inputs para parecerse más al original (Captura 1) */
        .form-control:focus {
            border-color: #7abaff;
            box-shadow: 0 0 0 0.25rem rgba(59, 123, 191, 0.25);
        }
    </style>
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center min-vh-100">
        <div class="main-box login w-100 text-center" style="max-width: 400px;">
            
                        <div class="mb-4">
                                                <img src="../recursos/logo_cde.png" alt="Logo CDE" class="logo-img mx-auto d-block mb-3">
                <h2 class="system-title mb-1">Sistema de Expedientes</h2>
                <p class="text-muted small">Ingrese su usuario y contraseña para acceder</p>
            </div>
            
            <form action="../controladores/LoginController.php" method="POST" class="text-start">
                
                <?php 
                // Lógica de mensajes de sesión (Mantenida por ahora)
                if ($mensaje): 
                    $clase = ($tipo_mensaje == 'error') ? 'alert-danger' : 'alert-success';
                ?>
                    <div class="alert <?php echo $clase; ?> text-center" role="alert">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php 
                endif; 
                ?>

                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required autofocus autocomplete="username">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>