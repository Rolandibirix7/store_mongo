<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/conexion.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
try {
    $oid = new MongoDB\BSON\ObjectId($id);
} catch(Exception $e) {
    echo "<div style='color:white;text-align:center;padding:80px;'>Producto no encontrado.</div>";
    include __DIR__ . '/../includes/footer.php'; exit;
}

$row = $col_productos->findOne(['_id' => $oid]);
if (!$row) {
    echo "<div style='color:white;text-align:center;padding:80px;'>Producto no encontrado.</div>";
    include __DIR__ . '/../includes/footer.php'; exit;
}

$cat              = $col_categorias->findOne(['_id' => $row['categoria_id']]);
$categoria_nombre = $cat ? $cat['nombre'] : 'Sin categoría';
$prod_id          = (string)$row['_id'];

// Rating data
$prom      = (float)($row['calificacion_promedio'] ?? 0);
$total_cal = (int)($row['total_calificaciones']  ?? 0);

// Has the current logged-in user already rated this?
$ya_calificado = false;
if (isset($_SESSION['user_id'])) {
    $ya_calificado = (bool)$col_productos->findOne([
        '_id'                         => $oid,
        'calificaciones.usuario_id'   => $_SESSION['user_id'],
    ]);
}

// Handle rating POST right here on the product page
$rate_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calificar_producto'])) {
    if (!isset($_SESSION['usuario'])) {
        $rate_msg = 'error:Debes iniciar sesión para calificar.';
    } elseif ($ya_calificado) {
        $rate_msg = 'error:Ya calificaste este producto.';
    } else {
        $estrellas = max(1, min(5, (int)($_POST['estrellas'] ?? 0)));
        if ($estrellas < 1) {
            $rate_msg = 'error:Selecciona al menos 1 estrella.';
        } else {
            $col_productos->updateOne(
                ['_id' => $oid],
                ['$push' => ['calificaciones' => [
                    'usuario_id' => $_SESSION['user_id'],
                    'usuario'    => $_SESSION['usuario'],
                    'estrellas'  => $estrellas,
                    'fecha'      => new MongoDB\BSON\UTCDateTime(),
                ]]]
            );
            // Recalculate average
            $updated = $col_productos->findOne(['_id' => $oid]);
            $cals    = iterator_to_array($updated['calificaciones']);
            $nuevo_prom = array_sum(array_column($cals, 'estrellas')) / count($cals);
            $col_productos->updateOne(
                ['_id' => $oid],
                ['$set' => [
                    'calificacion_promedio'  => round($nuevo_prom, 1),
                    'total_calificaciones'   => count($cals),
                ]]
            );
            // Reload updated values
            $row       = $col_productos->findOne(['_id' => $oid]);
            $prom      = (float)$row['calificacion_promedio'];
            $total_cal = (int)$row['total_calificaciones'];
            $ya_calificado = true;
            $rate_msg  = 'success:¡Gracias por tu calificación!';
        }
    }
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/producto.css">

