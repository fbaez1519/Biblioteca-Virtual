<?php
// ============================================
// PANEL DE ADMINISTRACIÓN
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Verificar que sea administrador
requireAdmin();

$db = getDB();
$user = $auth->getUser();

// Estadísticas del sistema
$stats = [];

// Total de usuarios
$result = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
$stats['usuarios'] = $result->fetch_assoc()['total'];

// Total de recursos
$result = $db->query("SELECT COUNT(*) as total FROM recursos WHERE activo = 1");
$stats['recursos'] = $result->fetch_assoc()['total'];

// Préstamos activos
$result = $db->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'activo'");
$stats['prestamos_activos'] = $result->fetch_assoc()['total'];

// Préstamos vencidos
$result = $db->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'vencido'");
$stats['prestamos_vencidos'] = $result->fetch_assoc()['total'];

// Últimos usuarios registrados
$usuarios_recientes = $db->query("
    SELECT * FROM usuarios 
    ORDER BY fecha_registro DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Préstamos recientes
$prestamos_recientes = $db->query("
    SELECT p.*, u.nombre_completo, r.titulo 
    FROM prestamos p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    JOIN recursos r ON p.id_recurso = r.id_recurso
    ORDER BY p.fecha_prestamo DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Panel de Administración';
include '../../includes/header.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <div>
            <h1>👑 Panel de Administración</h1>
            <p>Bienvenido, <?php echo e($user['nombre_completo']); ?></p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>modules/admin/usuarios.php" class="btn btn-primary">
                👥 Gestionar Usuarios
            </a>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $stats['usuarios']; ?></h3>
                <p>Usuarios Registrados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h3><?php echo $stats['recursos']; ?></h3>
                <p>Recursos Disponibles</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <h3><?php echo $stats['prestamos_activos']; ?></h3>
                <p>Préstamos Activos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-info">
                <h3><?php echo $stats['prestamos_vencidos']; ?></h3>
                <p>Préstamos Vencidos</p>
            </div>
        </div>
    </div>

    <div class="admin-grid">
        <!-- Usuarios recientes -->
        <div class="admin-card">
            <div class="card-header">
                <h3>📝 Últimos Usuarios</h3>
                <a href="usuarios.php" class="btn-link">Ver todos →</a>
            </div>
            <div class="card-body">
                <?php if (empty($usuarios_recientes)): ?>
                    <p class="empty">No hay usuarios registrados</p>
                <?php else: ?>
                    <ul class="user-list">
                        <?php foreach ($usuarios_recientes as $u): ?>
                            <li>
                                <span class="user-name"><?php echo e($u['nombre_completo']); ?></span>
                                <span class="user-email"><?php echo e($u['email']); ?></span>
                                <span class="user-role"><?php echo getRoleName($u['id_rol']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Préstamos recientes -->
        <div class="admin-card">
            <div class="card-header">
                <h3>📋 Préstamos Recientes</h3>
                <a href="<?php echo SITE_URL; ?>modules/prestamos/index.php" class="btn-link">Ver todos →</a>
            </div>
            <div class="card-body">
                <?php if (empty($prestamos_recientes)): ?>
                    <p class="empty">No hay préstamos registrados</p>
                <?php else: ?>
                    <ul class="loan-list">
                        <?php foreach ($prestamos_recientes as $p): ?>
                            <li>
                                <span class="loan-user"><?php echo e($p['nombre_completo']); ?></span>
                                <span class="loan-resource">"<?php echo e($p['titulo']); ?>"</span>
                                <span class="loan-date"><?php echo formatDate($p['fecha_prestamo']); ?></span>
                                <span class="loan-status status-<?php echo $p['estado']; ?>">
                                    <?php 
                                    $statusLabels = [
                                        'activo' => '🟢 Activo',
                                        'devuelto' => '✅ Devuelto',
                                        'vencido' => '🔴 Vencido',
                                        'cancelado' => '⛔ Cancelado'
                                    ];
                                    echo $statusLabels[$p['estado']] ?? $p['estado'];
                                    ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas de administración -->
    <div class="admin-actions">
        <h3>⚡ Acciones Rápidas</h3>
        <div class="actions-grid">
            <a href="<?php echo SITE_URL; ?>modules/resources/create.php" class="action-item">
                <span class="action-icon">📖</span>
                <span>Agregar Recurso</span>
            </a>
            <a href="<?php echo SITE_URL; ?>modules/prestamos/new.php" class="action-item">
                <span class="action-icon">📋</span>
                <span>Nuevo Préstamo</span>
            </a>
            <a href="<?php echo SITE_URL; ?>modules/admin/usuarios.php" class="action-item">
                <span class="action-icon">👥</span>
                <span>Gestionar Usuarios</span>
            </a>
            <a href="<?php echo SITE_URL; ?>modules/resources/index.php" class="action-item">
                <span class="action-icon">🔍</span>
                <span>Ver Recursos</span>
            </a>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    padding: 1rem 0;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.dashboard-header h1 {
    font-size: 1.8rem;
    color: #2d3436;
    margin-bottom: 0.25rem;
}

.dashboard-header p {
    color: #636e72;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

.admin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin: 2rem 0;
}

.admin-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 1rem;
    color: #2d3436;
}

.btn-link {
    color: #6C5CE7;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
}

.btn-link:hover {
    text-decoration: underline;
}

.card-body {
    padding: 1rem 1.5rem;
    max-height: 300px;
    overflow-y: auto;
}

.user-list, .loan-list {
    list-style: none;
    padding: 0;
}

.user-list li, .loan-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f5f5f5;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.user-list li:last-child, .loan-list li:last-child {
    border-bottom: none;
}

.user-name {
    font-weight: 500;
    color: #2d3436;
}

.user-email {
    color: #636e72;
    font-size: 0.85rem;
}

.user-role {
    background: #f0f0f0;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    color: #636e72;
    margin-left: auto;
}

.loan-user {
    font-weight: 500;
    color: #2d3436;
}

.loan-resource {
    color: #636e72;
    font-size: 0.9rem;
}

.loan-date {
    font-size: 0.8rem;
    color: #b2bec3;
}

.loan-status {
    font-size: 0.8rem;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    margin-left: auto;
}

.status-activo {
    background: #d4edda;
    color: #155724;
}

.status-devuelto {
    background: #cce5ff;
    color: #004085;
}

.status-vencido {
    background: #f8d7da;
    color: #721c24;
}

.status-cancelado {
    background: #e2e3e5;
    color: #383d41;
}

.empty {
    color: #b2bec3;
    text-align: center;
    padding: 1rem 0;
}

.admin-actions {
    margin-top: 2rem;
}

.admin-actions h3 {
    margin-bottom: 1rem;
    color: #2d3436;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

@media (max-width: 768px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>