<?php
// ============================================
// CANCELAR PRÉSTAMO
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireBibliotecario();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setAlert('ID de préstamo inválido', 'error');
    header('Location: index.php');
    exit;
}

// Obtener datos del préstamo
$stmt = $db->prepare("SELECT * FROM prestamos WHERE id_prestamo = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$prestamo = $stmt->get_result()->fetch_assoc();

if (!$prestamo) {
    setAlert('Préstamo no encontrado', 'error');
    header('Location: index.php');
    exit;
}

if ($prestamo['estado'] !== 'activo') {
    setAlert('Solo se pueden cancelar préstamos activos', 'warning');
    header('Location: index.php');
    exit;
}

// Procesar cancelación
$db->begin_transaction();

try {
    // Actualizar estado del préstamo
    $stmt = $db->prepare("
        UPDATE prestamos 
        SET estado = 'cancelado' 
        WHERE id_prestamo = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Devolver la disponibilidad del recurso, ya que el préstamo no se concretó
    $stmt = $db->prepare("
        UPDATE recursos 
        SET cantidad_disponible = cantidad_disponible + 1 
        WHERE id_recurso = ?
    ");
    $stmt->bind_param("i", $prestamo['id_recurso']);
    $stmt->execute();

    $db->commit();

    setAlert('Préstamo cancelado correctamente', 'success');
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $db->rollback();
    setAlert('Error al cancelar préstamo: ' . $e->getMessage(), 'error');
    header('Location: index.php');
    exit;
}
?>