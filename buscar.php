<?php
require_once("config/conexion.php");

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if($buscar == ''){ exit(); }

$regex = new MongoDB\BSON\Regex(preg_quote($buscar, '/'), 'i');

$resultados = iterator_to_array($col_productos->find([
    '$or' => [['nombre' => $regex], ['descripcion' => $regex]]
], ['sort' => ['_id' => -1]]));

// También buscar por nombre de categoría
$ids_ya = array_map(fn($p) => (string)$p['_id'], $resultados);
foreach($col_categorias->find(['nombre' => $regex]) as $c){
    foreach($col_productos->find(['categoria_id' => $c['_id']]) as $p){
        if(!in_array((string)$p['_id'], $ids_ya)){
            $resultados[] = $p;
            $ids_ya[]     = (string)$p['_id'];
        }
    }
}

if(empty($resultados)){
    echo "<p class='sin-resultados'>No se encontraron productos.</p>";
    exit();
}

foreach($resultados as $row){
    $cat        = $col_categorias->findOne(['_id' => $row['categoria_id']]);
    $cat_nombre = $cat ? $cat['nombre'] : 'Sin categoría';
    $prod_id    = (string)$row['_id'];
?>
    <div class="card-resultado">
        <a href="<?php echo BASE_URL; ?>/index.php?ruta=producto&id=<?php echo $prod_id; ?>" class="card-resultado-link"></a>
        <div class="card-resultado-imagen">
            <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($row['imagen']); ?>" alt="">
        </div>
        <div class="card-resultado-info">
            <span class="categoria-min"><?php echo htmlspecialchars($cat_nombre); ?></span>
            <h4 class="nombre-min"><?php echo htmlspecialchars($row['nombre']); ?></h4>
            <p class="precio-min">$<?php echo number_format((float)$row['precio'], 2); ?></p>
        </div>
    </div>
<?php } ?>
