<?php
// ============================================
// CONFIGURACIÓN GENERAL DEL SISTEMA
// ============================================

// Configuración de zona horaria
date_default_timezone_set('America/Santo_Domingo');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Constantes del sistema
define('SITE_NAME', 'Biblioteca Virtual');
define('SITE_URL', 'http://localhost:8080/biblioteca_virtual/');
define('SITE_VERSION', '1.0.0');

// Configuración de rutas
define('ROOT_PATH', dirname(__DIR__) . '/');
define('ASSETS_URL', SITE_URL . 'assets/');
define('MODULES_PATH', ROOT_PATH . 'modules/');

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de seguridad
define('SALT', 'BIBLIOTECA_VIRTUAL_2024_*&^%$#@!');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para depuración (desactivar en producción)
function debug($data) {
    echo '<pre style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin: 10px;">';
    print_r($data);
    echo '</pre>';
}
?>