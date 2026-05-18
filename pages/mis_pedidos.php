<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/login.php'); exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Cancelar pedido
if (isset($_POST['cancelar_pedido'])) {
    try {
        $oid = new MongoDB\BSON\ObjectId($_POST['pedido_id']);
        $p   = $col_pedidos->findOne(['_id' => $oid, 'usuario_id' => $user_id]);
        if ($p && in_array((string)$p['estado'], ['Procesando','Confirmado'])) {
            $col_pedidos->updateOne(['_id' => $oid], ['$set' => ['estado' => 'Cancelado']]);
            $msg = 'success:Pedido cancelado correctamente.';
        } else {
            $est = $p ? (string)$p['estado'] : 'desconocido';
            $msg = 'error:No se puede cancelar un pedido en estado "' . $est . '".';
        }
    } catch(Exception $e) { $msg = 'error:Error al cancelar.'; }
}

$pedidos = iterator_to_array(
    $col_pedidos->find(['usuario_id' => $user_id], ['sort' => ['fecha' => -1]])
);

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
    <h2 class="orders-title">📦 Mis Pedidos</h2>

    <?php if ($msg):
        [$tipo, $texto] = explode(':', $msg, 2); ?>
    <div class="alert-<?php echo $tipo; ?>" style="margin-bottom:20px">
        <?php echo htmlspecialchars($texto); ?>
    </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
    <div class="no-orders">
        <div style="font-size:3rem">📭</div>
        <h3>Aún no tienes pedidos</h3>
        <a href="<?php echo BASE_URL; ?>/index.php" class="btn-primary">Explorar tienda</a>
    </div>
    <?php else: ?>

    <?php foreach ($pedidos as $p):
        $fecha = $p['fecha'] instanceof MongoDB\BSON\UTCDateTime
            ? $p['fecha']->toDateTime()->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i')
            : 'N/A';
        $est           = (string)($p['estado'] ?? 'Procesando');
        $cls           = $estado_class[$est] ?? 'badge-processing';
        $pedido_id_str = (string)$p['_id'];
        $puede_cancelar = in_array($est, ['Procesando','Confirmado']);
    ?>
    <div class="order-card">
        <div class="order-card-header">
            <div>
                <span class="order-ref"># <?php echo htmlspecialchars($p['referencia']); ?></span>
                <span class="order-date"><?php echo $fecha; ?></span>
            </div>
            <span class="order-badge <?php echo $cls; ?>"><?php echo $est; ?></span>
        </div>

        <div class="order-items-list">
            <?php foreach ($p['items'] as $item): ?>
            <div class="order-line">
                <span class="order-line-name">
                    <?php echo htmlspecialchars($item['nombre']); ?> <em>x<?php echo $item['cantidad']; ?></em>
                </span>
                <span class="order-line-price">$<?php echo number_format($item['subtotal'], 2); ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="order-footer">
            <div class="order-address">📍 <?php echo htmlspecialchars($p['direccion'] ?? 'N/A'); ?></div>
            <div class="order-payment">
                💳 <?php echo htmlspecialchars($p['tarjeta']['tipo'] ?? ''); ?>
                ••••<?php echo htmlspecialchars($p['tarjeta']['last4'] ?? ''); ?>
            </div>
            <div class="order-total">Total: <strong>$<?php echo number_format($p['total'], 2); ?></strong></div>

            <?php if ($puede_cancelar): ?>
            <form method="POST" style="margin-left:auto"
                  onsubmit="return confirm('¿Seguro que deseas cancelar este pedido?')">
                <input type="hidden" name="pedido_id" value="<?php echo $pedido_id_str; ?>">
                <button type="submit" name="cancelar_pedido" class="btn-cancel-order">
                    ✕ Cancelar pedido
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
