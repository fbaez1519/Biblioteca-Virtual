<?php
// ============================================
// PERFIL DE USUARIO
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$db = getDB();
$user = $auth->getUser();
$userId = $auth->getUserId();

$error = '';
$success = '';
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'perfil';

// ============================================
// ACTUALIZAR PERFIL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Actualizar datos personales
    if ($action === 'update_profile') {
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        if (empty($nombre_completo)) {
            $error = 'El nombre completo es requerido';
        } else {
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET nombre_completo = ?, telefono = ?, direccion = ? 
                WHERE id_usuario = ?
            ");
            $stmt->bind_param("sssi", $nombre_completo, $telefono, $direccion, $userId);
            
            if ($stmt->execute()) {
                $success = 'Perfil actualizado correctamente';
                // Recargar datos del usuario
                $auth->login($user['email'], null); // Recargar sesión
                $user = $auth->getUser();
            } else {
                $error = 'Error al actualizar perfil';
            }
        }
    }
    
    // Cambiar contraseña
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Verificar contraseña actual
        if (!password_verify($current_password, $user['contrasena'])) {
            $error = 'Contraseña actual incorrecta';
        } elseif (strlen($new_password) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Las contraseñas no coinciden';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
            $stmt->bind_param("si", $hashed, $userId);
            
            if ($stmt->execute()) {
                $success = 'Contraseña actualizada correctamente';
            } else {
                $error = 'Error al actualizar contraseña';
            }
        }
    }
}

