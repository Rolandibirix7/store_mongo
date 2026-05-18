<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AER Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/buscar.css">
</head>
<body>

<header id="main-header">
    <div class="header-logo">
        <a href="<?php echo BASE_URL; ?>/index.php" class="logo-link">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
            <span class="logo-text">AER Shop</span>
        </a>
    </div>

    <nav class="header-nav">
        

        <a href="<?php echo BASE_URL; ?>/index.php" class="nav-link">Inicio</a>
        <a href="<?php echo BASE_URL; ?>/index.php#categorias" class="nav-link">Productos</a>

        <!-- Pedidos link (logged in users) -->
        <?php if (isset($_SESSION['usuario'])): ?>
        <a href="<?php echo BASE_URL; ?>/index.php?ruta=mis_pedidos" class="nav-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            Ver Pedidos
        </a>
        <?php endif; ?>

        <div class="busqueda-container">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="busqueda" class="busqueda-input" placeholder="Buscar productos..." autocomplete="off">
            <div id="resultado-busqueda"></div>
        </div>

        <!-- Cart icon -->
        <a href="<?php echo BASE_URL; ?>/index.php?ruta=carrito" class="nav-link cart-link" title="Mi Carrito">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span id="cart-badge" class="cart-badge">0</span>
        </a>

        <?php if (isset($_SESSION['usuario'])): ?>
            <?php if ($_SESSION['rol'] === 'repartidor'): ?>
                <a href="<?php echo BASE_URL; ?>/index.php?ruta=repartidor_panel" class="nav-link nav-link--admin" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#10b981">
                    🚚 Mis entregas
                </a>
            <?php endif; ?>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="nav-link nav-link--admin">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    Admin
                </a>
            <?php endif; ?>
            <div class="user-menu">
                <button class="user-btn" id="userMenuBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="chevron">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-info">
                        <span class="dropdown-user"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                        <span class="dropdown-role"><?php echo ucfirst($_SESSION['rol']); ?></span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/index.php?ruta=mis_pedidos" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        Mis Pedidos
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?ruta=mis_tarjetas" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        Mis Tarjetas
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="dropdown-item dropdown-item--danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/login.php" class="nav-link nav-link--cta">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Iniciar Sesión
            </a>
        <?php endif; ?>
    </nav>

    <button class="menu-toggle" id="menuToggle" aria-label="Menú">
        <span></span><span></span><span></span>
    </button>
</header>

<script>
// Search
const inputBusqueda = document.getElementById("busqueda");
const resultados    = document.getElementById("resultado-busqueda");
inputBusqueda.addEventListener("keyup", function(){
    const texto = this.value.trim();
    if(texto === ""){ resultados.innerHTML = ""; resultados.style.display = "none"; return; }
    fetch("<?php echo BASE_URL; ?>/buscar.php?buscar=" + encodeURIComponent(texto))
        .then(r => r.text()).then(data => {
            resultados.innerHTML = data;
            resultados.style.display = data.trim() !== "" ? "block" : "none";
        });
});
document.addEventListener("click", function(e){
    if(!resultados.contains(e.target) && e.target !== inputBusqueda){
        resultados.style.display = "none";
    }
});

// User dropdown
const userBtn = document.getElementById("userMenuBtn");
const userDropdown = document.getElementById("userDropdown");
if(userBtn){
    userBtn.addEventListener("click", function(e){
        e.stopPropagation();
        userDropdown.classList.toggle("open");
    });
    document.addEventListener("click", function(){ userDropdown.classList.remove("open"); });
}

// Mobile menu toggle
const menuToggle = document.getElementById("menuToggle");
const headerNav  = document.querySelector(".header-nav");
if(menuToggle){
    menuToggle.addEventListener("click", function(){
        headerNav.classList.toggle("open");
        menuToggle.classList.toggle("active");
    });
}

// Scroll effect
const header = document.getElementById("main-header");
window.addEventListener("scroll", function(){
    header.classList.toggle("scrolled", window.scrollY > 20);
});

// Cart badge
function actualizarBadge() {
    fetch(BASE_URL + '/carrito_action.php?action=count')
        .then(r => r.json()).then(d => {
            const b = document.getElementById('cart-badge');
            if (b) { b.textContent = d.count; b.style.display = d.count > 0 ? 'flex' : 'none'; }
        }).catch(()=>{});
}
const BASE_URL = '<?php echo BASE_URL; ?>';
actualizarBadge();
</script>
