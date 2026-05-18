<?php
require_once("../config/auth.php");
require_once("../config/conexion.php");
include("../includes/header.php");

$total_productos  = $col_productos->countDocuments();
$total_categorias = $col_categorias->countDocuments();
$total_usuarios   = $col_usuarios->countDocuments();
$total_pedidos    = $col_pedidos->countDocuments();
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dashboard.css">
<style>
.pedidos-bar{background:#1a1b23;border:1px solid rgba(255,255,255,.08);border-radius:16px;
padding:20px 28px;display:flex;align-items:center;justify-content:space-between;
margin:24px 0 40px;gap:16px;flex-wrap:wrap}
.pedidos-bar h3{margin:0;font-size:1.1rem}
.pedidos-bar p{margin:4px 0 0;color:#94a3b8;font-size:.9rem}
.btn-pedidos{background:#6366f1;color:white;padding:10px 22px;border-radius:10px;
text-decoration:none;font-weight:700;font-size:.9rem;white-space:nowrap}
.btn-pedidos:hover{background:#818cf8}

/* Select Dark Options */
#cat-filter option { background: #1a1b23; color: white; }

/* Custom Alert Modal */
.custom-confirm-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center; z-index: 9999;
    opacity: 0; pointer-events: none; transition: opacity 0.2s;
}
.custom-confirm-overlay.active { opacity: 1; pointer-events: auto; }
.custom-confirm-modal {
    background: #1a1b23; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;
    padding: 24px; max-width: 320px; width: 90%; text-align: center;
    transform: scale(0.95) translateY(10px); transition: transform 0.2s;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}
.custom-confirm-overlay.active .custom-confirm-modal { transform: scale(1) translateY(0); }
.custom-confirm-icon {
    width: 48px; height: 48px; background: rgba(239,68,68,0.1); color: #ef4444;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.custom-confirm-title { font-size: 1.1rem; font-weight: 700; color: white; margin: 0 0 8px; }
.custom-confirm-text { font-size: 0.9rem; color: #94a3b8; margin: 0 0 24px; line-height: 1.4; }
.custom-confirm-actions { display: flex; gap: 12px; }
.custom-confirm-btn {
    flex: 1; padding: 10px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer; border: none; transition: 0.2s;
}
.custom-btn-cancel { background: rgba(255,255,255,0.05); color: #f1f5f9; }
.custom-btn-cancel:hover { background: rgba(255,255,255,0.1); }
.custom-btn-delete { background: #ef4444; color: white; }
.custom-btn-delete:hover { background: #dc2626; }
</style>

<div class="container">
    <div class="dashboard-header">
        <div>
            <h1>Panel de Administración</h1>
            <p class="welcome-msg">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>
        </div>
        <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Cerrar Sesión
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_productos; ?></div>
                <div class="stat-label">Productos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_categorias; ?></div>
                <div class="stat-label">Categorías</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_usuarios; ?></div>
                <div class="stat-label">Usuarios</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
            </div>
            <div>
                <div class="stat-value"><?php echo $total_pedidos; ?></div>
                <div class="stat-label">Pedidos</div>
            </div>
        </div>
    </div>

    <div class="section-title">Acciones Rápidas</div>
    <div class="actions-grid">
        <a href="agregar_producto.php" class="action-card">
            <div class="action-icon action-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <span class="action-label">Agregar Producto</span>
            <svg class="action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="categorias.php" class="action-card">
            <div class="action-icon action-icon--purple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
            </div>
            <span class="action-label">Gestionar Categorías</span>
            <svg class="action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="registrar_administrador.php" class="action-card">
            <div class="action-icon action-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            </div>
            <span class="action-label">Registrar Administrador</span>
            <svg class="action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="<?php echo BASE_URL; ?>/index.php" class="action-card">
            <div class="action-icon action-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span class="action-label">Ver Tienda</span>
            <svg class="action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    </div>

    <!-- Pedidos bar — above products list -->
    <div class="pedidos-bar">
        <div>
            <h3>📦 Gestión de Pedidos</h3>
            <p>Ver, filtrar y actualizar estados: Procesando, Enviado, Entregado</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/index.php?ruta=admin_pedidos" class="btn-pedidos">Ver Pedidos →</a>
    </div>

    <!-- Filter bar -->
    <?php
    $selected_cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
    $filter = [];
    if (!empty($selected_cat)) {
        try {
            $cat_oid = new MongoDB\BSON\ObjectId($selected_cat);
            $filter = ['$or' => [['categoria_id' => $cat_oid], ['categoria_id' => $selected_cat]]];
        } catch(Exception $e) {
            $filter = ['categoria_id' => $selected_cat];
        }
    }
    ?>
    <div class="filter-bar" style="margin-bottom: 24px; display: flex; gap: 12px; align-items: center; background: #1a1b23; padding: 16px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
        <label for="cat-filter" style="color: #94a3b8; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Filtrar:
        </label>
        <select id="cat-filter" onchange="window.location.href='?cat='+this.value" style="padding: 10px 14px; border-radius: 8px; background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); font-family: inherit; font-size: 0.95rem; outline: none; cursor: pointer; flex: 1; max-width: 300px; transition: border-color 0.2s;">
            <option value="">Todas las categorías</option>
            <?php foreach($col_categorias->find([], ['sort' => ['nombre' => 1]]) as $c): ?>
                <option value="<?php echo $c['_id']; ?>" <?php if($selected_cat === (string)$c['_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($c['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if(!empty($selected_cat)): ?>
            <a href="dashboard.php" style="color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 600; padding: 6px 12px; border-radius: 6px; background: rgba(239,68,68,0.1); transition: 0.2s;">Quitar filtro</a>
        <?php endif; ?>
    </div>

    <!-- Products list -->
    <div class="section-title" style="margin-top:0;">
        Productos
        <a href="agregar_producto.php" class="section-action">+ Nuevo</a>
    </div>
    <div class="productos">
    <?php 
    $productos_cursor = $col_productos->find($filter, ['sort' => ['_id' => -1]]);
    $hay_productos = false;
    foreach($productos_cursor as $row):
        $hay_productos = true;
        $cat        = $col_categorias->findOne(['_id' => $row['categoria_id']]);
        $cat_nombre = $cat ? $cat['nombre'] : 'Sin categoría';
        $prod_id    = (string)$row['_id'];
    ?>
        <div class="card">
            <img src="../img/<?php echo htmlspecialchars($row['imagen']); ?>" alt="">
            <div class="card-content">
                <h3><?php echo htmlspecialchars($row['nombre']); ?></h3>
                <p class="categoria"><?php echo htmlspecialchars($cat_nombre); ?></p>
                <p class="precio">$<?php echo number_format((float)$row['precio'], 2); ?></p>
                <div class="acciones">
                    <?php $cat_param = !empty($selected_cat) ? '&cat=' . urlencode($selected_cat) : ''; ?>
                    <a href="editar_producto.php?id=<?php echo $prod_id; ?><?php echo $cat_param; ?>" class="btn-editar">Editar</a>
                    <a href="eliminar_producto.php?id=<?php echo $prod_id; ?><?php echo $cat_param; ?>" class="btn-eliminar"
                       onclick="confirmarEliminar(event, this.href)">Eliminar</a>
                </div>
            </div>
        </div>
    <?php endforeach; 
    if (!$hay_productos): ?>
        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #1a1b23; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; color: #94a3b8;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="margin-bottom: 12px; opacity: 0.5;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p style="margin: 0; font-size: 1.1rem; font-weight: 500; color: white;">No se encontraron productos</p>
            <p style="margin: 4px 0 0; font-size: 0.9rem;">No hay productos en esta categoría.</p>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Modal Confirmar Eliminar -->
<div class="custom-confirm-overlay" id="deleteModal">
    <div class="custom-confirm-modal">
        <div class="custom-confirm-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
        </div>
        <h3 class="custom-confirm-title">¿Eliminar producto?</h3>
        <p class="custom-confirm-text">Esta acción no se puede deshacer.</p>
        <div class="custom-confirm-actions">
            <button class="custom-confirm-btn custom-btn-cancel" onclick="cerrarModal()">Cancelar</button>
            <a href="#" class="custom-confirm-btn custom-btn-delete" id="btnConfirmDelete" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Sí, eliminar</a>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(e, url) {
    e.preventDefault();
    document.getElementById('btnConfirmDelete').href = url;
    document.getElementById('deleteModal').classList.add('active');
}
function cerrarModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
</script>

<?php include("../includes/footer.php"); ?>