// ============================================
// OBTENER DATOS DEL USUARIO
// ============================================
$stmt = $db->prepare("
    SELECT u.*, r.nombre_rol 
    FROM usuarios u
    JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.id_usuario = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// ============================================
// OBTENER HISTORIAL DE PRÉSTAMOS
// ============================================
$stmt = $db->prepare("
    SELECT p.*, r.titulo, r.autor, r.tipo_recurso
    FROM prestamos p
    JOIN recursos r ON p.id_recurso = r.id_recurso
    WHERE p.id_usuario = ?
    ORDER BY p.fecha_prestamo DESC
    LIMIT 20
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$historialPrestamos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Mi Perfil';
include '../../includes/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <h1>👤 Mi Perfil</h1>
        <p>Gestiona tu información personal y preferencias</p>
    </div>

    <!-- Mensajes de alerta -->
    <?php if ($error): ?>
        <div class="alert alert-error">
            <span class="alert-icon">❌</span>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✅</span>
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Tabs de navegación -->
    <div class="profile-tabs">
        <a href="?tab=perfil" class="tab <?php echo $activeTab === 'perfil' ? 'active' : ''; ?>">
            📝 Datos Personales
        </a>
        <a href="?tab=password" class="tab <?php echo $activeTab === 'password' ? 'active' : ''; ?>">
            🔒 Cambiar Contraseña
        </a>
        <a href="?tab=historial" class="tab <?php echo $activeTab === 'historial' ? 'active' : ''; ?>">
            📋 Historial de Préstamos
        </a>
    </div>

    <!-- Contenido de los tabs -->
    <div class="profile-content">
        <!-- TAB 1: Datos Personales -->
        <?php if ($activeTab === 'perfil'): ?>
            <div class="profile-card">
                <div class="profile-info">
                    <div class="info-row">
                        <span class="info-label">👤 Usuario</span>
                        <span class="info-value"><?php echo e($userData['nombre_usuario']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">📧 Email</span>
                        <span class="info-value"><?php echo e($userData['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">👑 Rol</span>
                        <span class="info-value badge"><?php echo e($userData['nombre_rol']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">📅 Registrado</span>
                        <span class="info-value"><?php echo formatDate($userData['fecha_registro'], 'd/m/Y H:i'); ?></span>
                    </div>
                    <?php if ($userData['ultimo_acceso']): ?>
                        <div class="info-row">
                            <span class="info-label">🕐 Último acceso</span>
                            <span class="info-value"><?php echo formatDate($userData['ultimo_acceso'], 'd/m/Y H:i'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" action="" class="profile-form">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo <span class="required">*</span></label>
                        <input type="text" id="nombre_completo" name="nombre_completo" 
                               value="<?php echo e($userData['nombre_completo']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" 
                               value="<?php echo e($userData['telefono']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="2"><?php echo e($userData['direccion']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Actualizar Datos</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- TAB 2: Cambiar Contraseña -->
        <?php if ($activeTab === 'password'): ?>
            <div class="profile-card">
                <h3>🔒 Cambiar Contraseña</h3>
                <p class="form-hint">Por seguridad, te recomendamos cambiar tu contraseña regularmente.</p>

                <form method="POST" action="" class="profile-form">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Contraseña Actual <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña <span class="required">*</span></label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                        <small class="form-hint">Mínimo 6 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">🔑 Cambiar Contraseña</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- TAB 3: Historial de Préstamos -->
        <?php if ($activeTab === 'historial'): ?>
            <div class="profile-card">
                <h3>📋 Historial de Préstamos</h3>
                
                <?php if (empty($historialPrestamos)): ?>
                    <div class="empty-state">
                        <p>📭 No tienes préstamos registrados</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Recurso</th>
                                    <th>Fecha Préstamo</th>
                                    <th>Devolución</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historialPrestamos as $p): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($p['titulo']); ?></strong>
                                            <small><?php echo e($p['autor'] ?? 'Autor desconocido'); ?></small>
                                        </td>
                                        <td><?php echo formatDate($p['fecha_prestamo'], 'd/m/Y'); ?></td>
                                        <td>
                                            <?php if ($p['fecha_devolucion_real']): ?>
                                                <?php echo formatDate($p['fecha_devolucion_real'], 'd/m/Y'); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status status-<?php echo $p['estado']; ?>">
                                                <?php 
                                                $labels = [
                                                    'activo' => '🟢 Activo',
                                                    'devuelto' => '✅ Devuelto',
                                                    'vencido' => '🔴 Vencido',
                                                    'cancelado' => '⛔ Cancelado'
                                                ];
                                                echo $labels[$p['estado']] ?? $p['estado'];
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ============================================
   ESTILOS DEL PERFIL
   ============================================ */

.profile-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem 0;
}

.profile-header {
    margin-bottom: 2rem;
}

.profile-header h1 {
    font-size: 1.8rem;
    color: #2d3436;
    margin-bottom: 0.25rem;
}

.profile-header p {
    color: #636e72;
}

/* Tabs */
.profile-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    background: white;
    padding: 0.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    flex-wrap: wrap;
}

.tab {
    padding: 0.7rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    color: #636e72;
    font-weight: 500;
    transition: all 0.3s;
    flex: 1;
    text-align: center;
    min-width: 120px;
}

.tab:hover {
    background: #f0f0f0;
    color: #6C5CE7;
}

.tab.active {
    background: #6C5CE7;
    color: white;
}

/* Profile Cards */
.profile-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.profile-card h3 {
    color: #2d3436;
    margin-bottom: 0.5rem;
}

.profile-card .form-hint {
    color: #636e72;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

/* Info Rows */
.profile-info {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #f0f0f0;
}

.info-row {
    display: flex;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #636e72;
    width: 150px;
    flex-shrink: 0;
}

.info-value {
    color: #2d3436;
    font-weight: 500;
}

.badge {
    background: #6C5CE7;
    color: white;
    padding: 0.2rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
}

/* Profile Form */
.profile-form {
    margin-top: 1rem;
}

.profile-form .form-group {
    margin-bottom: 1.25rem;
}

.profile-form label {
    display: block;
    font-weight: 500;
    color: #2d3436;
    margin-bottom: 0.4rem;
    font-size: 0.9rem;
}

.profile-form .required {
    color: #E17055;
}

.profile-form input,
.profile-form textarea {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.3s;
}

.profile-form input:focus,
.profile-form textarea:focus {
    outline: none;
    border-color: #6C5CE7;
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
}

.profile-form textarea {
    resize: vertical;
    min-height: 60px;
}

.profile-form .form-hint {
    display: block;
    color: #b2bec3;
    font-size: 0.85rem;
    margin-top: 0.3rem;
}

/* History Table */
.table-container {
    overflow-x: auto;
    margin-top: 1rem;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.history-table thead {
    background: #f8f9fa;
}

.history-table th {
    padding: 0.8rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #2d3436;
}

.history-table td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #f5f5f5;
}

.history-table tr:hover {
    background: #fafafa;
}

.history-table small {
    display: block;
    color: #636e72;
    font-size: 0.8rem;
}

.status {
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
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

.text-muted {
    color: #b2bec3;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #b2bec3;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-card {
        padding: 1.5rem;
    }
    
    .profile-tabs {
        flex-direction: column;
    }
    
    .tab {
        flex: none;
        width: 100%;
    }
    
    .info-row {
        flex-direction: column;
        padding: 0.4rem 0;
    }
    
    .info-label {
        width: 100%;
        font-size: 0.85rem;
    }
    
    .history-table {
        font-size: 0.8rem;
    }
    
    .history-table th,
    .history-table td {
        padding: 0.5rem;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>