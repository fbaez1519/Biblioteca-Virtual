<?php
// ============================================
// DEVOLUCIÓN DE PRÉSTAMO
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
$stmt = $db->prepare("
    SELECT p.*, r.titulo, u.nombre_completo 
    FROM prestamos p
    JOIN recursos r ON p.id_recurso = r.id_recurso
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    WHERE p.id_prestamo = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$prestamo = $stmt->get_result()->fetch_assoc();

if (!$prestamo) {
    setAlert('Préstamo no encontrado', 'error');
    header('Location: index.php');
    exit;
}

if ($prestamo['estado'] !== 'activo') {
    setAlert('Este préstamo ya fue devuelto o cancelado', 'warning');
    header('Location: index.php');
    exit;
}

// Procesar devolución
$db->begin_transaction();

try {
    // Actualizar préstamo
    $stmt = $db->prepare("
        UPDATE prestamos 
        SET estado = 'devuelto', 
            fecha_devolucion_real = NOW() 
        WHERE id_prestamo = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Actualizar cantidad disponible
    $stmt = $db->prepare("
        UPDATE recursos 
        SET cantidad_disponible = cantidad_disponible + 1 
        WHERE id_recurso = ?
    ");
    $stmt->bind_param("i", $prestamo['id_recurso']);
    $stmt->execute();
    
    $db->commit();
    
    setAlert('Devolución registrada correctamente', 'success');
    header('Location: index.php');
    exit;
    
} catch (Exception $e) {
    $db->rollback();
    setAlert('Error al registrar devolución: ' . $e->getMessage(), 'error');
    header('Location: index.php');
    exit;
}
?>