<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/login.php'); exit;
}
$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    header('Location: ' . BASE_URL . '/index.php?ruta=carrito'); exit;
}

$user_id  = $_SESSION['user_id'];
$subtotal = 0;
foreach ($carrito as $item) $subtotal += $item['precio'] * $item['cantidad'];
$envio = 3.50;
$total = $subtotal + $envio;

$tarjetas = iterator_to_array($col_tarjetas->find(['usuario_id' => $user_id]));

$departamentos = [
    'Ahuachapán','Santa Ana','Sonsonate','Chalatenango','La Libertad',
    'San Salvador','Cuscatlán','La Paz','Cabañas','San Vicente',
    'Usulután','San Miguel','Morazán','La Unión'
];
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/checkout.css">

<div class="checkout-wrapper">
    <h2 class="checkout-title">💳 Finalizar Compra</h2>
    <div class="checkout-layout">

        <!-- LEFT -->
        <div class="checkout-left">

            <?php if (!empty($tarjetas)): ?>
            <div class="section-card">
                <h3>Mis Tarjetas Guardadas</h3>
                <div class="saved-cards">
                    <?php foreach ($tarjetas as $t):
                        $num   = (string)$t['numero'];
                        $last4 = substr(str_replace(' ','',$num), -4);
                        $tipo  = (string)$t['tipo'];
                        $icon  = strtolower($tipo)==='visa' ? '💳' : (strtolower($tipo)==='mastercard' ? '🔴' : '💳');
                    ?>
                    <label class="saved-card-label">
                        <input type="radio" name="tarjeta_guardada" value="<?php echo (string)$t['_id']; ?>">
                        <div class="saved-card-box">
                            <span class="card-icon-big"><?php echo $icon; ?></span>
                            <div>
                                <div class="card-tipo"><?php echo htmlspecialchars($tipo); ?></div>
                                <div class="card-num">•••• •••• •••• <?php echo $last4; ?></div>
                                <div class="card-holder"><?php echo htmlspecialchars($t['titular']); ?></div>
                            </div>
                            <div class="card-exp">Vence: <?php echo htmlspecialchars($t['vencimiento']); ?></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <button class="btn-new-card" id="toggleNewCard">+ Usar otra tarjeta</button>
            </div>
            <?php endif; ?>

            <div class="section-card" id="newCardSection" <?php echo !empty($tarjetas)?'style="display:none"':''; ?>>
                <h3><?php echo empty($tarjetas)?'Datos de Pago':'Nueva Tarjeta'; ?></h3>
                <div class="card-visual">
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
                <div class="form-group">
                    <label>Número de tarjeta</label>
                    <input type="text" id="cardNum" maxlength="19" placeholder="1234 5678 9012 3456" oninput="formatCardNum(this)">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Vencimiento</label>
                        <input type="text" id="cardExp" maxlength="5" placeholder="MM/AA" oninput="formatExp(this)">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" id="cardCvv" maxlength="4" placeholder="123" oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                </div>
                <div class="form-group">
                    <label>Nombre del titular</label>
                    <input type="text" id="cardHolder" placeholder="Como aparece en la tarjeta"
                           oninput="document.getElementById('cvHolder').textContent=this.value.toUpperCase()||'TU NOMBRE'">
                </div>
                <div class="form-group">
                    <label>Tipo de tarjeta</label>
                    <select id="cardTipo">
                        <option value="VISA">VISA</option>
                        <option value="Mastercard">Mastercard</option>
                        <option value="American Express">American Express</option>
                    </select>
                </div>
                <label class="check-save">
                    <input type="checkbox" id="saveCard"> Guardar tarjeta para futuras compras
                </label>
            </div>

            <div class="section-card">
                <h3>Dirección de envío</h3>
                <div class="form-group">
                    <label>Departamento</label>
                    <select id="departamento">
                        <option value="">-- Selecciona tu departamento --</option>
                        <?php foreach($departamentos as $d): ?>
                        <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dirección exacta</label>
                    <input type="text" id="direccion" placeholder="Colonia, calle, número de casa">
                </div>
                <div class="form-group">
                    <label>Ciudad / Municipio</label>
                    <input type="text" id="ciudad" placeholder="Municipio">
                </div>
            </div>
        </div>

        <!-- RIGHT: summary -->
        <div class="checkout-right">
            <div class="section-card">
                <h3>Tu pedido</h3>
                <?php foreach ($carrito as $item): ?>
                <div class="order-item">
                    <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($item['imagen']); ?>" alt="">
                    <div class="order-item-info">
                        <div><?php echo htmlspecialchars($item['nombre']); ?></div>
                        <div class="order-qty">x<?php echo $item['cantidad']; ?></div>
                    </div>
                    <div class="order-item-price">$<?php echo number_format($item['precio']*$item['cantidad'],2); ?></div>
                </div>
                <?php endforeach; ?>
                <div class="summary-divider"></div>
                <div class="summary-line"><span>Subtotal</span><span>$<?php echo number_format($subtotal,2); ?></span></div>
                <div class="summary-line"><span>Envío</span><span>$<?php echo number_format($envio,2); ?></span></div>
                <div class="summary-line total-line"><span>Total</span><span>$<?php echo number_format($total,2); ?></span></div>
                <button class="btn-pay" id="btnPagar" onclick="procesarPago()">
                    🔒 Pagar $<?php echo number_format($total,2); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="pay-modal" id="payModal">
    <div class="pay-modal-box">
        <div class="pay-spinner" id="paySpinner">
            <div class="spinner"></div>
            <p>Procesando pago...</p>
        </div>
        <div class="pay-success" id="paySuccess" style="display:none">
            <div class="success-icon">✅</div>
            <h3>¡Pago exitoso!</h3>
            <p id="paySuccessMsg"></p>
            <a href="<?php echo BASE_URL; ?>/index.php?ruta=mis_pedidos" class="btn-primary">Ver mis pedidos</a>
        </div>
        <div class="pay-error" id="payError" style="display:none">
            <div class="error-icon">❌</div>
            <h3>Pago rechazado</h3>
            <p id="payErrorMsg"></p>
            <button onclick="cerrarModal()" class="btn-secondary">Intentar de nuevo</button>
        </div>
    </div>
