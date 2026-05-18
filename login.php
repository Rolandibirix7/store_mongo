<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "/index.php");
    }
    exit();
}

require_once("./config/conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = trim($_POST['usuario']);
    $rawPassword = $_POST['password'] ?? '';
    $md5Password = md5($rawPassword);

    $data = $col_usuarios->findOne(['usuario' => $user]);

    if ($data && isset($data['password']) && (
            password_verify($rawPassword, $data['password']) ||
            $data['password'] === $md5Password
        )) {
        $_SESSION['usuario'] = $data['usuario'];
        $_SESSION['rol']     = $data['rol'];
        $_SESSION['user_id'] = (string)$data['_id'];

        if ($data['rol'] === 'admin') {
            header("Location: " . BASE_URL . "/admin/dashboard.php");
        } elseif ($data['rol'] === 'repartidor') {
            header("Location: " . BASE_URL . "/index.php?ruta=repartidor_panel");
        } else {
            header("Location: " . BASE_URL . "/index.php");
        }
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AER Shop — Iniciar Sesión</title>
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
        <div class="wave wave-1"></div>
        <div class="wave wave-2"></div>
        <div class="wave wave-3"></div>
        <div class="wave wave-4"></div>
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
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <form class="login-form" id="loginForm" method="POST" novalidate>
                <div class="form-group">
                    <div class="input-container">
                        <div class="input-bg"></div>
                        <input type="text" id="usuario" name="usuario" required autocomplete="username" placeholder="Usuario">
                        <div class="input-wave"></div>
                    </div>
                    <span class="error-message" id="usuarioError"></span>
                </div>

                <div class="form-group">
                    <div class="input-container password-container">
                        <div class="input-bg"></div>
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Contraseña">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <div class="toggle-bg"></div>
                            <div class="toggle-icon">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </div>
                        </button>
                        <div class="input-wave"></div>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <button type="submit" name="login" class="gradient-button">
                    <div class="button-bg"></div>
                    <div class="button-content">
                        <span class="btn-text">Iniciar Sesión</span>
                    </div>
                </button>

                <p class="auth-link">
                    ¿No tienes cuenta?
                    <a href="<?php echo BASE_URL; ?>/registro.php">Regístrate gratis</a>
                </p>
            </form>
        </div>
    </div>

    <script src="./js/login.js"></script>
</body>
</html>
