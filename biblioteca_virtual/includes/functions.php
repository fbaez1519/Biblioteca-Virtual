// Escapar HTML para seguridad
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}