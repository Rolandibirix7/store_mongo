<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

$carrito = $_SESSION['carrito'] ?? [];
$subtotal = 0;
foreach ($carrito as $item) $subtotal += $item['precio'] * $item['cantidad'];
$envio   = $subtotal > 0 ? 3.50 : 0;
$total   = $subtotal + $envio;
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/carrito.css">

<div class="cart-wrapper">
    <div class="cart-header">
        <h2>🛒 Mi Carrito</h2>
        <span class="cart-count"><?php echo array_sum(array_column($carrito,'cantidad')); ?> artículo(s)</span>
    </div>

    <?php if (empty($carrito)): ?>
        <div class="cart-empty">
            <div class="empty-icon">🛍️</div>
            <h3>Tu carrito está vacío</h3>
            <p>Agrega productos desde la tienda.</p>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn-primary">Ir a la tienda</a>
        </div>
    <?php else: ?>
    <div class="cart-layout">
        <!-- Items -->
        <div class="cart-items">
            <?php foreach ($carrito as $id => $item): ?>
            <div class="cart-item" data-id="<?php echo htmlspecialchars($id); ?>">
                <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($item['imagen']); ?>" alt="">
                <div class="item-info">
                    <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                    <p class="item-price">$<?php echo number_format($item['precio'],2); ?></p>
                </div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="cambiarCantidad('<?php echo $id; ?>',-1)">−</button>
                    <span class="qty-val" id="qty-<?php echo $id; ?>"><?php echo $item['cantidad']; ?></span>
                    <button class="qty-btn" onclick="cambiarCantidad('<?php echo $id; ?>',1)">+</button>
                </div>
                <div class="item-total" id="total-<?php echo $id; ?>">
                    $<?php echo number_format($item['precio']*$item['cantidad'],2); ?>
                </div>
                <button class="btn-remove" onclick="eliminarItem('<?php echo $id; ?>')">✕</button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary -->
        <div class="cart-summary">
            <h3>Resumen del pedido</h3>
            <div class="summary-row"><span>Subtotal</span><span id="subtotal">$<?php echo number_format($subtotal,2); ?></span></div>
            <div class="summary-row"><span>Envío estimado</span><span>$<?php echo number_format($envio,2); ?></span></div>
            <div class="summary-row total-row"><span>Total</span><span id="grand-total">$<?php echo number_format($total,2); ?></span></div>
            <a href="<?php echo BASE_URL; ?>/index.php?ruta=checkout" class="btn-checkout">
                💳 Proceder al pago
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn-continue">← Seguir comprando</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
const BASE = '<?php echo BASE_URL; ?>';

function actualizarContador() {
    fetch(BASE + '/carrito_action.php?action=count')
        .then(r => r.json()).then(d => {
            const b = document.getElementById('cart-badge');
            if (b) b.textContent = d.count;
        });
}

function cambiarCantidad(id, delta) {
    const el    = document.getElementById('qty-' + id);
    let nuevaCant = parseInt(el.textContent) + delta;
    if (nuevaCant < 1) { eliminarItem(id); return; }

    const fd = new FormData();
    fd.append('action','actualizar'); fd.append('producto_id',id); fd.append('cantidad',nuevaCant);
    fetch(BASE + '/carrito_action.php', {method:'POST', body:fd})
        .then(r => r.json()).then(d => {
            if (d.ok) location.reload();
        });
}

function eliminarItem(id) {
    const fd = new FormData();
    fd.append('action','eliminar'); fd.append('producto_id',id);
    fetch(BASE + '/carrito_action.php', {method:'POST', body:fd})
        .then(r => r.json()).then(d => {
            if (d.ok) location.reload();
        });
}
actualizarContador();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
