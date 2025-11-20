<?php
// Iniciar sesión de forma segura
session_start();
session_regenerate_id(true);

// Configurar headers de seguridad mejorados
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; script-src 'self' https://cdn.jsdelivr.net;");
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(), camera=()');

// Función para escapar output de forma segura
function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generar captcha más seguro
$caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha = '';
try {
    for ($i = 0; $i < 4; $i++) {
        $captcha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    // Guardar el captcha en texto plano en la sesión para validación simple
    $_SESSION['captcha_code'] = $captcha;
} catch (Exception $e) {
    error_log('Error al generar captcha: ' . $e->getMessage());
    die('Error del sistema');
}

// Validar que la sesión esté activa
if (session_status() !== PHP_SESSION_ACTIVE) {
    die('Error de sesión');
}