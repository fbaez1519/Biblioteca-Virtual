<?php
// ============================================
// GESTIÓN DE USUARIOS - ADMIN
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$db = getDB();
$user = $auth->getUser();

// Procesar acciones
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Activar/Desactivar usuario
if ($action === 'toggle' && $id > 0 && $id != $user['id_usuario']) {
    $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    setAlert('Estado del usuario actualizado correctamente', 'success');
    header('Location: ' . SITE_URL . 'modules/admin/usuarios.php');
    exit;
}

// Cambiar rol
if ($action === 'role' && $id > 0 && $id != $user['id_usuario']) {
    $rol = isset($_GET['rol']) ? (int)$_GET['rol'] : 2;
    $stmt = $db->prepare("UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?");
    $stmt->bind_param("ii", $rol, $id);
    $stmt->execute();
    setAlert('Rol del usuario actualizado correctamente', 'success');
    header('Location: ' . SITE_URL . 'modules/admin/usuarios.php');
    exit;
}

// Eliminar usuario
if ($action === 'delete' && $id > 0 && $id != $user['id_usuario']) {
    $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    setAlert('Usuario eliminado correctamente', 'success');
    header('Location: ' . SITE_URL . 'modules/admin/usuarios.php');
    exit;
}

// Obtener usuarios
$usuarios = $db->query("
    SELECT u.*, r.nombre_rol 
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id_rol
    ORDER BY u.fecha_registro DESC
")->fetch_all(MYSQLI_ASSOC);

// Obtener roles para el select
$roles = $db->query("SELECT * FROM roles ORDER BY id_rol")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Gestión de Usuarios';
include '../../includes/header.php';
?>

<div class="admin-section">
    <div class="section-header">
        <div>
            <h1>👥 Gestión de Usuarios</h1>
            <p>Administra los usuarios del sistema</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo SITE_URL; ?>modules/auth/register.php" class="btn btn-primary" target="_blank">
                ➕ Nuevo Usuario
            </a>
            <a href="<?php echo SITE_URL; ?>modules/admin/dashboard.php" class="btn btn-outline">
                ← Volver
            </a>
        </div>
    </div>

    <?php 
    $alert = getAlert();
    if ($alert): 
    ?>
        <div class="alert alert-<?php echo $alert['type']; ?>">
            <?php echo e($alert['message']); ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <?php if (empty($usuarios)): ?>
            <div class="empty-state">
                <p>No hay usuarios registrados</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>#<?php echo $u['id_usuario']; ?></td>
                            <td>
                                <strong><?php echo e($u['nombre_usuario']); ?></strong>
                                <?php if ($u['id_usuario'] == $user['id_usuario']): ?>
                                    <span class="badge badge-primary">Tú</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($u['nombre_completo']); ?></td>
                            <td><?php echo e($u['email']); ?></td>
                            <td>
                                <?php if ($u['id_usuario'] == $user['id_usuario']): ?>
                                    <span class="badge badge-primary"><?php echo e($u['nombre_rol']); ?></span>
                                <?php else: ?>
                                    <form method="GET" action="" style="display: inline-block;">
                                        <input type="hidden" name="action" value="role">
                                        <input type="hidden" name="id" value="<?php echo $u['id_usuario']; ?>">
                                        <select name="rol" onchange="this.form.submit()" class="role-select">
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?php echo $r['id_rol']; ?>" 
                                                    <?php echo $u['id_rol'] == $r['id_rol'] ? 'selected' : ''; ?>>
                                                    <?php echo e($r['nombre_rol']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['activo']): ?>
                                    <span class="status-active">✅ Activo</span>
                                <?php else: ?>
                                    <span class="status-inactive">⛔ Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($u['fecha_registro'], 'd/m/Y'); ?></td>
                            <td>
                                <?php if ($u['id_usuario'] != $user['id_usuario']): ?>
                                    <a href="?action=toggle&id=<?php echo $u['id_usuario']; ?>" 
                                       class="btn-action toggle"
                                       onclick="return confirm('¿Cambiar estado del usuario?')">
                                        <?php echo $u['activo'] ? '🔴' : '🟢'; ?>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $u['id_usuario']; ?>" 
                                       class="btn-action delete"
                                       onclick="return confirm('¿Eliminar usuario <?php echo e($u['nombre_completo']); ?>?')">
                                        🗑️
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-section {
    padding: 1rem 0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-header h1 {
    font-size: 1.8rem;
    color: #2d3436;
    margin-bottom: 0.25rem;
}

.section-header p {
    color: #636e72;
}

.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.admin-table thead {
    background: #f8f9fa;
}

.admin-table th {
    padding: 1rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #2d3436;
    border-bottom: 2px solid #f0f0f0;
}

.admin-table td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #f5f5f5;
}

.admin-table tr:hover {
    background: #fafafa;
}

.admin-table tbody tr:last-child td {
    border-bottom: none;
}

.badge {
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-primary {
    background: #6C5CE7;
    color: white;
}

.status-active {
    color: #00B894;
    font-weight: 500;
}

.status-inactive {
    color: #E17055;
    font-weight: 500;
}

.role-select {
    padding: 0.3rem 0.5rem;
    border: 1px solid #dfe6e9;
    border-radius: 6px;
    font-size: 0.85rem;
    background: white;
    cursor: pointer;
}

.role-select:focus {
    outline: none;
    border-color: #6C5CE7;
}

.btn-action {
    text-decoration: none;
    font-size: 1.1rem;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    transition: all 0.3s;
    display: inline-block;
}

.btn-action:hover {
    transform: scale(1.2);
}

.btn-action.delete:hover {
    background: #f8d7da;
}

.btn-action.toggle:hover {
    background: #d4edda;
}

.text-muted {
    color: #b2bec3;
}

.empty-state {
    padding: 3rem;
    text-align: center;
    color: #b2bec3;
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-table {
        font-size: 0.8rem;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 0.5rem;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>