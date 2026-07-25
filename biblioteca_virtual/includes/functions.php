<?php
// ============================================
// FUNCIONES AUXILIARES DEL SISTEMA
// ============================================

// Escapar HTML para seguridad
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ============================================
// SISTEMA DE ALERTAS (mensajes de sesión)
// ============================================

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

// Obtener y limpiar el mensaje de alerta guardado (para páginas que arman su propio HTML)
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

// Mostrar la alerta guardada directo en HTML (para páginas que no arman su propio bloque)
function displayAlert() {
    $alert = getAlert();
    if ($alert) {
        $icons = [
            'success' => '✅',
            'error'   => '❌',
            'warning' => '⚠️',
            'info'    => 'ℹ️'
        ];
        $icon = $icons[$alert['type']] ?? 'ℹ️';

        echo '<div class="alert alert-' . e($alert['type']) . '">';
        echo '<span class="alert-icon">' . $icon . '</span>';
        echo e($alert['message']);
        echo '</div>';
    }
}

// ============================================
// TEXTO
// ============================================

// Cortar un texto largo y agregar "..." al final
function truncateText($text, $length = 100) {
    if (empty($text)) {
        return '';
    }
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

// ============================================
// ROLES
// ============================================

// Convertir id_rol en un nombre legible
function getRoleName($id_rol) {
    $roles = [
        1 => '👑 Administrador',
        2 => '📚 Bibliotecario',
        3 => '👤 Usuario'
    ];
    return $roles[$id_rol] ?? 'Desconocido';
}

// ============================================
// FECHAS
// ============================================

// Formatear una fecha de MySQL (YYYY-MM-DD HH:MM:SS) a un formato legible
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }
    return date($format, $timestamp);
}
?>