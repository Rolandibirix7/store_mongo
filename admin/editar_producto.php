<?php
include("../config/auth.php");
require_once("../config/conexion.php");

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
try { $oid = new MongoDB\BSON\ObjectId($id); }
catch(Exception $e){ die("ID inválido"); }

$row = $col_productos->findOne(['_id' => $oid]);
if(!$row){ die("Producto no encontrado"); }

if(isset($_POST['actualizar'])){
    try { $cat_oid = new MongoDB\BSON\ObjectId($_POST['categoria_id']); }
    catch(Exception $e){ die("Categoría inválida"); }

    $updateData = [
        'nombre'       => trim($_POST['nombre']),
        'descripcion'  => trim($_POST['descripcion']),
        'precio'       => (float)$_POST['precio'],
        'categoria_id' => $cat_oid
    ];

    if (!empty($_FILES['imagen']['name'])) {
        $ext     = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imgName = 'prod_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], "../img/" . $imgName)) {
            if (!empty($row['imagen'])) {
                $oldImgPath = "../img/" . $row['imagen'];
                if (file_exists($oldImgPath)) { unlink($oldImgPath); }
            }
            $updateData['imagen'] = $imgName;
        }
    }

    $col_productos->updateOne(['_id' => $oid], ['$set' => $updateData]);
    $cat_param_url = isset($_GET['cat']) ? trim($_GET['cat']) : '';
    $redirect_url = BASE_URL . "/admin/dashboard.php" . (!empty($cat_param_url) ? "?cat=" . urlencode($cat_param_url) : "");
    header("Location: " . $redirect_url); exit();
}

include("../includes/header.php");
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/AD_forms.css">
<style>
.file-drop {
    position: relative; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 8px;
    border: 2px dashed rgba(255,255,255,0.2); border-radius: 12px;
    padding: 24px; text-align: center; cursor: pointer;
    transition: all 0.2s; color: #94a3b8; font-size: 0.85rem;
    min-height: 120px; margin-top: 10px; margin-bottom: 15px;
}
.file-drop input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.file-drop:hover, .file-drop.drag-over { border-color: #6366f1; background: rgba(99,102,241,0.07); color: #818cf8; }
.file-drop #img-preview { max-width: 100%; max-height: 180px; border-radius: 8px; object-fit: cover; position: relative; z-index: 1; pointer-events: none; }
.file-drop.has-preview svg, .file-drop.has-preview span { display: none; }
.current-img {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px; padding: 10px; margin-top: 10px;
}
.current-img img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
.current-img span { font-size: 0.8rem; color: #64748b; }
</style>

<div class="container">
    <div class="form-container">
        <h2>Editar Producto</h2>
        <form method="POST" enctype="multipart/form-data" class="form">
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" required>
            <textarea name="descripcion"><?php echo htmlspecialchars($row['descripcion']); ?></textarea>
            <select name="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach($col_categorias->find() as $cat):
                    $cid      = (string)$cat['_id'];
                    $selected = ((string)$row['categoria_id'] === $cid) ? 'selected' : '';
                ?>
                    <option value="<?php echo $cid; ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" step="0.01" name="precio" value="<?php echo $row['precio']; ?>" required>
            
            <label style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Imagen (opcional)</label>
            <?php if (!empty($row['imagen'])): ?>
                <div class="current-img">
                    <img src="../img/<?php echo htmlspecialchars($row['imagen']); ?>" alt="">
                    <span>Imagen actual</span>
                </div>
            <?php endif; ?>
            <div class="file-drop" id="fileDrop">
                <input type="file" name="imagen" id="file-input" accept="image/*">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>Arrastra o haz clic para subir imagen nueva</span>
                <img id="img-preview" src="" alt="" style="display:none;">
            </div>
            <button type="submit" name="actualizar">Actualizar Producto</button>
        </form>
    </div>
</div>
<script>
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
