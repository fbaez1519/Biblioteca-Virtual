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

    <?php $alert = getAlert(); if ($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?>">
            <?php echo e($alert['message']); ?>
        </div>
    <?php endif; ?>

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
                                    <a href="cancel.php?id=<?php echo $p['id_prestamo']; ?>" 
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
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    font-size: 1.8rem;
    color: #2d3436;
}

.page-header p {
    color: #636e72;
    margin-top: 0.2rem;
}

.filters-section {
    background: white;
    padding: 1.2rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 0.6rem 1rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 0.9rem;
    font-family: 'Inter', sans-serif;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #6C5CE7;
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
}

.empty-state {
    background: white;
    padding: 3rem 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.empty-state p {
    color: #b2bec3;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow-x: auto;
}

.loans-table {
    width: 100%;
    border-collapse: collapse;
}

.loans-table th {
    text-align: left;
    padding: 1rem;
    background: #f8f9fc;
    color: #636e72;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    white-space: nowrap;
}

.loans-table td {
    padding: 1rem;
    border-top: 1px solid #f0f0f0;
    vertical-align: top;
    font-size: 0.9rem;
}

.loans-table small {
    display: block;
    color: #b2bec3;
    font-size: 0.78rem;
    margin-top: 0.2rem;
}

.text-muted {
    color: #b2bec3;
}

.status {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-activo {
    background: #e8f8ed;
    color: #00B894;
}

.status-devuelto {
    background: #e8f0fe;
    color: #4169E1;
}

.status-vencido {
    background: #fde8e8;
    color: #E17055;
}

.status-cancelado {
    background: #f0f0f0;
    color: #636e72;
}

.btn-success {
    background: #00B894;
    color: white;
}

.btn-danger {
    background: #E17055;
    color: white;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>