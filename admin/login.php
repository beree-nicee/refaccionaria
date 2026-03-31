<?php
require_once(__DIR__."/sistema.class.php");
$app = new Sistema();
$accion = $_GET['accion'] ?? '';

// Si ya tiene sesión activa, redirigir al inicio
if (isset($_SESSION['id_usuario']) && $accion !== 'logout') {
    header("Location: index.php");
    exit;
}

switch ($accion) {
    case 'login':
        if (isset($_POST['enviar'])) {
            $correo = $_POST['correo'] ?? '';
            $pass   = $_POST['contrasena'] ?? '';

            if (empty($correo) || empty($pass)) {
                $error = "Por favor ingresa correo y contraseña";
            } elseif ($app->login($correo, $pass)) {
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
                exit;
            } else {
                $error = "Correo o contraseña incorrectos";
            }
        }
        break;

    case 'logout':
        $app->logout();
        header("Location: login.php");
        exit;
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión &mdash; Taller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 16px; padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
        .login-card h2 { color: #212529; font-weight: 700; margin-bottom: .25rem; }
        .login-card .subtitle { color: #6c757d; margin-bottom: 2rem; }
        .login-card .form-control { border-radius: 8px; padding: .65rem 1rem; }
        .login-card .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
        .btn-login { width: 100%; padding: .75rem; border-radius: 8px; font-weight: 600; letter-spacing: .5px; }
        .logo-area { text-align: center; margin-bottom: 1.5rem; }
        .logo-area i { font-size: 3rem; color: #0d6efd; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-area">
            <i class="fas fa-wrench"></i>
            <h2>Taller</h2>
            <p class="subtitle">Panel de Administración</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php?accion=login">
            <div class="mb-3">
                <label class="form-label fw-semibold">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="correo" class="form-control"
                           value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                           placeholder="correo@ejemplo.com" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="contrasena" class="form-control"
                           placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" name="enviar" class="btn btn-primary btn-login">
                <i class="fas fa-right-to-bracket"></i> Entrar al Sistema
            </button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
