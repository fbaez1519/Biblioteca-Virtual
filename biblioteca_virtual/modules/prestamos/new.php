<?php
// ============================================
// NUEVO PRÉSTAMO
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireBibliotecario();

$db = getDB();
$error = '';
$success = '';

// Obtener lista de usuarios
$usuarios = $db->query("
    SELECT id_usuario, nombre_completo, email 
    FROM usuarios 
    WHERE activo = 1 
    ORDER BY nombre_completo
")->fetch_all(MYSQLI_ASSOC);

// Obtener recursos disponibles
$recursos = $db->query("
    SELECT id_recurso, titulo, autor, cantidad_disponible 
    FROM recursos 
    WHERE activo = 1 AND cantidad_disponible > 0
    ORDER BY titulo
")->fetch_all(MYSQLI_ASSOC);

// Pre-seleccionar recurso si viene por GET
$recursoSeleccionado = isset($_GET['recurso']) ? (int)$_GET['recurso'] : 0;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
    $id_recurso = isset($_POST['id_recurso']) ? (int)$_POST['id_recurso'] : 0;
    $dias_prestamo = isset($_POST['dias_prestamo']) ? (int)$_POST['dias_prestamo'] : 7;
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Validaciones
    if ($id_usuario <= 0) {
        $error = 'Selecciona un usuario';
    } elseif ($id_recurso <= 0) {
        $error = 'Selecciona un recurso';
    } elseif ($dias_prestamo < 1) {
        $error = 'El plazo de préstamo debe ser al menos 1 día';
    } else {
        // Verificar que el recurso esté disponible
        $stmt = $db->prepare("SELECT cantidad_disponible FROM recursos WHERE id_recurso = ?");
        $stmt->bind_param("i", $id_recurso);
        $stmt->execute();
        $recurso = $stmt->get_result()->fetch_assoc();
        
        if (!$recurso || $recurso['cantidad_disponible'] < 1) {
            $error = 'El recurso no está disponible';
        } else {
            // Verificar que el usuario no tenga préstamos vencidos
            $stmt = $db->prepare("
                SELECT COUNT(*) as total 
                FROM prestamos 
                WHERE id_usuario = ? AND estado = 'vencido'
            ");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $vencidos = $stmt->get_result()->fetch_assoc()['total'];
            
            if ($vencidos > 0) {
                $error = 'El usuario tiene préstamos vencidos. No puede tomar nuevos préstamos.';
            } else {
                // Crear el préstamo
                $fecha_prestamo = date('Y-m-d H:i:s');
                $fecha_devolucion = date('Y-m-d H:i:s', strtotime("+$dias_prestamo days"));
                
                $db->begin_transaction();
                
                try {
                    // Insertar préstamo
                    $stmt = $db->prepare("
                        INSERT INTO prestamos (
                            id_usuario, id_recurso, fecha_prestamo, 
                            fecha_devolucion_esperada, observaciones
                        ) VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("iisss", $id_usuario, $id_recurso, $fecha_prestamo, $fecha_devolucion, $observaciones);
                    $stmt->execute();
                    
                    // Actualizar cantidad disponible
                    $stmt = $db->prepare("
                        UPDATE recursos 
                        SET cantidad_disponible = cantidad_disponible - 1 
                        WHERE id_recurso = ?
                    ");
                    $stmt->bind_param("i", $id_recurso);
                    $stmt->execute();
                    
                    $db->commit();
                    
                    setAlert('Préstamo registrado exitosamente', 'success');
                    header('Location: index.php');
                    exit;
                    
                } catch (Exception $e) {
                    $db->rollback();
                    $error = 'Error al registrar préstamo: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Nuevo Préstamo';
include '../../includes/header.php';
?>

<div class="loan-form-container">
    <div class="form-header">
        <h1>📋 Nuevo Préstamo</h1>
        <a href="index.php" class="btn btn-outline">← Volver a Préstamos</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="loan-form">
        <div class="form-group">
            <label for="id_usuario">Usuario <span class="required">*</span></label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Seleccionar usuario</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?php echo $u['id_usuario']; ?>">
                        <?php echo e($u['nombre_completo']); ?> - <?php echo e($u['email']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_recurso">Recurso <span class="required">*</span></label>
            <select id="id_recurso" name="id_recurso" required>
                <option value="">Seleccionar recurso</option>
                <?php foreach ($recursos as $r): ?>
                    <option value="<?php echo $r['id_recurso']; ?>" 
                        <?php echo $recursoSeleccionado == $r['id_recurso'] ? 'selected' : ''; ?>>
                        <?php echo e($r['titulo']); ?> - <?php echo e($r['autor'] ?? 'Sin autor'); ?> 
                        (<?php echo $r['cantidad_disponible']; ?> disponibles)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="dias_prestamo">Días de Préstamo <span class="required">*</span></label>
            <input type="number" id="dias_prestamo" name="dias_prestamo" 
                   value="7" min="1" max="30" required>
            <small class="form-hint">Máximo 30 días</small>
        </div>

        <div class="form-group">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3"
                      placeholder="Notas adicionales sobre el préstamo"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">📋 Registrar Préstamo</button>
            <a href="index.php" class="btn btn-outline btn-lg">Cancelar</a>
        </div>
    </form>
</div>

<style>
.loan-form-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 1rem 0;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.form-header h1 {
    font-size: 1.8rem;
    color: #2d3436;
}

.loan-form {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: #2d3436;
    margin-bottom: 0.4rem;
}

.form-group .required {
    color: #E17055;
}

.form-group select,
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.3s;
}

.form-group select:focus,
.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #6C5CE7;
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-hint {
    display: block;
    color: #b2bec3;
    font-size: 0.85rem;
    margin-top: 0.3rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f0f0f0;
}

.btn-lg {
    padding: 0.8rem 2rem;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .loan-form {
        padding: 1.5rem;
    }
    
    .form-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>