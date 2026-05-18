<?php
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/conexion.php';

if(!isset($_GET['id'])){ die("Categoría no encontrada"); }

try {
    $oid = new MongoDB\BSON\ObjectId($_GET['id']);
} catch(Exception $e){ die("ID inválido"); }

$categoria = $col_categorias->findOne(['_id' => $oid]);
if(!$categoria){ die("Categoría no encontrada"); }

$productos_arr = iterator_to_array($col_productos->find(['categoria_id' => $oid]));
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/categoria.css">

<section class="container">
    <h2 class="titulo-seccion"><?php echo htmlspecialchars($categoria['nombre']); ?></h2>

    <?php if(count($productos_arr) > 0): ?>
        <div class="productos">
            <?php foreach($productos_arr as $row): $prod_id = (string)$row['_id']; ?>
                <div class="card">
                    <a class="card-link" href="<?php echo BASE_URL; ?>/index.php?ruta=producto&id=<?php echo $prod_id; ?>"></a>
                    <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($row['imagen']); ?>">
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                        <div class="categoria"><?php echo htmlspecialchars($categoria['nombre']); ?></div>
                        <div class="precio">$<?php echo $row['precio']; ?></div>
                        <a class="btn-comprar" href="<?php echo BASE_URL; ?>/index.php?ruta=producto&id=<?php echo $prod_id; ?>">
                            Comprar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; opacity:.7;">No hay productos en esta categoría todavía.</p>
    <?php endif; ?>
</section>

<script src="<?php echo BASE_URL; ?>/js/categoria.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
