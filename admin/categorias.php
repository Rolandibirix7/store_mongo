<?php
session_start();
require_once("../config/auth.php");
require_once("../config/conexion.php");

$mensaje = "";
$tipo_msg = "";

// ── CREATE ──────────────────────────────────────
if (isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    if (empty($nombre)) {
        $mensaje = "El nombre es obligatorio."; $tipo_msg = "error";
    } else {
        // Check duplicate
        $existe = $col_categorias->findOne(['nombre' => ['$regex' => '^' . preg_quote($nombre) . '$', '$options' => 'i']]);
        if ($existe) {
            $mensaje = "Ya existe una categoría con ese nombre."; $tipo_msg = "error";
        } else {
            $img = "";
            if (!empty($_FILES['imagen']['name'])) {
                $ext     = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $imgName = 'cat_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], "../img/" . $imgName)) {
                    $img = $imgName;
                }
            }
            $col_categorias->insertOne(['nombre' => $nombre, 'imagen' => $img]);
            $mensaje = "Categoría agregada con éxito."; $tipo_msg = "success";
        }
    }
}

// ── UPDATE ──────────────────────────────────────
if (isset($_POST['actualizar'])) {
    $id     = trim($_POST['id']);
    $nombre = trim($_POST['nombre']);
    try { $oid = new MongoDB\BSON\ObjectId($id); } catch(Exception $e){ die("ID inválido"); }

    $updateData = ['nombre' => $nombre];
    if (!empty($_FILES['imagen']['name'])) {
        $ext     = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imgName = 'cat_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], "../img/" . $imgName)) {
            $updateData['imagen'] = $imgName;
        }
    }
    $col_categorias->updateOne(['_id' => $oid], ['$set' => $updateData]);
    $mensaje = "Categoría actualizada."; $tipo_msg = "success";
}

// ── DELETE ──────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = trim($_GET['eliminar']);
    try {
        $oid = new MongoDB\BSON\ObjectId($id);
        
        // Check if any product uses this category (supporting both ObjectId and String just in case)
        $count = $col_productos->countDocuments([
            '$or' => [
                ['categoria_id' => $oid],
                ['categoria_id' => $id]
            ]
        ]);

        if ($count > 0) {
            $mensaje = "No se puede eliminar: hay $count productos vinculados a esta categoría.";
            $tipo_msg = "error";
        } else {
            // Get category to delete image
            $catToDelete = $col_categorias->findOne(['_id' => $oid]);
            if ($catToDelete) {
                if (!empty($catToDelete['imagen'])) {
                    $imgPath = "../img/" . $catToDelete['imagen'];
                    if (file_exists($imgPath)) { unlink($imgPath); }
                }
                $col_categorias->deleteOne(['_id' => $oid]);
                header("Location: categorias.php?ok=1");
                exit();
            } else {
                $mensaje = "La categoría no existe.";
                $tipo_msg = "error";
            }
        }
    } catch(Exception $e) {
        $mensaje = "Error al intentar eliminar: ID inválido o problema de base de datos.";
        $tipo_msg = "error";
    }
}

if (isset($_GET['ok'])) { $mensaje = "Categoría eliminada."; $tipo_msg = "success"; }

// ── EDIT MODE ────────────────────────────────────
$editCat = null;
if (isset($_GET['editar'])) {
    try { $oid = new MongoDB\BSON\ObjectId(trim($_GET['editar'])); $editCat = $col_categorias->findOne(['_id' => $oid]); } catch(Exception $e){}
}

$categorias = $col_categorias->find([], ['sort' => ['nombre' => 1]]);

include("../includes/header.php");
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/AD_forms.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/categorias_admin.css">

