<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

// Change order status
if (isset($_POST['cambiar_estado'])) {
    try {
        $oid    = new MongoDB\BSON\ObjectId($_POST['pedido_id']);
        $estado = $_POST['estado'];
        $col_pedidos->updateOne(['_id' => $oid], ['$set' => ['estado' => $estado]]);
    } catch(Exception $e) {}
    header('Location: ' . BASE_URL . '/index.php?ruta=admin_pedidos'); exit;
}

$filtro_estado = $_GET['estado'] ?? '';
$filtro        = $filtro_estado ? ['estado' => $filtro_estado] : [];

$pedidos = iterator_to_array(
    $col_pedidos->find($filtro, ['sort' => ['fecha' => -1]])
);

$estados = ['Procesando','Confirmado','Enviado','Entregado','Cancelado'];
$totales = [];
foreach ($estados as $e) {
    $totales[$e] = $col_pedidos->countDocuments(['estado' => $e]);
}
$grand_total = $col_pedidos->countDocuments([]);

$estado_class = [
    'Procesando' => 'badge-processing',
    'Confirmado' => 'badge-confirmed',
    'Enviado'    => 'badge-shipped',
    'Entregado'  => 'badge-delivered',
    'Cancelado'  => 'badge-cancelled',
];
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/pedidos.css">

<div class="orders-wrapper">
    <h2 class="orders-title">📦 Gestión de Pedidos</h2>

    <!-- Stats -->
    <div class="orders-stats">
        <a href="?ruta=admin_pedidos" class="stat-box <?php echo !$filtro_estado?'active':''; ?>">
            <span class="stat-num"><?php echo $grand_total; ?></span>
            <span class="stat-label">Todos</span>
        </a>
        <?php foreach ($estados as $e): ?>
        <a href="?ruta=admin_pedidos&estado=<?php echo urlencode($e); ?>"
           class="stat-box <?php echo $filtro_estado===$e?'active':''; ?> <?php echo $estado_class[$e]; ?>">
            <span class="stat-num"><?php echo $totales[$e]; ?></span>
            <span class="stat-label"><?php echo $e; ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="no-orders"><div style="font-size:3rem">📭</div><h3>No hay pedidos</h3></div>
    <?php else: ?>

    <?php foreach ($pedidos as $p): ?>
    <?php
        $fecha = $p['fecha'] instanceof MongoDB\BSON\UTCDateTime
            ? $p['fecha']->toDateTime()->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i')
            : 'N/A';
        $est = (string)($p['estado'] ?? 'Procesando');
        $cls = $estado_class[$est] ?? 'badge-processing';
    ?>
    <div class="order-card">
        <div class="order-card-header">
            <div>
                <span class="order-ref"># <?php echo htmlspecialchars($p['referencia']); ?></span>
                <span class="order-date"><?php echo $fecha; ?></span>
                <span class="order-user">👤 <?php echo htmlspecialchars($p['usuario']); ?></span>
            </div>
            <span class="order-badge <?php echo $cls; ?>"><?php echo $est; ?></span>
        </div>

        <div class="order-items-list">
            <?php foreach ($p['items'] as $item): ?>
            <div class="order-line">
                <span class="order-line-name"><?php echo htmlspecialchars($item['nombre']); ?> <em>x<?php echo $item['cantidad']; ?></em></span>
                <span class="order-line-price">$<?php echo number_format($item['subtotal'],2); ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="order-footer">
            <div class="order-address">📍 <?php echo htmlspecialchars($p['direccion']); ?></div>
            <div class="order-payment">
                💳 <?php echo htmlspecialchars($p['tarjeta']['tipo'] ?? ''); ?> ••••<?php echo htmlspecialchars($p['tarjeta']['last4'] ?? ''); ?>
            </div>
            <div class="order-total">Total: <strong>$<?php echo number_format($p['total'],2); ?></strong></div>

            <!-- Change status -->
            <form method="POST" class="status-form">
                <input type="hidden" name="pedido_id" value="<?php echo (string)$p['_id']; ?>">
                <select name="estado" class="status-select">
                    <?php foreach ($estados as $e): ?>
                    <option value="<?php echo $e; ?>" <?php echo $est===$e?'selected':''; ?>><?php echo $e; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="cambiar_estado" class="btn-status">Actualizar</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
