<?php
// ============================================
// FUNCIONES AUXILIARES DEL SISTEMA
// ============================================

// Escapar HTML para seguridad
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ============================================
// SISTEMA DE ALERTAS (mensajes de sesión)
// ============================================

// Guardar una alerta para mostrarla en la siguiente página
function setAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type // success, error, warning, info
    ];
}

// Mostrar la alerta guardada (y borrarla para que no se repita)
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
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

        unset($_SESSION['alert']);
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
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'N/A';
    }
    return date($format, $timestamp);
}