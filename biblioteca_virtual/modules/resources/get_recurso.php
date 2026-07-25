<?php
// ============================================
// OBTENER DATOS DE UN RECURSO (AJAX - JSON)
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Solo usuarios con sesión iniciada pueden consultar
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'ID de recurso inválido']);
    exit;
}

$stmt = $db->prepare("
    SELECT r.*, c.nombre_categoria
    FROM recursos r
    LEFT JOIN categorias c ON r.id_categoria = c.id_categoria
    WHERE r.id_recurso = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$recurso = $stmt->get_result()->fetch_assoc();

if (!$recurso) {
    echo json_encode(['error' => 'Recurso no encontrado']);
    exit;
}

echo json_encode([
    'id_recurso'          => $recurso['id_recurso'],
    'titulo'               => e($recurso['titulo']),
    'autor'                => e($recurso['autor']),
    'tipo_recurso'         => e($recurso['tipo_recurso']),
    'categoria'            => e($recurso['nombre_categoria']),
    'isbn'                 => e($recurso['isbn']),
    'editorial'            => e($recurso['editorial']),
    'anio_publicacion'     => $recurso['anio_publicacion'],
    'cantidad_disponible'  => $recurso['cantidad_disponible'],
    'cantidad_total'       => $recurso['cantidad_total'],
    'ubicacion'            => e($recurso['ubicacion']),
    'descripcion'          => e($recurso['descripcion'])
]);