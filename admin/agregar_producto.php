<?php
session_start();
include("../config/auth.php");
require_once("../config/conexion.php");

$mensaje = "";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])){
    $nombre   = trim($_POST['nombre']);
    $desc     = trim($_POST['descripcion']);
    $precio   = (float)$_POST['precio'];
    $cat_str  = $_POST['categoria_id'];
    $img      = $_FILES['imagen']['name'];
    $tmp      = $_FILES['imagen']['tmp_name'];

    if(move_uploaded_file($tmp, "../img/" . $img)){
        try { $cat_oid = new MongoDB\BSON\ObjectId($cat_str); }
        catch(Exception $e){ $mensaje = "<p class='error'>Categoría inválida.</p>"; goto fin; }

        $r = $col_productos->insertOne([
            'nombre'      => $nombre,
            'descripcion' => $desc,
            'precio'      => $precio,
            'imagen'      => $img,
            'categoria_id'=> $cat_oid
        ]);
        $mensaje = $r->getInsertedCount() > 0
            ? "<p class='success'>Producto agregado correctamente.</p>"
            : "<p class='error'>Error al guardar el producto.</p>";
    } else {
        $mensaje = "<p class='error'>Error al subir la imagen.</p>";
    }
}
fin:
include("../includes/header.php");
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/AD_forms.css">

<div class="container">
    <div class="form-container">
        <h2>Agregar Producto</h2>
        <?php echo $mensaje; ?>
        <form method="POST" enctype="multipart/form-data" class="form">
            <input type="text" name="nombre" placeholder="Nombre del producto" required>
            <textarea name="descripcion" placeholder="Descripción breve..."></textarea>
            <select name="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach($col_categorias->find() as $cat): ?>
                    <option value="<?php echo (string)$cat['_id']; ?>">
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" step="0.01" name="precio" placeholder="Precio (ej: 10.50)" required>
            <div id="preview-area" style="margin:10px 0;text-align:center;">
                <img id="img-preview" src="" style="display:none;width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:1px solid #333;">
            </div>
            <input type="file" name="imagen" id="file-input" accept="image/*" required>
            <button type="submit" name="guardar">Guardar Producto</button>
        </form>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/js/AD_forms.js"></script>
<?php include("../includes/footer.php"); ?>
