<?php
// ============================================
// FUNCIONES AUXILIARES DEL SISTEMA
// ============================================

// Escapar HTML para seguridad
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Guardar un mensaje de alerta en sesión (se muestra en la próxima carga de página)
function setAlert($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type // success | error | warning | info
    ];
}

// Obtener y limpiar el mensaje de alerta guardado
function getAlert() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Formatear una fecha (acepta NULL sin generar error)
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }
    return date($format, $timestamp);
}
?>