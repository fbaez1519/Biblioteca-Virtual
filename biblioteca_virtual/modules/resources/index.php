<?php
// ============================================
// LISTADO DE RECURSOS - CON CRUD COMPLETO
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$db = getDB();
$user = $auth->getUser();

// Filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Construir consulta
$where = ['r.activo = 1'];
$params = [];
$types = '';

if ($search) {
    $where[] = '(r.titulo LIKE ? OR r.autor LIKE ? OR r.descripcion LIKE ?)';
    $searchParam = '%' . $search . '%';
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= 'sss';
}

if ($categoria > 0) {
    $where[] = 'r.id_categoria = ?';
    $params[] = $categoria;
    $types .= 'i';
}

if ($tipo) {
    $where[] = 'r.tipo_recurso = ?';
    $params[] = $tipo;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT r.*, c.nombre_categoria 
    FROM recursos r
    LEFT JOIN categorias c ON r.id_categoria = c.id_categoria
    $whereClause
    ORDER BY r.fecha_creacion DESC
";

$stmt = $db->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$recursos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener categorías para el filtro
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetch_all(MYSQLI_ASSOC);

// Obtener tipos para el filtro
$tipos = [
    'libro' => '📖 Libros',
    'audio' => '🎵 Audio Libros',
    'articulo' => '📄 Artículos'
];

$pageTitle = 'Recursos';
include '../../includes/header.php';
?>

<div class="resources-page">
    <div class="page-header">
        <div>
            <h1>📚 Recursos Disponibles</h1>
            <p>Explora nuestro catálogo de recursos</p>
        </div>
        <?php if ($auth->isBibliotecario() || $auth->isAdmin()): ?>
            <div class="header-actions">
                <a href="create.php" class="btn btn-primary">
                    ➕ Agregar Recurso
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="🔍 Buscar por título, autor..." 
                       value="<?php echo e($search); ?>" class="filter-input">
            </div>
            
            <div class="filter-group">
                <select name="categoria" class="filter-select">
                    <option value="0">📂 Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id_categoria']; ?>" 
                            <?php echo $categoria == $cat['id_categoria'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['nombre_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="tipo" class="filter-select">
                    <option value="">📋 Todos los tipos</option>
                    <?php foreach ($tipos as $key => $label): ?>
                        <option value="<?php echo $key; ?>" 
                            <?php echo $tipo == $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
            <a href="index.php" class="btn btn-outline">🔄 Limpiar</a>
        </form>
    </div>

    <?php if (empty($recursos)): ?>
        <div class="empty-state">
            <p>📭 No se encontraron recursos</p>
            <?php if ($auth->isBibliotecario() || $auth->isAdmin()): ?>
                <a href="create.php" class="btn btn-primary">Agregar primer recurso</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="resources-grid">
            <?php foreach ($recursos as $recurso): ?>
                <div class="resource-card">
                    <div class="resource-header">
                        <div class="resource-type-wrapper">
                            <span class="resource-type <?php echo $recurso['tipo_recurso']; ?>">
                                <?php 
                                $icon = '📖';
                                if ($recurso['tipo_recurso'] == 'audio') $icon = '🎵';
                                if ($recurso['tipo_recurso'] == 'articulo') $icon = '📄';
                                echo $icon;
                                ?>
                            </span>
                            <span class="resource-id">#<?php echo $recurso['id_recurso']; ?></span>
                        </div>
                        <?php if ($recurso['cantidad_disponible'] > 0): ?>
                            <span class="status available">✅ Disponible</span>
                        <?php else: ?>
                            <span class="status unavailable">❌ Agotado</span>
                        <?php endif; ?>
                    </div>
                    
                    <h3><?php echo e($recurso['titulo']); ?></h3>
                    <p class="author">✍️ <?php echo e($recurso['autor'] ?? 'Autor desconocido'); ?></p>
                    
                    <div class="resource-meta">
                        <span class="category">📂 <?php echo e($recurso['nombre_categoria'] ?? 'Sin categoría'); ?></span>
                        <span class="copies">📋 <?php echo $recurso['cantidad_disponible']; ?>/<?php echo $recurso['cantidad_total']; ?></span>
                    </div>
                    
                    <?php if ($recurso['descripcion']): ?>
                        <p class="description"><?php echo e(truncateText($recurso['descripcion'], 80)); ?></p>
                    <?php endif; ?>
                    
                    <!-- ========================================== -->
                    <!-- BOTONES CRUD COMPLETOS -->
                    <!-- ========================================== -->
                    <div class="resource-actions">
                        <!-- Botón Ver -->
                        <a href="#" class="btn-action view" 
                           onclick="verRecurso(<?php echo $recurso['id_recurso']; ?>); return false;" 
                           title="Ver detalles">
                            👁️
                        </a>
                        
                        <!-- Botón Editar -->
                        <?php if ($auth->isBibliotecario() || $auth->isAdmin()): ?>
                            <a href="edit.php?id=<?php echo $recurso['id_recurso']; ?>" 
                               class="btn-action edit" title="Editar recurso">
                                ✏️
                            </a>
                        <?php endif; ?>
                        
                        <!-- Botón Prestar -->
                        <?php if ($recurso['cantidad_disponible'] > 0): ?>
                            <a href="<?php echo SITE_URL; ?>modules/prestamos/new.php?recurso=<?php echo $recurso['id_recurso']; ?>" 
                               class="btn-action loan" title="Realizar préstamo">
                                📋
                            </a>
                        <?php else: ?>
                            <span class="btn-action disabled" title="No disponible">🚫</span>
                        <?php endif; ?>
                        
                        <!-- Botón Eliminar -->
                        <?php if ($auth->isAdmin()): ?>
                            <a href="delete.php?id=<?php echo $recurso['id_recurso']; ?>" 
                               class="btn-action delete" 
                               title="Eliminar recurso"
                               onclick="return confirm('⚠️ ¿Estás seguro de eliminar el recurso \"<?php echo e($recurso['titulo']); ?>\"? Esta acción no se puede deshacer.');">
                                🗑️
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para ver detalles -->
<div id="recursoModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitulo">Detalles del Recurso</h2>
            <span class="modal-close" onclick="cerrarModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Contenido cargado vía AJAX -->
            <p>Cargando...</p>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ADICIONALES PARA CRUD
   ============================================ */

.resources-page {
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
    margin-bottom: 0.25rem;
}

.page-header p {
    color: #636e72;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

/* Filtros */
.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 0.6rem 1rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 0.9rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.3s;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #6C5CE7;
}

/* Grid de recursos */
.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.resource-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.resource-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.resource-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.resource-type-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.resource-type {
    font-size: 2rem;
}

.resource-id {
    font-size: 0.7rem;
    color: #b2bec3;
    background: #f0f0f0;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
}

.status {
    font-size: 0.8rem;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-weight: 500;
}

.status.available {
    background: #d4edda;
    color: #155724;
}

.status.unavailable {
    background: #f8d7da;
    color: #721c24;
}

.resource-card h3 {
    font-size: 1.1rem;
    color: #2d3436;
    margin-bottom: 0.25rem;
}

.resource-card .author {
    color: #636e72;
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.resource-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #636e72;
    margin-bottom: 0.5rem;
}

.category {
    background: #f0f0f0;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
}

.copies {
    font-weight: 500;
}

.description {
    color: #636e72;
    font-size: 0.85rem;
    margin: 0.5rem 0;
    line-height: 1.4;
}

/* ========================================== */
/* BOTONES DE ACCIONES CRUD */
/* ========================================== */
.resource-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    background: #f8f9fa;
    color: #2d3436;
}

.btn-action:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-action.view:hover {
    background: #cce5ff;
    color: #004085;
}

.btn-action.edit:hover {
    background: #fff3cd;
    color: #856404;
}

.btn-action.loan:hover {
    background: #d4edda;
    color: #155724;
}

.btn-action.delete:hover {
    background: #f8d7da;
    color: #721c24;
}

.btn-action.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-action.disabled:hover {
    transform: none;
    box-shadow: none;
}

/* ========================================== */
/* MODAL PARA VER DETALLES */
/* ========================================== */
.modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    width: 90%;
    max-width: 600px;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: slideDown 0.3s;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.3rem;
    color: #2d3436;
}

.modal-close {
    font-size: 1.8rem;
    cursor: pointer;
    color: #b2bec3;
    transition: color 0.3s;
}

.modal-close:hover {
    color: #2d3436;
}

.modal-body {
    padding: 1.5rem;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-body .detail-row {
    display: flex;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f5f5f5;
}

.modal-body .detail-label {
    font-weight: 500;
    color: #636e72;
    width: 120px;
    flex-shrink: 0;
}

.modal-body .detail-value {
    color: #2d3436;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    color: #b2bec3;
}

.empty-state .btn {
    margin-top: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .resources-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
    }
}

/* ========================================== */
/* TOOLTIPS */
/* ========================================== */
.btn-action[title] {
    position: relative;
}

.btn-action[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: calc(100% + 5px);
    left: 50%;
    transform: translateX(-50%);
    background: #2d3436;
    color: white;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    font-size: 0.7rem;
    white-space: nowrap;
    z-index: 10;
}
</style>

<script>
// ============================================
// FUNCIONES PARA EL CRUD
// ============================================

// Ver recurso (modal)
function verRecurso(id) {
    const modal = document.getElementById('recursoModal');
    const body = document.getElementById('modalBody');
    
    modal.style.display = 'block';
    body.innerHTML = '<p>Cargando...</p>';
    
    // Obtener datos del recurso vía AJAX
    fetch('get_recurso.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                body.innerHTML = '<p style="color:red;">❌ ' + data.error + '</p>';
                return;
            }
            
            let html = '';
            html += '<div class="detail-row"><span class="detail-label">ID</span><span class="detail-value">#' + data.id_recurso + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Título</span><span class="detail-value"><strong>' + data.titulo + '</strong></span></div>';
            html += '<div class="detail-row"><span class="detail-label">Autor</span><span class="detail-value">' + (data.autor || 'N/A') + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Tipo</span><span class="detail-value">' + data.tipo_recurso + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Categoría</span><span class="detail-value">' + (data.categoria || 'Sin categoría') + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">ISBN</span><span class="detail-value">' + (data.isbn || 'N/A') + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Editorial</span><span class="detail-value">' + (data.editorial || 'N/A') + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Año</span><span class="detail-value">' + (data.anio_publicacion || 'N/A') + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Cantidad</span><span class="detail-value">' + data.cantidad_disponible + ' / ' + data.cantidad_total + '</span></div>';
            html += '<div class="detail-row"><span class="detail-label">Ubicación</span><span class="detail-value">' + (data.ubicacion || 'N/A') + '</span></div>';
            
            if (data.descripcion) {
                html += '<div class="detail-row" style="flex-direction:column; align-items:flex-start;">';
                html += '<span class="detail-label" style="width:100%;">Descripción</span>';
                html += '<span class="detail-value" style="width:100%;">' + data.descripcion + '</span>';
                html += '</div>';
            }
            
            body.innerHTML = html;
        })
        .catch(error => {
            body.innerHTML = '<p style="color:red;">❌ Error al cargar los datos</p>';
            console.error('Error:', error);
        });
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('recursoModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('recursoModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Confirmar eliminación (usando SweetAlert o confirm nativo)
function confirmarEliminar(url, titulo) {
    if (confirm('⚠️ ¿Estás seguro de eliminar el recurso "' + titulo + '"? Esta acción no se puede deshacer.')) {
        window.location.href = url;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>