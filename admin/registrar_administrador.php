<?php
session_start();
include("../config/auth.php");
require_once("../config/conexion.php");
include("../includes/header.php");

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
    $usuario = trim($_POST['usuario']);
    $pass    = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol     = in_array($_POST['rol'], ['admin','repartidor']) ? $_POST['rol'] : 'admin';

    if ($col_usuarios->findOne(['usuario' => $usuario])) {
        $mensaje = "<p class='error'>Error: El usuario ya existe.</p>";
    } else {
        $doc = ['usuario' => $usuario, 'password' => $pass, 'rol' => $rol];
        if ($rol === 'repartidor' && !empty($_POST['zona'])) {
            $doc['zona'] = trim($_POST['zona']);
        }
        $r = $col_usuarios->insertOne($doc);
        $label = $rol === 'repartidor' ? 'Repartidor' : 'Administrador';
        $mensaje = $r->getInsertedCount() > 0
            ? "<p class='success'>$label registrado correctamente.</p>"
            : "<p class='error'>Error al registrar.</p>";
    }
}

$departamentos = [
    'Ahuachapán','Santa Ana','Sonsonate','Chalatenango','La Libertad',
    'San Salvador','Cuscatlán','La Paz','Cabañas','San Vicente',
    'Usulután','San Miguel','Morazán','La Unión'
];
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/AD_forms.css">
<style>
.role-tabs { display:flex; gap:8px; margin-bottom:20px; }
.role-tab  { flex:1; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.1);
             background:rgba(255,255,255,.04); color:#94a3b8; cursor:pointer; text-align:center;
             font-weight:600; font-size:.9rem; transition:all .2s; }
.role-tab.active-admin { background:rgba(99,102,241,.2); border-color:rgba(99,102,241,.5); color:#818cf8; }
.role-tab.active-rep   { background:rgba(16,185,129,.2); border-color:rgba(16,185,129,.5); color:#10b981; }
#zona-group { display:none; }
.form-select {
    width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12);
    border-radius:10px; color:#f1f5f9; padding:12px 14px; font-size:.95rem;
    margin-bottom:15px; -webkit-appearance:none; appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:calc(100% - 12px) center;
}
.form-select option { background:#1a1b23; color:#f1f5f9; }
</style>

<div class="container">
    <div class="form-container">
        <h2>Registrar Usuario del Sistema</h2>
        <?php echo $mensaje; ?>

        <form method="POST" class="form" id="regForm">
            <input type="hidden" name="rol" id="rolInput" value="admin">

            <div class="role-tabs">
                <div class="role-tab active-admin" onclick="setRol('admin',this)">
                    🛡️ Administrador
                </div>
                <div class="role-tab" onclick="setRol('repartidor',this)">
                    🚚 Repartidor
                </div>
            </div>

            <input type="text" name="usuario" placeholder="Nombre de usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>

            <div id="zona-group">
                <p style="color:#94a3b8;font-size:.85rem;margin:0 0 8px">Zona de entrega del repartidor:</p>
                <select name="zona" class="form-select">
                    <option value="">-- Selecciona zona --</option>
                    <?php foreach($departamentos as $d): ?>
                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <p style="color:#64748b;font-size:.8rem;margin-bottom:15px;" id="roleNote">
                * Tendrá acceso total al panel de administración.
            </p>
            <button type="submit" name="registrar" id="submitBtn">Registrar Administrador</button>
        </form>
    </div>
</div>

<script>
function setRol(rol, el) {
    document.getElementById('rolInput').value = rol;
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active-admin','active-rep'));
    if (rol === 'admin') {
        el.classList.add('active-admin');
        document.getElementById('zona-group').style.display = 'none';
        document.getElementById('roleNote').textContent = '* Tendrá acceso total al panel de administración.';
        document.getElementById('submitBtn').textContent = 'Registrar Administrador';
    } else {
        el.classList.add('active-rep');
        document.getElementById('zona-group').style.display = 'block';
        document.getElementById('roleNote').textContent = '* Solo verá los pedidos de su departamento asignado.';
        document.getElementById('submitBtn').textContent = 'Registrar Repartidor';
    }
}
</script>

<?php include("../includes/footer.php"); ?>