<div class="view-wrapper">
    <div class="main-card">
        <!-- Imagen -->
        <div class="image-container">
            <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($row['imagen']); ?>" alt="Producto">
        </div>

        <!-- Info -->
        <div class="info-container">
            <span class="cat-label">CATEGORÍA:</span>
            <span class="cat-name"><?php echo htmlspecialchars($categoria_nombre); ?></span>

            <h1 class="prod-title"><?php echo htmlspecialchars($row['nombre']); ?></h1>
            <div class="prod-price">$<?php echo number_format((float)$row['precio'], 2); ?></div>

            <!-- Rating display -->
            <div class="rating-display">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star-display <?php echo $i <= round($prom) ? 'filled' : ''; ?>">★</span>
                <?php endfor; ?>
                <?php if ($total_cal > 0): ?>
                    <span class="rating-avg"><?php echo number_format($prom, 1); ?></span>
                    <span class="rating-count-prod">(<?php echo $total_cal; ?> calificación<?php echo $total_cal !== 1 ? 'es' : ''; ?>)</span>
                <?php else: ?>
                    <span class="rating-count-prod">Sin calificaciones aún</span>
                <?php endif; ?>
            </div>

            <div class="desc-box">
                <h3>DESCRIPCIÓN</h3>
                <p><?php echo nl2br(htmlspecialchars($row['descripcion'])); ?></p>
            </div>

            <!-- Rate this product (anyone logged in) -->
            <div class="rate-section">
                <?php if ($rate_msg):
                    [$tipo, $texto] = explode(':', $rate_msg, 2); ?>
                <div class="rate-alert rate-<?php echo $tipo; ?>"><?php echo htmlspecialchars($texto); ?></div>
                <?php endif; ?>

                <?php if (!isset($_SESSION['usuario'])): ?>
                    <p class="rate-login-note">
                        <a href="<?php echo BASE_URL; ?>/login.php">Inicia sesión</a> para calificar este producto.
                    </p>
                <?php elseif ($ya_calificado): ?>
                    <p class="rate-done">⭐ Ya calificaste este producto — ¡gracias!</p>
                <?php else: ?>
                    <p class="rate-label">¿Qué te parece este producto?</p>
                    <form method="POST" id="ratingForm">
                        <input type="hidden" name="calificar_producto" value="1">
                        <input type="hidden" name="estrellas" id="starVal" value="0">
                        <div class="stars-interactive" id="starsRow">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                            <button type="button" class="star-btn" data-val="<?php echo $s; ?>"
                                    onmouseover="hoverStar(<?php echo $s; ?>)"
                                    onmouseout="resetStars()"
                                    onclick="selectStar(<?php echo $s; ?>)">★</button>
                            <?php endfor; ?>
                        </div>
                        <button type="submit" class="btn-rate-submit" id="btnRate" disabled>
                            Enviar calificación
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Add to cart -->
            <div id="cart-msg-prod" class="cart-msg" style="display:none"></div>

            <div class="prod-actions">
                <button class="buy-btn" id="btnAgregar"
                        onclick="agregarCarrito('<?php echo $prod_id; ?>')">
                     Agregar al Carrito
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Rating display ── */
.rating-display {
    display: flex; align-items: center; gap: 4px; margin: 8px 0 20px;
}
.star-display { font-size: 1.3rem; color: rgba(255,255,255,.2); }
.star-display.filled { color: #f59e0b; }
.rating-avg { font-weight: 700; color: #f59e0b; margin-left: 4px; }
.rating-count-prod { font-size: .82rem; color: #64748b; }

/* ── Rate section ── */
.rate-section {
    border-top: 1px solid rgba(255,255,255,.08);
    padding-top: 18px; margin-top: 18px; margin-bottom: 4px;
}
.rate-label { font-size: .88rem; color: #94a3b8; margin: 0 0 10px; }
.stars-interactive { display: flex; gap: 4px; margin-bottom: 12px; }
.star-btn {
    background: none; border: none; font-size: 2rem;
    color: rgba(255,255,255,.2); cursor: pointer;
    transition: color .1s, transform .1s; line-height: 1; padding: 0;
}
.star-btn.hovered, .star-btn.selected { color: #f59e0b; transform: scale(1.15); }
.btn-rate-submit {
    background: rgba(245,158,11,.15); border: 1px solid rgba(245,158,11,.35);
    color: #f59e0b; padding: 8px 20px; border-radius: 10px;
    cursor: pointer; font-size: .88rem; font-weight: 600; transition: all .2s;
}
.btn-rate-submit:disabled { opacity: .4; cursor: not-allowed; }
.btn-rate-submit:not(:disabled):hover { background: rgba(245,158,11,.25); }
.rate-done { color: #f59e0b; font-size: .88rem; margin: 0; }
.rate-login-note { font-size: .85rem; color: #64748b; margin: 0; }
.rate-login-note a { color: #6366f1; text-decoration: none; }
.rate-alert { padding: 8px 12px; border-radius: 8px; font-size: .85rem; margin-bottom: 12px; }
.rate-success { background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3); color: #10b981; }
.rate-error   { background: rgba(239,68,68,.15);  border: 1px solid rgba(239,68,68,.3);  color: #ef4444; }

/* ── Cart actions ── */
.cart-msg {
    margin-bottom: 12px; padding: 10px 14px;
    background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3);
    border-radius: 10px; color: #10b981; font-size: .9rem;
}
.prod-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
.buy-btn {
    flex: 1; background: #3b82f6; color: #fff; border: none;
    padding: 15px 20px; border-radius: 12px; font-size: 1rem;
    font-weight: 600; cursor: pointer; transition: .2s; min-width: 160px;
}
.buy-btn:hover:not(:disabled) { background: #2563eb; transform: translateY(-2px); }
.buy-btn:disabled { opacity: .7; cursor: not-allowed; transform: none; }
.btn-ver-carrito {
    flex: 1; background: rgba(99,102,241,.2); border: 1px solid rgba(99,102,241,.5);
    color: white; padding: 15px 20px; border-radius: 12px; font-size: 1rem;
    font-weight: 600; text-align: center; text-decoration: none;
    transition: .2s; min-width: 140px;
}
.btn-ver-carrito:hover { background: rgba(99,102,241,.35); }
</style>

<script>
let selectedStar = 0;

function hoverStar(val) {
    document.querySelectorAll('.star-btn').forEach((s, i) => {
        s.classList.toggle('hovered', i < val);
        s.classList.remove('selected');
    });
}
function resetStars() {
    document.querySelectorAll('.star-btn').forEach((s, i) => {
        s.classList.remove('hovered');
        s.classList.toggle('selected', i < selectedStar);
    });
}
function selectStar(val) {
    selectedStar = val;
    document.getElementById('starVal').value = val;
    document.getElementById('btnRate').disabled = false;
    document.querySelectorAll('.star-btn').forEach((s, i) => {
        s.classList.remove('hovered');
        s.classList.toggle('selected', i < val);
    });
}

function agregarCarrito(id) {
    const btn = document.getElementById('btnAgregar');
    btn.disabled = true;
    btn.textContent = 'Agregando...';

    const fd = new FormData();
    fd.append('action', 'agregar');
    fd.append('producto_id', id);
    fd.append('cantidad', 1);

    fetch(BASE_URL + '/carrito_action.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                const msg = document.getElementById('cart-msg-prod');
                if (msg) {
                    msg.textContent = '¡Producto agregado al carrito! (' + d.total_items + ' artículo(s))';
                    msg.style.display = 'block';
                }
                btn.textContent = '✓ En el carrito';
                const badge = document.getElementById('cart-badge');
                if (badge) { badge.textContent = d.total_items; badge.style.display = 'flex'; }
                setTimeout(() => {
                    btn.disabled = false;
                    btn.textContent = ' Agregar al Carrito';
                }, 250);
            } else {
                btn.disabled = false;
                btn.textContent = ' Agregar al Carrito';
                alert('Error al agregar: ' + (d.msg || 'intenta de nuevo'));
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.textContent = ' Agregar al Carrito';
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
