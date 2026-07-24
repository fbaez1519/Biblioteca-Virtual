<?php
// ============================================
// EDITAR RECURSO
// ============================================

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireBibliotecario();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setAlert('ID de recurso inválido', 'error');
    header('Location: index.php');
    exit;
}

// Obtener datos del recurso
$stmt = $db->prepare("SELECT * FROM recursos WHERE id_recurso = ? AND activo = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$recurso = $stmt->get_result()->fetch_assoc();

if (!$recurso) {
    setAlert('Recurso no encontrado', 'error');
    header('Location: index.php');
    exit;
}

// Obtener categorías
$categorias = $db->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'autor' => trim($_POST['autor'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'tipo_recurso' => $_POST['tipo_recurso'] ?? 'libro',
        'id_categoria' => isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null,
        'isbn' => trim($_POST['isbn'] ?? ''),
        'editorial' => trim($_POST['editorial'] ?? ''),
        'anio_publicacion' => isset($_POST['anio_publicacion']) ? (int)$_POST['anio_publicacion'] : null,
        'cantidad_total' => isset($_POST['cantidad_total']) ? (int)$_POST['cantidad_total'] : 1,
        'ubicacion' => trim($_POST['ubicacion'] ?? '')
    ];
    
    // Validaciones
    $errors = [];
    if (empty($formData['titulo'])) {
        $errors[] = 'El título es requerido';
    }
    if ($formData['cantidad_total'] < 1) {
        $errors[] = 'La cantidad total debe ser al menos 1';
    }
    
    if (empty($errors)) {
        // Actualizar recurso
        $stmt = $db->prepare("
            UPDATE recursos SET
                titulo = ?, autor = ?, descripcion = ?, tipo_recurso = ?,
                id_categoria = ?, isbn = ?, editorial = ?, anio_publicacion = ?,
                cantidad_total = ?, ubicacion = ?
            WHERE id_recurso = ?
        ");
        
        $stmt->bind_param(
            "ssssissisii",
            $formData['titulo'],
            $formData['autor'],
            $formData['descripcion'],
            $formData['tipo_recurso'],
            $formData['id_categoria'],
            $formData['isbn'],
            $formData['editorial'],
            $formData['anio_publicacion'],
            $formData['cantidad_total'],
            $formData['ubicacion'],
            $id
        );
        
        if ($stmt->execute()) {
            setAlert('Recurso actualizado exitosamente', 'success');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Error al actualizar: ' . $stmt->error;
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$pageTitle = 'Editar Recurso';
include '../../includes/header.php';
?>

<div class="resource-form-container">
    <div class="form-header">
        <h1>✏️ Editar Recurso</h1>
        <a href="index.php" class="btn btn-outline">← Volver a Recursos</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="resource-form">
        <div class="form-grid">
            <div class="form-column">
                <div class="form-group">
                    <label for="titulo">Título <span class="required">*</span></label>
                    <input type="text" id="titulo" name="titulo" 
                           value="<?php echo e($recurso['titulo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="autor">Autor</label>
                    <input type="text" id="autor" name="autor" 
                           value="<?php echo e($recurso['autor']); ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo e($recurso['descripcion']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tipo_recurso">Tipo de Recurso <span class="required">*</span></label>
                    <select id="tipo_recurso" name="tipo_recurso" required>
                        <option value="libro" <?php echo $recurso['tipo_recurso'] == 'libro' ? 'selected' : ''; ?>>📖 Libro</option>
                        <option value="audio" <?php echo $recurso['tipo_recurso'] == 'audio' ? 'selected' : ''; ?>>🎵 Audio Libro</option>
                        <option value="articulo" <?php echo $recurso['tipo_recurso'] == 'articulo' ? 'selected' : ''; ?>>📄 Artículo</option>
                    </select>
                </div>
            </div>

            <div class="form-column">
                <div class="form-group">
                    <label for="id_categoria">Categoría</label>
                    <select id="id_categoria" name="id_categoria">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" 
                                <?php echo $recurso['id_categoria'] == $cat['id_categoria'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" value="<?php echo e($recurso['isbn']); ?>">
                </div>

                <div class="form-group">
                    <label for="editorial">Editorial</label>
                    <input type="text" id="editorial" name="editorial" value="<?php echo e($recurso['editorial']); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="anio_publicacion">Año de Publicación</label>
                        <input type="number" id="anio_publicacion" name="anio_publicacion" 
                               value="<?php echo e($recurso['anio_publicacion']); ?>"
                               min="1900" max="<?php echo date('Y'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cantidad_total">Cantidad Total <span class="required">*</span></label>
                        <input type="number" id="cantidad_total" name="cantidad_total" 
                               value="<?php echo e($recurso['cantidad_total']); ?>" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ubicacion">Ubicación</label>
                    <input type="text" id="ubicacion" name="ubicacion" value="<?php echo e($recurso['ubicacion']); ?>">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">💾 Actualizar Recurso</button>
            <a href="index.php" class="btn btn-outline btn-lg">Cancelar</a>
        </div>
    </form>
</div>

<style>
/* Mismo estilo que create.php */
.resource-form-container {
    max-width: 1000px;
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

.resource-form {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.form-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    color: #2d3436;
    margin-bottom: 0.4rem;
    font-size: 0.9rem;
}

.form-group .required {
    color: #E17055;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.7rem 1rem;
    border: 1px solid #dfe6e9;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #6C5CE7;
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #f0f0f0;
    justify-content: flex-end;
}

.btn-lg {
    padding: 0.8rem 2rem;
    font-size: 1rem;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .resource-form {
        padding: 1.5rem;
    }
    
    .form-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>