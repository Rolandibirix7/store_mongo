<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'repartidor') {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Update delivery status
if (isset($_POST['actualizar_estado'])) {
    try {
        $oid    = new MongoDB\BSON\ObjectId($_POST['pedido_id']);
        $estado = $_POST['estado'];
        // Only allow repartidor to mark as Enviado or Entregado
        if (in_array($estado, ['Enviado','Entregado'])) {
            $col_pedidos->updateOne(['_id' => $oid], ['$set' => ['estado' => $estado, 'repartidor_id' => $user_id]]);
            $msg = 'success:Estado actualizado.';
        }
    } catch(Exception $e) { $msg = 'error:Error al actualizar.'; }
}

// Get repartidor's zone
$repartidor = $col_usuarios->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);
$zona = $repartidor ? (string)($repartidor['zona'] ?? '') : '';

// Update zona
if (isset($_POST['guardar_zona'])) {
    $nueva_zona = trim($_POST['zona']);
    $col_usuarios->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($user_id)],
        ['$set' => ['zona' => $nueva_zona]]
    );
    $zona = $nueva_zona;
    $msg = 'success:Zona actualizada a ' . $nueva_zona . '.';
}

$departamentos = [
    'Ahuachapán','Santa Ana','Sonsonate','Chalatenango','La Libertad',
    'San Salvador','Cuscatlán','La Paz','Cabañas','San Vicente',
    'Usulután','San Miguel','Morazán','La Unión'
];

// Get pedidos in this zone (Confirmado or Enviado)
$pedidos = [];
if ($zona) {
    $pedidos = iterator_to_array(
        $col_pedidos->find(
            ['departamento' => $zona, 'estado' => ['$in' => ['Confirmado','Enviado']]],
            ['sort' => ['fecha' => 1]]
        )
    );
}

$estado_class = [
    'Confirmado' => 'badge-confirmed',
    'Enviado'    => 'badge-shipped',
    'Entregado'  => 'badge-delivered',
];

$total_entregados = $zona
    ? $col_pedidos->countDocuments(['departamento' => $zona, 'estado' => 'Entregado', 'repartidor_id' => $user_id])
    : 0;
$total_pendientes = count($pedidos);
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/pedidos.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/repartidor.css">

<div class="orders-wrapper">
    <h2 class="orders-title">🚚 Panel de Repartidor</h2>

    <?php if ($msg):
        [$tipo, $texto] = explode(':', $msg, 2); ?>
    <div class="alert-<?php echo $tipo; ?>" style="margin-bottom:20px">
        <?php echo htmlspecialchars($texto); ?>
    </div>
    <?php endif; ?>

    <!-- Zone selector -->
    <div class="zone-card">
        <div class="zone-header">
            <div>
                <h3>📍 Mi Zona de Entrega</h3>
                <p>Los pedidos que ves son los de tu departamento asignado</p>
            </div>
            <?php if ($zona): ?>
            <div class="zone-badge"><?php echo htmlspecialchars($zona); ?></div>
            <?php endif; ?>
        </div>
        <form method="POST" class="zone-form">
            <select name="zona" class="status-select zone-select">
                <option value="">-- Selecciona tu zona --</option>
                <?php foreach ($departamentos as $d): ?>
                <option value="<?php echo $d; ?>" <?php echo $zona===$d?'selected':''; ?>><?php echo $d; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="guardar_zona" class="btn-status">Guardar Zona</button>
        </form>
    </div>

    <!-- Stats -->
    <?php if ($zona): ?>
    <div class="orders-stats" style="margin-bottom:28px">
        <div class="stat-box active">
            <span class="stat-num"><?php echo $total_pendientes; ?></span>
            <span class="stat-label">Por entregar</span>
        </div>
        <div class="stat-box badge-delivered">
            <span class="stat-num"><?php echo $total_entregados; ?></span>
            <span class="stat-label">Entregados por mí</span>
        </div>
    </div>

    <?php if (empty($pedidos)): ?>
    <div class="no-orders">
        <div style="font-size:3rem">✅</div>
        <h3>No hay pedidos pendientes en <?php echo htmlspecialchars($zona); ?></h3>
        <p style="color:var(--text-muted)">Los nuevos pedidos de esta zona aparecerán aquí automáticamente.</p>
    </div>
    <?php else: ?>

    <h3 style="margin:0 0 16px;font-size:1rem;color:var(--text-soft)">
        Pedidos en <?php echo htmlspecialchars($zona); ?> (<?php echo count($pedidos); ?>)
    </h3>

    <?php foreach ($pedidos as $p):
        $fecha = $p['fecha'] instanceof MongoDB\BSON\UTCDateTime
            ? $p['fecha']->toDateTime()->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i')
            : 'N/A';
        $est = (string)($p['estado'] ?? 'Confirmado');
        $cls = $estado_class[$est] ?? 'badge-confirmed';
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
            <div class="order-total">Total: <strong>$<?php echo number_format($p['total'],2); ?></strong></div>

            <form method="POST" class="status-form">
                <input type="hidden" name="pedido_id" value="<?php echo (string)$p['_id']; ?>">
                <select name="estado" class="status-select">
                    <option value="Enviado" <?php echo $est==='Enviado'?'selected':''; ?>>Enviado</option>
                    <option value="Entregado" <?php echo $est==='Entregado'?'selected':''; ?>>Entregado</option>
                </select>
                <button type="submit" name="actualizar_estado" class="btn-status">Actualizar</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
