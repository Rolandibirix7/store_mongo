<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Redirect if already logged in
if (isset($_SESSION['usuario'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

require_once("./config/conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario   = trim($_POST['usuario']);
    $password  = $_POST['password'];
    $confirmar = $_POST['confirmar_password'];

    if (empty($usuario) || empty($password) || empty($confirmar)) {
        $error = "Completa todos los campos";
    } elseif (strlen($usuario) < 3) {
        $error = "El usuario debe tener al menos 3 caracteres";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
        $error = "El usuario solo puede tener letras, números y guiones bajos";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden";
    } else {
        $existe = $col_usuarios->findOne(['usuario' => ['$regex' => '^' . preg_quote($usuario) . '$', '$options' => 'i']]);
        if ($existe) {
            $error = "Ese nombre de usuario ya está en uso";
        } else {
            $col_usuarios->insertOne([
                'usuario'  => $usuario,
                'password' => md5($password),   // Note: use password_hash in production
                'rol'      => 'cliente'
            ]);
            header("Location: " . BASE_URL . "/login.php?registered=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AER Shop — Crear Cuenta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<?php if (isset($error)): ?>
    <div class="toast toast--error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="wave-container">
    <div class="wave wave-1"></div><div class="wave wave-2"></div>
    <div class="wave wave-3"></div><div class="wave wave-4"></div>
</div>
<div class="gradient-particles">
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
    <div class="particle"></div><div class="particle"></div>
</div>

<div class="login-container">
    <div class="login-card">
        <div class="card-glow"></div>
        <div class="login-header">
            <div class="gradient-icon">
                <div class="icon-wave"></div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h2>Crear Cuenta</h2>
            <p>Únete a AER Shop hoy</p>
        </div>

        <form class="login-form" method="POST">
            <div class="form-group">
                <div class="input-container">
                    <div class="input-bg"></div>
                    <input type="text" name="usuario" placeholder="Usuario (mín. 3 caracteres)" required autocomplete="username" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
                    <div class="input-wave"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-container">
                    <div class="input-bg"></div>
                    <input type="password" name="password" placeholder="Contraseña (mín. 6 caracteres)" required autocomplete="new-password">
                    <div class="input-wave"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-container">
                    <div class="input-bg"></div>
                    <input type="password" name="confirmar_password" placeholder="Confirmar contraseña" required autocomplete="new-password">
                    <div class="input-wave"></div>
                </div>
            </div>

            <button type="submit" class="gradient-button">
                <div class="button-bg"></div>
                <div class="button-content">
                    <span class="btn-text">Crear Cuenta</span>
                </div>
            </button>

            <p class="auth-link">
                ¿Ya tienes cuenta?
                <a href="<?php echo BASE_URL; ?>/login.php">Inicia sesión</a>
            </p>
        </form>
    </div>
</div>

<script src="./js/login.js"></script>
</body>
</html>
