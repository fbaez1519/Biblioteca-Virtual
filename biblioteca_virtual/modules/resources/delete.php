<?php
// ============================================
// ELIMINAR RECURSO
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireBibliotecario();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setAlert('ID de recurso inválido', 'error');
    header('Location: index.php');
    exit;
}

// Verificar si el recurso existe
$stmt = $db->prepare("SELECT titulo FROM recursos WHERE id_recurso = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$recurso = $stmt->get_result()->fetch_assoc();

if (!$recurso) {
    setAlert('Recurso no encontrado', 'error');
    header('Location: index.php');
    exit;
}

// Verificar si tiene préstamos activos
$stmt = $db->prepare("SELECT COUNT(*) as total FROM prestamos WHERE id_recurso = ? AND estado = 'activo'");
$stmt->bind_param("i", $id);
$stmt->execute();
$prestamosActivos = $stmt->get_result()->fetch_assoc()['total'];

// Si tiene préstamos activos, solo desactivar
if ($prestamosActivos > 0) {
    $stmt = $db->prepare("UPDATE recursos SET activo = 0 WHERE id_recurso = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    setAlert('El recurso tiene préstamos activos. Se ha desactivado.', 'warning');
} else {
    // Eliminar completamente
    $stmt = $db->prepare("DELETE FROM recursos WHERE id_recurso = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    setAlert('Recurso eliminado correctamente', 'success');
}

header('Location: index.php');
exit;
?>