<?php
/**
 * Vista de Login - Proyecto Bee
 * Autenticacion minimalista con animaciones GSAP
 */

session_start();

require_once '../../includes/functions/auth.functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña';
    } else {
        $result = login($username, $password);
        if ($result['success']) {
            header('Location: ../dashboard/index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

if (isLoggedIn()) {
    header('Location: ../dashboard/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bee - Iniciar Sesion</title>
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/login.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <img src="../../assets/img/logo.jpg" alt="Bee" class="logo-icon" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h1 class="title">Bee</h1>
                <p class="subtitle">Sistema de Gestion</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label for="username">Usuario o Email</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Ingrese su usuario"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Ingrese su contraseña"
                        required
                    >
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Recordarme
                    </label>
                    <a href="forgot-password.php" class="forgot-link">¿Olvido su contraseña?</a>
                </div>

                <button type="submit" class="login-button">
                    <span class="button-text">Iniciar Sesion</span>
                    <span class="button-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20">
                            <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            </form>

            <div class="loading-message">
                <div class="loading-spinner"></div>
                <p>Iniciando sesion...</p>
            </div>

            <div class="login-footer">
                <p>¿No tiene una cuenta? <a href="register.php">Registrese</a></p>
            </div>
        </div>
    </div>

    <script src="../../assets/js/login.js"></script>
</body>
</html>
