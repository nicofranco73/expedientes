<?php 
session_start();

$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';

// Limpiar la sesión inmediatamente después de leer el mensaje
if (isset($_SESSION['mensaje'])) {
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Si hay sesión activa, redirige al dashboard.
if (isset($_SESSION['usuario'])) {
    // Redirección relativa a dashboard.php dentro de la misma carpeta 'vistas/'
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expedientes</title>
    <!-- Ruta absoluta para Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de Bootstrap -->
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
    </style>
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center min-vh-100">
        <div class="main-box login w-100" style="max-width: 400px;">
            <!-- INICIO: Formulario de Login -->
            <!-- RUTA RELATIVA: Desde /vistas/ subimos (..) a /expedientes/ y entramos a /controladores/ -->
            <form action="../controladores/loginController.php" method="POST">
                
                <h2 class="text-center mb-4">Sistema de Expedientes</h2>

                <?php 
                // Lógica de mensajes de sesión (si existe)
                if ($mensaje): 
                    $clase = ($tipo_mensaje == 'error') ? 'alert-danger' : 'alert-success';
                ?>
                    <div class="alert <?php echo $clase; ?> text-center" role="alert">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php 
                endif; 
                ?>

                <!-- Campo Usuario -->
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
                </div>

                <!-- Campo Contraseña -->
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <!-- Botón Ingresar -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                </div>
            </form>
            <!-- FIN: Formulario de Login -->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>