<div class="container">
    <div class="page-header">
        <div>
            <h1>Gestión de Categorías</h1>
            <p class="page-subtitle">Crear, editar y eliminar categorías de productos</p>
        </div>
        <a href="dashboard.php" class="btn btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            Volver al Dashboard
        </a>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert--<?php echo $tipo_msg; ?>">
            <?php if ($tipo_msg === 'success'): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M20 6L9 17l-5-5"/></svg>
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php endif; ?>
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div class="crud-layout">
        <!-- ── FORM ── -->
        <div class="form-panel">
            <?php if ($editCat): ?>
                <h2 class="panel-title">Editar Categoría</h2>
                <form method="POST" enctype="multipart/form-data" class="cat-form">
                    <input type="hidden" name="id" value="<?php echo (string)$editCat['_id']; ?>">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($editCat['nombre']); ?>" required placeholder="Nombre de la categoría">
                    </div>
                    <div class="form-group">
                        <label>Imagen (opcional, deja vacío para mantener)</label>
                        <?php if (!empty($editCat['imagen'])): ?>
                            <div class="current-img">
                                <img src="../img/<?php echo htmlspecialchars($editCat['imagen']); ?>" alt="">
                                <span>Imagen actual</span>
                            </div>
                        <?php endif; ?>
                        <div class="file-drop" id="fileDrop">
                            <input type="file" name="imagen" id="file-input" accept="image/*">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Arrastra o haz clic para subir imagen</span>
                            <img id="img-preview" src="" alt="" style="display:none;">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="actualizar" class="btn-submit">Actualizar Categoría</button>
                        <a href="categorias.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            <?php else: ?>
                <h2 class="panel-title">Nueva Categoría</h2>
                <form method="POST" enctype="multipart/form-data" class="cat-form">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required placeholder="Ej: Electrónicos">
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <div class="file-drop" id="fileDrop">
                            <input type="file" name="imagen" id="file-input" accept="image/*" required>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>Arrastra o haz clic para subir imagen</span>
                            <img id="img-preview" src="" alt="" style="display:none;">
                        </div>
                    </div>
                    <button type="submit" name="guardar" class="btn-submit">Guardar Categoría</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- ── TABLE ── -->
        <div class="table-panel">
            <h2 class="panel-title">Categorías existentes</h2>
            <?php
            $cats_arr = [];
            foreach ($categorias as $c) { $cats_arr[] = $c; }
            if (empty($cats_arr)):
            ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    <p>No hay categorías aún</p>
                </div>
            <?php else: ?>
                <div class="cat-table-wrap">
                    <table class="cat-table">
                        <thead><tr><th>Imagen</th><th>Nombre</th><th>Productos</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach ($cats_arr as $cat):
                            $cid   = (string)$cat['_id'];
                            // Robust count
                            $count = $col_productos->countDocuments([
                                '$or' => [
                                    ['categoria_id' => $cat['_id']],
                                    ['categoria_id' => $cid]
                                ]
                            ]);
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($cat['imagen'])): ?>
                                        <img src="../img/<?php echo htmlspecialchars($cat['imagen']); ?>" class="cat-thumb" alt="">
                                    <?php else: ?>
                                        <div class="cat-thumb-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="cat-name"><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                <td><span class="badge"><?php echo $count; ?></span></td>
                                <td class="cat-actions">
                                    <a href="categorias.php?editar=<?php echo $cid; ?>" class="action-btn action-btn--edit" title="Editar">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Editar
                                    </a>
                                    <a href="<?php echo ($count === 0) ? 'categorias.php?eliminar='.$cid : '#'; ?>" 
                                       class="action-btn <?php echo ($count > 0) ? 'action-btn--disabled' : 'action-btn--delete'; ?>"
                                       onclick="<?php echo ($count > 0) ? "alert('No puedes eliminar esta categoría: Primero mueve o borra los $count productos asignados.'); return false;" : "return confirm('¿Eliminar esta categoría?')"; ?>"
                                       title="<?php echo ($count > 0) ? 'Bloqueado: Tiene productos asignados' : 'Eliminar'; ?>">
                                        
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                            <?php if ($count > 0): ?>
                                                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                            <?php else: ?>
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>
                                            <?php endif; ?>
                                        </svg>
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// File preview
const fileInput = document.getElementById('file-input');
const preview   = document.getElementById('img-preview');
const dropZone  = document.getElementById('fileDrop');

if(fileInput){
    fileInput.addEventListener('change', function(){
        if(this.files && this.files[0]){
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                dropZone.classList.add('has-preview');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault(); dropZone.classList.remove('drag-over');
        fileInput.files = e.dataTransfer.files;
        fileInput.dispatchEvent(new Event('change'));
    });
}
</script>

<?php include("../includes/footer.php"); ?>