</div>

<script>
const BASE = '<?php echo BASE_URL; ?>';
const toggleBtn = document.getElementById('toggleNewCard');
const newSection = document.getElementById('newCardSection');
if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        newSection.style.display = newSection.style.display === 'none' ? 'block' : 'none';
        document.querySelectorAll('input[name="tarjeta_guardada"]').forEach(r => r.checked = false);
    });
}
function formatCardNum(input) {
    let v = input.value.replace(/\D/g,'').substring(0,16);
    input.value = v.replace(/(.{4})/g,'$1 ').trim();
    document.getElementById('cvNum').textContent = input.value || '•••• •••• •••• ••••';
    const b = document.getElementById('cvBrand');
    if(v.startsWith('4')) b.textContent='VISA';
    else if(v.startsWith('5')) b.textContent='MC';
    else if(v.startsWith('3')) b.textContent='AMEX';
    else b.textContent='CARD';
}
function formatExp(input) {
    let v = input.value.replace(/\D/g,'');
    if(v.length>=2) v=v.substring(0,2)+'/'+v.substring(2,4);
    input.value=v;
    document.getElementById('cvExp').textContent=v||'MM/AA';
}
function procesarPago() {
    const selectedSaved = document.querySelector('input[name="tarjeta_guardada"]:checked');
    let cardData = {};
    if (selectedSaved) {
        cardData = { tarjeta_id: selectedSaved.value };
    } else {
        const num=document.getElementById('cardNum').value.replace(/\s/g,'');
        const exp=document.getElementById('cardExp').value;
        const cvv=document.getElementById('cardCvv').value;
        const holder=document.getElementById('cardHolder').value;
        const tipo=document.getElementById('cardTipo').value;
        const save=document.getElementById('saveCard')?.checked;
        if(num.length<16||!exp||cvv.length<3||!holder){alert('Completa los datos de la tarjeta.');return;}
        cardData={numero:num,vencimiento:exp,cvv,titular:holder,tipo,guardar:save};
    }
    const dep  = document.getElementById('departamento').value;
    const dir  = document.getElementById('direccion').value;
    const ciu  = document.getElementById('ciudad').value;
    if(!dep||!dir||!ciu){alert('Ingresa tu departamento y dirección completa.');return;}

    document.getElementById('payModal').style.display='flex';
    document.getElementById('btnPagar').disabled=true;
    const fd=new FormData();
    fd.append('action','pagar');
    fd.append('departamento',dep);
    fd.append('direccion',dir+', '+ciu+', '+dep);
    fd.append('ciudad',ciu);
    fd.append('card_data',JSON.stringify(cardData));
    setTimeout(()=>{
        fetch(BASE+'/pago_action.php',{method:'POST',body:fd})
            .then(r=>r.json()).then(d=>{
                document.getElementById('paySpinner').style.display='none';
                if(d.ok){
                    document.getElementById('paySuccess').style.display='block';
                    document.getElementById('paySuccessMsg').textContent='Pedido #'+d.pedido_id+' confirmado. Tu compra está en camino 🚀';
                } else {
                    document.getElementById('payError').style.display='block';
                    document.getElementById('payErrorMsg').textContent=d.msg||'Error al procesar.';
                    document.getElementById('btnPagar').disabled=false;
                }
            });
    },2500);
}
function cerrarModal(){
    document.getElementById('payModal').style.display='none';
    document.getElementById('paySpinner').style.display='block';
    document.getElementById('paySuccess').style.display='none';
    document.getElementById('payError').style.display='none';
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
