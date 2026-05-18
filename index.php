<?php
session_start();

require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/config/routs/routs.php";

$ruta = $_GET['ruta'] ?? 'inicio';

if ($ruta !== 'inicio') {
    $rutas   = new Rutas();
    $archivo = $rutas->pagina($ruta);

    if ($archivo && file_exists($archivo)) {
        include $archivo;
    } else {
        echo "Página no encontrada";
    }
    exit;
}

include __DIR__ . "/includes/header.php";

$categorias = $col_categorias->find();

// Productos destacados = mejor calificados, luego por _id desc
$productos_cursor = $col_productos->find([], ['sort' => ['calificacion_promedio' => -1, '_id' => -1], 'limit' => 6]);
$productos_lista  = [];
foreach ($productos_cursor as $prod) {
    $cat      = $col_categorias->findOne(['_id' => $prod['categoria_id']]);
    $prod_arr = (array)$prod;
    $prod_arr['categoria'] = $cat ? $cat['nombre'] : 'Sin categoría';
    $prod_arr['id']        = (string)$prod['_id'];
    $productos_lista[]     = $prod_arr;
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/index.css">
    

<section class="hero">
    <div class="hero-content">
        <h2>Tu tienda favorita en línea</h2>
        <p>Encuentra los mejores productos al mejor precio. Calidad garantizada, envío seguro.</p>
        <a href="#categorias" class="btn-hero">
            Explorar Ahora
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>
</section>

<section class="categorias" id="categorias">
    <h2 class="titulo-seccion">Categorías</h2>
    <div class="categorias-grid">
        <?php foreach ($categorias as $cat): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?ruta=categoria&id=<?php echo (string)$cat['_id']; ?>" class="categoria-card">
                <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($cat['imagen']); ?>" alt="<?php echo htmlspecialchars($cat['nombre']); ?>">
                <h3><?php echo htmlspecialchars($cat['nombre']); ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="productos">
    <h2 class="titulo-seccion">Productos Destacados</h2>
    <div class="productos-grid">
        <?php foreach ($productos_lista as $row): ?>
            <div class="card">
                <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($row['imagen']); ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                <div class="card-body">
                    <span class="categoria"><?php echo htmlspecialchars($row['categoria']); ?></span>
                    <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                    <p class="precio">$<?php echo number_format((float)$row['precio'], 2); ?></p>
                    <?php 
                    $prom = (float)($row['calificacion_promedio'] ?? 0);
                    $total_cal = (int)($row['total_calificaciones'] ?? 0);
                    if ($prom > 0):
                        $llenas = floor($prom);
                        $media  = ($prom - $llenas) >= 0.5 ? 1 : 0;
                    ?>
                    <div class="prod-rating">
                        <?php for($i=0;$i<$llenas;$i++) echo '<span class="star-s filled">★</span>'; ?>
                        <?php if($media) echo '<span class="star-s half">★</span>'; ?>
                        <?php for($i=0;$i<(5-$llenas-$media);$i++) echo '<span class="star-s">★</span>'; ?>
                        <span class="rating-count">(<?php echo $total_cal; ?>)</span>
                    </div>
                    <?php endif; ?>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <a href="<?php echo BASE_URL; ?>/index.php?ruta=producto&id=<?php echo $row['id']; ?>" class="btn-comprar" style="flex:1">
                            Ver Producto
                        </a>
                        <button class="btn-comprar btn-cart-quick" style="flex:1;background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.4)"
                                onclick="addToCart('<?php echo $row['id']; ?>',this)">
                            🛒
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
const BASE_URL_IDX = '<?php echo BASE_URL; ?>';
function addToCart(id, btn) {
    btn.disabled = true; btn.textContent = '✓';
    const fd = new FormData();
    fd.append('action','agregar'); fd.append('producto_id',id); fd.append('cantidad',1);
    fetch(BASE_URL_IDX + '/carrito_action.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(d=>{
            if(d.ok){
                const badge = document.getElementById('cart-badge');
                if(badge){ badge.textContent = d.total_items; badge.style.display='flex'; }
                setTimeout(()=>{ btn.disabled=false; btn.textContent='🛒'; },1500);
            }
        });
}
</script>
<?php include("includes/footer.php"); ?>
