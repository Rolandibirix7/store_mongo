<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/login.php'); exit;
}

$user_id  = $_SESSION['user_id'];
$success  = '';
$error    = '';

// Handle add card
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_tarjeta'])) {
    $num    = preg_replace('/\D/','',$_POST['numero'] ?? '');
    $exp    = trim($_POST['vencimiento'] ?? '');
    $holder = trim($_POST['titular'] ?? '');
    $tipo   = $_POST['tipo'] ?? 'VISA';

    if (strlen($num) < 16 || !$exp || !$holder) {
        $error = 'Todos los campos son obligatorios y el número debe tener 16 dígitos.';
    } else {
        $formatted = implode(' ', str_split($num, 4));
        $col_tarjetas->insertOne([
            'usuario_id'  => $user_id,
            'numero'      => $formatted,
            'vencimiento' => $exp,
            'titular'     => $holder,
            'tipo'        => $tipo,
            'creada_en'   => new MongoDB\BSON\UTCDateTime(),
        ]);
        $success = '¡Tarjeta agregada correctamente!';
    }
}

// Handle delete
if (isset($_GET['eliminar'])) {
    try {
        $col_tarjetas->deleteOne([
            '_id'        => new MongoDB\BSON\ObjectId($_GET['eliminar']),
            'usuario_id' => $user_id,
        ]);
        $success = 'Tarjeta eliminada.';
    } catch(Exception $e) {}
}

$tarjetas = iterator_to_array($col_tarjetas->find(['usuario_id' => $user_id]));
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/checkout.css">

<div class="checkout-wrapper">
    <h2 class="checkout-title">💳 Mis Tarjetas</h2>

    <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert-error"><?php echo $error; ?></div><?php endif; ?>

    <!-- Existing cards -->
    <?php if (!empty($tarjetas)): ?>
    <div class="section-card">
        <h3>Tarjetas guardadas</h3>
        <div class="saved-cards">
            <?php foreach ($tarjetas as $t):
                $num   = (string)$t['numero'];
                $last4 = substr(str_replace(' ','',$num), -4);
                $tipo  = (string)$t['tipo'];
                $icon  = strtolower($tipo) === 'visa' ? '💳' : (strtolower($tipo) === 'mastercard' ? '🔴' : '💳');
            ?>
            <div class="saved-card-box" style="position:relative">
                <span class="card-icon-big"><?php echo $icon; ?></span>
                <div>
                    <div class="card-tipo"><?php echo htmlspecialchars($tipo); ?></div>
                    <div class="card-num">•••• •••• •••• <?php echo $last4; ?></div>
                    <div class="card-holder"><?php echo htmlspecialchars($t['titular']); ?></div>
                </div>
                <div class="card-exp">Vence: <?php echo htmlspecialchars($t['vencimiento']); ?></div>
                <a href="?eliminar=<?php echo (string)$t['_id']; ?>"
                   onclick="return confirm('¿Eliminar esta tarjeta?')"
                   class="btn-remove-card" title="Eliminar">✕</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add new card -->
    <div class="section-card">
        <h3>Agregar Nueva Tarjeta</h3>

        <div class="card-visual" id="cardVisual">
            <div class="card-visual-top">
                <span class="chip">▪▪▪</span>
                <span class="card-brand" id="cvBrand">CARD</span>
            </div>
            <div class="card-visual-num" id="cvNum">•••• •••• •••• ••••</div>
            <div class="card-visual-bottom">
                <div><div class="cv-label">TITULAR</div><div id="cvHolder">TU NOMBRE</div></div>
                <div><div class="cv-label">VENCE</div><div id="cvExp">MM/AA</div></div>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Número de tarjeta</label>
                <input type="text" name="numero" id="cardNum" maxlength="19"
                       placeholder="1234 5678 9012 3456" oninput="formatCardNum(this)" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Vencimiento</label>
                    <input type="text" name="vencimiento" id="cardExp" maxlength="5"
                           placeholder="MM/AA" oninput="formatExp(this)" required>
                </div>
                <div class="form-group">
                    <label>CVV (no se guarda)</label>
                    <input type="text" id="cardCvv" maxlength="4" placeholder="123"
                           oninput="this.value=this.value.replace(/\D/g,'')">
                </div>
            </div>
            <div class="form-group">
                <label>Nombre del titular</label>
                <input type="text" name="titular" id="cardHolder" placeholder="Como aparece en la tarjeta"
                       oninput="document.getElementById('cvHolder').textContent=this.value.toUpperCase()||'TU NOMBRE'" required>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo" id="cardTipo">
                    <option value="VISA">VISA</option>
                    <option value="Mastercard">Mastercard</option>
                    <option value="American Express">American Express</option>
                </select>
            </div>
            <button type="submit" name="agregar_tarjeta" class="btn-pay">+ Guardar Tarjeta</button>
        </form>
    </div>
</div>

<script>
function formatCardNum(input) {
    let v = input.value.replace(/\D/g,'').substring(0,16);
    input.value = v.replace(/(.{4})/g,'$1 ').trim();
    document.getElementById('cvNum').textContent = input.value || '•••• •••• •••• ••••';
    const brand = document.getElementById('cvBrand');
    if (v.startsWith('4')) brand.textContent = 'VISA';
    else if (v.startsWith('5')) brand.textContent = 'MC';
    else if (v.startsWith('3')) brand.textContent = 'AMEX';
    else brand.textContent = 'CARD';
}
function formatExp(input) {
    let v = input.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.substring(0,2)+'/'+v.substring(2,4);
    input.value = v;
    document.getElementById('cvExp').textContent = v || 'MM/AA';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
