<?php
// ============================================
// PÁGINA PRINCIPAL - BIBLIOTECA VIRTUAL
// ============================================

require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Verificar autenticación
if (!$auth->isAuthenticated()) {
    header('Location: ' . SITE_URL . 'modules/auth/login.php');
    exit;
}

// Obtener datos del usuario
$user = $auth->getUser();
$userId = $auth->getUserId();

// Estadísticas para el dashboard
$db = getDB();

// Total de recursos
$stmt = $db->query("SELECT COUNT(*) as total FROM recursos WHERE activo = 1");
$totalRecursos = $stmt->fetch_assoc()['total'];

// Total de préstamos activos
$stmt = $db->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'activo'");
$totalPrestamosActivos = $stmt->fetch_assoc()['total'];

// Préstamos del usuario actual
$stmt = $db->prepare("SELECT COUNT(*) as total FROM prestamos WHERE id_usuario = ? AND estado = 'activo'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$misPrestamos = $stmt->get_result()->fetch_assoc()['total'];

// Últimos recursos agregados
$stmt = $db->query("
    SELECT r.*, c.nombre_categoria 
    FROM recursos r 
    LEFT JOIN categorias c ON r.id_categoria = c.id_categoria 
    WHERE r.activo = 1 
    ORDER BY r.fecha_creacion DESC 
    LIMIT 6
");
$ultimosRecursos = $stmt->fetch_all(MYSQLI_ASSOC);

// Préstamos activos del usuario
$stmt = $db->prepare("
    SELECT p.*, r.titulo, r.autor, r.tipo_recurso
    FROM prestamos p
    JOIN recursos r ON p.id_recurso = r.id_recurso
    WHERE p.id_usuario = ? AND p.estado = 'activo'
    ORDER BY p.fecha_devolucion_esperada ASC
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$misPrestamosActivos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Inicio';

// Función de escape (por si no está definida)
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- ========================================== -->
    <!-- BIENVENIDA MEJORADA -->
    <!-- ========================================== -->
    <div class="welcome-section">
        <div class="welcome-content">
            <div class="welcome-text">
                <span class="welcome-emoji">👋</span>
                <h1>¡Bienvenido, <span class="highlight"><?php echo e($user['nombre_completo']); ?></span>!</h1>
                <p>📚 Biblioteca Virtual - Tu portal de conocimiento</p>
            </div>
            <div class="welcome-stats-mini">
                <div class="mini-stat">
                    <span class="mini-number"><?php echo $totalRecursos; ?></span>
                    <span class="mini-label">Recursos</span>
                </div>
                <div class="mini-divider"></div>
                <div class="mini-stat">
                    <span class="mini-number"><?php echo $totalPrestamosActivos; ?></span>
                    <span class="mini-label">Préstamos</span>
                </div>
                <div class="mini-divider"></div>
                <div class="mini-stat">
                    <span class="mini-number"><?php echo $misPrestamos; ?></span>
                    <span class="mini-label">Mis préstamos</span>
                </div>
            </div>
        </div>
        <div class="welcome-date">
            <span class="date-icon">📅</span>
            <span><?php echo date('l, d \d\e F \d\e Y'); ?></span>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TARJETAS DE ESTADÍSTICAS INTERACTIVAS -->
    <!-- ========================================== -->
    <div class="stats-grid">
        <div class="stat-card" data-color="purple">
            <div class="stat-icon-wrapper">
                <span class="stat-icon">📖</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-number" data-count="<?php echo $totalRecursos; ?>">0</h3>
                <p>Recursos Disponibles</p>
                <span class="stat-trend">↑ +12% este mes</span>
            </div>
        </div>
        
        <div class="stat-card" data-color="blue">
            <div class="stat-icon-wrapper">
                <span class="stat-icon">📚</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-number" data-count="<?php echo $totalPrestamosActivos; ?>">0</h3>
                <p>Préstamos Activos</p>
                <span class="stat-trend">↑ +5% este mes</span>
            </div>
        </div>
        
        <div class="stat-card" data-color="green">
            <div class="stat-icon-wrapper">
                <span class="stat-icon">📋</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-number" data-count="<?php echo $misPrestamos; ?>">0</h3>
                <p>Mis Préstamos</p>
                <span class="stat-trend"><?php echo $misPrestamos > 0 ? '📌 Activos' : '📭 Sin préstamos'; ?></span>
            </div>
        </div>
        
        <div class="stat-card" data-color="orange">
            <div class="stat-icon-wrapper">
                <span class="stat-icon">👤</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo e($user['nombre_usuario']); ?></h3>
                <p>Mi Perfil</p>
                <span class="stat-trend">👑 <?php echo e($user['nombre_rol'] ?? 'Usuario'); ?></span>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- CONTENIDO PRINCIPAL -->
    <!-- ========================================== -->
    <div class="dashboard-grid">
        <!-- Mis préstamos activos -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-header-left">
                    <span class="card-icon">📋</span>
                    <h3>Mis Préstamos Activos</h3>
                    <span class="card-badge"><?php echo count($misPrestamosActivos); ?></span>
                </div>
                <a href="<?php echo SITE_URL; ?>modules/prestamos/index.php" class="btn-link">Ver todos →</a>
            </div>
            <div class="card-body">
                <?php if (empty($misPrestamosActivos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>No tienes préstamos activos</p>
                        <a href="<?php echo SITE_URL; ?>modules/resources/index.php" class="btn btn-primary btn-sm">
                            Explorar recursos
                        </a>
                    </div>
                <?php else: ?>
                    <ul class="loan-list">
                        <?php foreach ($misPrestamosActivos as $prestamo): ?>
                            <li>
                                <div class="loan-item">
                                    <span class="loan-icon">
                                        <?php 
                                        $icon = '📖';
                                        if ($prestamo['tipo_recurso'] == 'audio') $icon = '🎵';
                                        if ($prestamo['tipo_recurso'] == 'articulo') $icon = '📄';
                                        echo $icon;
                                        ?>
                                    </span>
                                    <div class="loan-info">
                                        <span class="loan-title"><?php echo e($prestamo['titulo']); ?></span>
                                        <span class="loan-author"><?php echo e($prestamo['autor'] ?? 'Autor desconocido'); ?></span>
                                    </div>
                                    <div class="loan-status-badge">
                                        <?php 
                                        $diasRestantes = (strtotime($prestamo['fecha_devolucion_esperada']) - time()) / 86400;
                                        if ($diasRestantes < 0): 
                                        ?>
                                            <span class="status overdue">🔴 Vencido</span>
                                        <?php elseif ($diasRestantes <= 3): ?>
                                            <span class="status warning">🟡 <?php echo round($diasRestantes); ?> días</span>
                                        <?php else: ?>
                                            <span class="status ok">🟢 <?php echo round($diasRestantes); ?> días</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="loan-date">
                                        <small>Devolver: <?php echo formatDate($prestamo['fecha_devolucion_esperada'], 'd/m/Y'); ?></small>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimos recursos -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-header-left">
                    <span class="card-icon">📚</span>
                    <h3>Últimos Recursos</h3>
                    <span class="card-badge"><?php echo count($ultimosRecursos); ?></span>
                </div>
                <a href="<?php echo SITE_URL; ?>modules/resources/index.php" class="btn-link">Ver todos →</a>
            </div>
            <div class="card-body">
                <?php if (empty($ultimosRecursos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>No hay recursos disponibles</p>
                        <?php if ($auth->isBibliotecario() || $auth->isAdmin()): ?>
                            <a href="<?php echo SITE_URL; ?>modules/resources/create.php" class="btn btn-primary btn-sm">
                                Agregar recurso
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <ul class="resource-list">
                        <?php foreach ($ultimosRecursos as $recurso): ?>
                            <li>
                                <div class="resource-item">
                                    <span class="resource-icon">
                                        <?php 
                                        $icon = '📖';
                                        if ($recurso['tipo_recurso'] == 'audio') $icon = '🎵';
                                        if ($recurso['tipo_recurso'] == 'articulo') $icon = '📄';
                                        echo $icon;
                                        ?>
                                    </span>
                                    <div class="resource-info">
                                        <span class="resource-title"><?php echo e($recurso['titulo']); ?></span>
                                        <span class="resource-meta">
                                            <?php echo e($recurso['autor'] ?? 'Autor desconocido'); ?>
                                            • <?php echo e($recurso['nombre_categoria'] ?? 'Sin categoría'); ?>
                                        </span>
                                    </div>
                                    <div class="resource-status">
                                        <?php if ($recurso['cantidad_disponible'] > 0): ?>
                                            <span class="status available">✅ Disponible</span>
                                        <?php else: ?>
                                            <span class="status unavailable">❌ Agotado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ACCIONES RÁPIDAS MEJORADAS -->
    <!-- ========================================== -->
    <div class="quick-actions">
        <div class="quick-actions-header">
            <h3>⚡ Acciones Rápidas</h3>
            <span class="quick-subtitle">Lo que necesitas al alcance de un clic</span>
        </div>
        <div class="actions-grid">
            <a href="<?php echo SITE_URL; ?>modules/resources/index.php" class="action-item" style="--action-color: #667eea;">
                <span class="action-icon">🔍</span>
                <span class="action-label">Buscar Recursos</span>
                <span class="action-desc">Encuentra lo que necesitas</span>
            </a>
            <?php if ($auth->isBibliotecario() || $auth->isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>modules/resources/create.php" class="action-item" style="--action-color: #43e97b;">
                    <span class="action-icon">➕</span>
                    <span class="action-label">Agregar Recurso</span>
                    <span class="action-desc">Añade al catálogo</span>
                </a>
                <a href="<?php echo SITE_URL; ?>modules/prestamos/new.php" class="action-item" style="--action-color: #4facfe;">
                    <span class="action-icon">📋</span>
                    <span class="action-label">Nuevo Préstamo</span>
                    <span class="action-desc">Registra un préstamo</span>
                </a>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>modules/profile/index.php" class="action-item" style="--action-color: #f093fb;">
                <span class="action-icon">👤</span>
                <span class="action-label">Mi Perfil</span>
                <span class="action-desc">Gestiona tu cuenta</span>
            </a>
            <?php if ($auth->isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>modules/admin/dashboard.php" class="action-item" style="--action-color: #fa709a;">
                    <span class="action-icon">👑</span>
                    <span class="action-label">Administración</span>
                    <span class="action-desc">Panel de control</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS DEL DASHBOARD MEJORADO
   ============================================ */

.dashboard-container {
    padding: 1rem 0;
}

/* ========================================== */
/* BIENVENIDA MEJORADA */
/* ========================================== */

.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 2.5rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
}

.welcome-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.welcome-section::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -10%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.welcome-content {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.welcome-text .welcome-emoji {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.2rem;
}

.welcome-text h1 {
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 0.2rem;
}

.welcome-text .highlight {
    background: rgba(255,255,255,0.2);
    padding: 0.1rem 0.6rem;
    border-radius: 8px;
}

.welcome-text p {
    opacity: 0.9;
    font-size: 1rem;
}

.welcome-stats-mini {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    background: rgba(255,255,255,0.12);
    padding: 0.8rem 1.5rem;
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

.mini-stat {
    text-align: center;
}

.mini-number {
    display: block;
    font-size: 1.6rem;
    font-weight: 800;
}

.mini-label {
    font-size: 0.7rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mini-divider {
    width: 1px;
    height: 30px;
    background: rgba(255,255,255,0.2);
}

.welcome-date {
    background: rgba(255,255,255,0.15);
    padding: 0.6rem 1.5rem;
    border-radius: 50px;
    font-size: 0.95rem;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255,255,255,0.1);
}

.date-icon {
    font-size: 1.2rem;
}

/* ========================================== */
/* STATS CARDS MEJORADAS */
/* ========================================== */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 1.2rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    border-left: 4px solid #667eea;
    position: relative;
    overflow: hidden;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle, rgba(102,126,234,0.03) 0%, transparent 70%);
    border-radius: 50%;
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.08);
}

.stat-card[data-color="purple"] { border-left-color: #667eea; }
.stat-card[data-color="blue"] { border-left-color: #4facfe; }
.stat-card[data-color="green"] { border-left-color: #43e97b; }
.stat-card[data-color="orange"] { border-left-color: #fa709a; }

.stat-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    flex-shrink: 0;
}

.stat-card[data-color="purple"] .stat-icon-wrapper {
    background: rgba(102,126,234,0.12);
}
.stat-card[data-color="blue"] .stat-icon-wrapper {
    background: rgba(79,172,254,0.12);
}
.stat-card[data-color="green"] .stat-icon-wrapper {
    background: rgba(67,233,123,0.12);
}
.stat-card[data-color="orange"] .stat-icon-wrapper {
    background: rgba(250,112,154,0.12);
}

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #2d3436;
    line-height: 1;
    margin-bottom: 0.1rem;
}

.stat-info p {
    color: #636e72;
    font-size: 0.85rem;
    margin: 0;
    font-weight: 500;
}

.stat-trend {
    font-size: 0.7rem;
    color: #b2bec3;
    display: block;
    margin-top: 0.2rem;
}

/* ========================================== */
/* DASHBOARD CARDS */
/* ========================================== */

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    overflow: hidden;
    transition: box-shadow 0.3s;
}

.dashboard-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.card-icon {
    font-size: 1.2rem;
}

.card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3436;
    margin: 0;
}

.card-badge {
    background: #f0f0f0;
    padding: 0.1rem 0.6rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #636e72;
}

.btn-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: color 0.3s;
}

.btn-link:hover {
    color: #764ba2;
}

.card-body {
    padding: 0.5rem 1.5rem 1.5rem;
    max-height: 350px;
    overflow-y: auto;
}

/* Scroll personalizado */
.card-body::-webkit-scrollbar {
    width: 4px;
}
.card-body::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}
.card-body::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

/* ========================================== */
/* LISTAS */
/* ========================================== */

.loan-list,
.resource-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.loan-list li,
.resource-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.2s;
}

.loan-list li:hover,
.resource-list li:hover {
    background: #fafafa;
    margin: 0 -0.5rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    border-radius: 8px;
}

.loan-list li:last-child,
.resource-list li:last-child {
    border-bottom: none;
}

.loan-item,
.resource-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
}

.loan-icon,
.resource-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.loan-info,
.resource-info {
    flex: 1;
    min-width: 120px;
}

.loan-title,
.resource-title {
    display: block;
    font-weight: 600;
    color: #2d3436;
    font-size: 0.95rem;
}

.loan-author,
.resource-meta {
    font-size: 0.8rem;
    color: #636e72;
}

.loan-status-badge {
    flex-shrink: 0;
}

.loan-date {
    flex-shrink: 0;
}

.loan-date small {
    font-size: 0.7rem;
    color: #b2bec3;
}

.resource-status {
    flex-shrink: 0;
}

/* ========================================== */
/* ESTADOS */
/* ========================================== */

.status {
    font-size: 0.7rem;
    padding: 0.15rem 0.6rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-block;
}

.status.overdue {
    background: #fde8e8;
    color: #c0392b;
}

.status.warning {
    background: #fff8e1;
    color: #856404;
}

.status.ok {
    background: #e8f8ed;
    color: #155724;
}

.status.available {
    background: #e8f8ed;
    color: #155724;
}

.status.unavailable {
    background: #fde8e8;
    color: #c0392b;
}

/* ========================================== */
/* EMPTY STATE */
/* ========================================== */

.empty-state {
    text-align: center;
    padding: 2rem 0;
    color: #b2bec3;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    margin-bottom: 0.8rem;
}

.btn-sm {
    padding: 0.4rem 1.2rem;
    font-size: 0.85rem;
    border-radius: 10px;
}

/* ========================================== */
/* ACCIONES RÁPIDAS MEJORADAS */
/* ========================================== */

.quick-actions {
    margin-top: 1.5rem;
}

.quick-actions-header {
    display: flex;
    align-items: baseline;
    gap: 0.8rem;
    margin-bottom: 1.2rem;
    flex-wrap: wrap;
}

.quick-actions-header h3 {
    font-size: 1.2rem;
    color: #2d3436;
    margin: 0;
}

.quick-subtitle {
    font-size: 0.85rem;
    color: #b2bec3;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}

.action-item {
    background: white;
    padding: 1.2rem 1rem;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    text-decoration: none;
    color: #2d3436;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.3s ease;
    text-align: center;
    border: 2px solid transparent;
    position: relative;
}

.action-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.08);
    border-color: var(--action-color, #667eea);
}

.action-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 3px;
    background: var(--action-color, #667eea);
    border-radius: 0 0 4px 4px;
    opacity: 0;
    transition: opacity 0.3s;
}

.action-item:hover::before {
    opacity: 1;
}

.action-icon {
    font-size: 2rem;
}

.action-label {
    font-weight: 600;
    font-size: 0.9rem;
}

.action-desc {
    font-size: 0.7rem;
    color: #b2bec3;
}

/* ========================================== */
/* RESPONSIVE */
/* ========================================== */

@media (max-width: 992px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .welcome-section {
        padding: 1.5rem;
        flex-direction: column;
        text-align: center;
    }
    
    .welcome-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .welcome-text h1 {
        font-size: 1.4rem;
    }
    
    .welcome-stats-mini {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .stat-icon-wrapper {
        width: 44px;
        height: 44px;
        font-size: 1.4rem;
    }
    
    .actions-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .loan-item,
    .resource-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
    }
    
    .loan-date {
        width: 100%;
        text-align: left;
    }
    
    .quick-actions-header {
        flex-direction: column;
        gap: 0.2rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .welcome-stats-mini {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .mini-divider {
        width: 80%;
        height: 1px;
    }
}
</style>

<script>
// ==========================================
// ANIMACIÓN DE CONTADORES
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number[data-count]');
    
    statNumbers.forEach(element => {
        const target = parseInt(element.getAttribute('data-count'));
        if (target === 0) {
            element.textContent = '0';
            return;
        }
        
        let current = 0;
        const increment = Math.ceil(target / 30);
        const duration = 800;
        const stepTime = duration / 30;
        
        const counter = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(counter);
            }
            element.textContent = current;
        }, stepTime);
    });
});

// ==========================================
// EFECTO DE PARPADEO EN ESTADOS DE PRÉSTAMOS
// ==========================================
document.querySelectorAll('.status.overdue').forEach(el => {
    setInterval(() => {
        el.style.opacity = el.style.opacity === '0.5' ? '1' : '0.5';
    }, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>