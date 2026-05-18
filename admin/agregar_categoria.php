<?php
require_once("../config/conexion.php");
include("../config/auth.php");

$mensaje = "";
if(isset($_POST['guardar'])){
    $nombre = trim($_POST['nombre']);
    $img    = $_FILES['imagen']['name'];
    if(move_uploaded_file($_FILES['imagen']['tmp_name'], "../img/".$img)){
        $col_categorias->insertOne(['nombre' => $nombre, 'imagen' => $img]);
        header("Location: categorias.php?ok=1"); exit();
    }
}

include("../includes/header.php");
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/AD_forms.css">

<div class="container">
    <div class="form-container">
        <h2>Agregar Categoría</h2>
        <?php echo $mensaje; ?>
        <form method="POST" enctype="multipart/form-data" class="form">
            <input type="text" name="nombre" placeholder="Nombre categoría" required>
            <div id="preview-container">
                <img id="img-preview" src="" style="display:none;width:100%;border-radius:8px;margin-bottom:10px;">
            </div>
            <input type="file" name="imagen" id="file-input" required>
            <button type="submit" name="guardar">Guardar Categoría</button>
        </form>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/js/AD_forms.js"></script>
<?php include("../includes/footer.php"); ?>
