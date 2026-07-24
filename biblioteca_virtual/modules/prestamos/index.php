<?php
// ============================================
// LISTADO DE PRÉSTAMOS
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$db = getDB();
$userId = $auth->getUserId();
$isAdmin = $auth->isAdmin() || $auth->isBibliotecario();

// Filtros
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construir consulta
$where = [];
$params = [];
$types = '';

if (!$isAdmin) {
    $where[] = 'p.id_usuario = ?';
    $params[] = $userId;
    $types .= 'i';
}

if ($estado) {
    $where[] = 'p.estado = ?';
    $params[] = $estado;
    $types .= 's';
}

if ($search) {
    $where[] = '(r.titulo LIKE ? OR u.nombre_completo LIKE ?)';
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT p.*, 
           u.nombre_completo as usuario_nombre, 
           u.email as usuario_email,
           r.titulo as recurso_titulo,
           r.autor as recurso_autor
    FROM prestamos p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    JOIN recursos r ON p.id_recurso = r.id_recurso
    $whereClause
    ORDER BY p.fecha_prestamo DESC
";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$prestamos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$estados = [
    'activo' => '🟢 Activo',
    'devuelto' => '✅ Devuelto',
    'vencido' => '🔴 Vencido',
    'cancelado' => '⛔ Cancelado'
];

$pageTitle = 'Préstamos';
include '../../includes/header.php';
?>

<div class="loans-page">
    <div class="page-header">
        <div>
            <h1>📋 Préstamos</h1>
            <p>Gestión de préstamos de recursos</p>
        </div>
        <a href="new.php" class="btn btn-primary">➕ Nuevo Préstamo</a>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="🔍 Buscar..." 
                       value="<?php echo e($search); ?>" class="filter-input">
            </div>
            
            <div class="filter-group">
                <select name="estado" class="filter-select">
                    <option value="">📋 Todos los estados</option>
                    <?php foreach ($estados as $key => $label): ?>
                        <option value="<?php echo $key; ?>" 
                            <?php echo $estado == $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
            <a href="index.php" class="btn btn-outline">🔄 Limpiar</a>
        </form>
    </div>

    <?php if (empty($prestamos)): ?>
        <div class="empty-state">
            <p>📭 No hay préstamos registrados</p>
            <a href="new.php" class="btn btn-primary">Crear primer préstamo</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="loans-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recurso</th>
                        <th>Usuario</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Devolución</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prestamos as $p): ?>
                        <tr>
                            <td>#<?php echo $p['id_prestamo']; ?></td>
                            <td>
                                <strong><?php echo e($p['recurso_titulo']); ?></strong>
                                <small><?php echo e($p['recurso_autor']); ?></small>
                            </td>
                            <td>
                                <?php echo e($p['usuario_nombre']); ?>
                                <small><?php echo e($p['usuario_email']); ?></small>
                            </td>
                            <td><?php echo formatDate($p['fecha_prestamo']); ?></td>
                            <td>
                                <?php if ($p['fecha_devolucion_real']): ?>
                                    <?php echo formatDate($p['fecha_devolucion_real']); ?>
                                <?php else: ?>
                                    <span class="text-muted">Pendiente</span>
                                    <br>
                                    <small>Esperada: <?php echo formatDate($p['fecha_devolucion_esperada']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status status-<?php echo $p['estado']; ?>">
                                    <?php echo $estados[$p['estado']] ?? $p['estado']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['estado'] == 'activo'): ?>
                                    <a href="return.php?id=<?php echo $p['id_prestamo']; ?>" 
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('¿Registrar devolución?')">
                                        ✅ Devolver
                                    </a>
                                <?php endif; ?>
                                <?php if ($isAdmin && $p['estado'] == 'activo'): ?>
                                    <a href="?action=cancel&id=<?php echo $p['id_prestamo']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Cancelar préstamo?')">
                                        ⛔ Cancelar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.loans-page {
    padding: 1rem 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap