<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/cliente.php");
require_once(__DIR__."/libs/correo.php");

// Si ya tiene sesión activa, redirigir
if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

$app   = new Cliente();
$error = null;
$exito = false;

if (isset($_POST['enviar'])) {
    try {
        // Crear el cliente (transacción Usuario + Cliente)
        $resultado = $app->registrar($_POST);

        // Intentar enviar correo de bienvenida
        $correoEnviado = false;
        try {
            $correoEnviado = enviarBienvenida(
                $resultado['nombre'],
                $resultado['email'],
                $resultado['contrasena']
            );
        } catch (Exception $em) {
            // Si el correo falla, no interrumpir el registro
            // Solo registrar el error (en producción usarías un log)
        }

        $exito         = true;
        $correoStatus  = $correoEnviado;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta &mdash; Taller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; min-height: 100vh; padding: 40px 16px; }
        .reg-card { background: #fff; border-radius: 16px; padding: 2.5rem;
                    width: 100%; max-width: 640px; margin: 0 auto;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
        .reg-card h2 { color: #212529; font-weight: 700; }
        .reg-card .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
        .section-title { font-size: .75rem; font-weight: 600; text-transform: uppercase;
                         letter-spacing: .08em; color: #6c757d;
                         border-bottom: 1px solid #dee2e6; padding-bottom: 6px; margin-bottom: 1rem; }
        .logo-area { text-align: center; margin-bottom: 1.5rem; }
        .logo-area i { font-size: 2.5rem; color: #0d6efd; }
        .req { color: #dc3545; }
    </style>
</head>
<body>
<div class="reg-card">

    <div class="logo-area">
        <i class="fas fa-wrench"></i>
        <h2>Taller</h2>
        <p class="text-muted mb-0">Crear nueva cuenta</p>
    </div>

    <?php if ($exito): ?>
    <!-- ── Pantalla de éxito ── -->
    <div class="text-center py-3">
        <div class="mb-3">
            <i class="fas fa-circle-check text-success" style="font-size:4rem"></i>
        </div>
        <h4 class="fw-bold">¡Cuenta creada exitosamente!</h4>
        <p class="text-muted mb-1">Tu cuenta ha sido registrada. Ya puedes iniciar sesión.</p>
        <?php if ($correoStatus): ?>
        <p class="text-muted small">
            <i class="fas fa-envelope text-success"></i>
            Te enviamos un correo de bienvenida con tus credenciales.
        </p>
        <?php else: ?>
        <p class="text-muted small">
            <i class="fas fa-envelope text-warning"></i>
            No pudimos enviar el correo de bienvenida, pero tu cuenta está activa.
        </p>
        <?php endif; ?>
        <a href="login.php" class="btn btn-primary mt-3 px-5">
            <i class="fas fa-right-to-bracket"></i> Iniciar sesión
        </a>
    </div>

    <?php else: ?>
    <!-- ── Formulario ── -->
    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
        <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>

        <!-- Datos personales -->
        <p class="section-title"><i class="fas fa-user"></i> Datos personales</p>
        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Nombre(s) <span class="req">*</span></label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                       placeholder="Ej. María Fernanda" required autofocus>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Apellido paterno <span class="req">*</span></label>
                <input type="text" name="apellido_paterno" class="form-control"
                       value="<?= htmlspecialchars($_POST['apellido_paterno'] ?? '') ?>"
                       placeholder="Ej. García" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Apellido materno <span class="req">*</span></label>
                <input type="text" name="apellido_materno" class="form-control"
                       value="<?= htmlspecialchars($_POST['apellido_materno'] ?? '') ?>"
                       placeholder="Ej. López" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Fecha de nacimiento <span class="req">*</span></label>
                <input type="date" name="fecha_nacimiento" class="form-control"
                       value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>"
                       max="<?= date('Y-m-d', strtotime('-16 years')) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Teléfono</label>
                <input type="tel" name="telefono" class="form-control"
                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                       placeholder="10 dígitos">
            </div>
        </div>

        <!-- Ubicación -->
        <p class="section-title"><i class="fas fa-map-marker-alt"></i> Ubicación</p>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Ciudad</label>
                <input type="text" name="ciudad" class="form-control"
                       value="<?= htmlspecialchars($_POST['ciudad'] ?? '') ?>"
                       placeholder="Ej. Salamanca">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" name="direccion" class="form-control"
                       value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>"
                       placeholder="Calle, número, colonia">
            </div>
        </div>

        <!-- Credenciales -->
        <p class="section-title"><i class="fas fa-lock"></i> Credenciales de acceso</p>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label fw-semibold">Correo electrónico <span class="req">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="correo@ejemplo.com" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Contraseña <span class="req">*</span></label>
                <div class="input-group">
                    <input type="password" name="contrasena" class="form-control"
                           id="pass1" placeholder="Mínimo 6 caracteres" required minlength="6">
                    <button type="button" class="btn btn-outline-secondary" id="togglePass1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Confirmar contraseña <span class="req">*</span></label>
                <div class="input-group">
                    <input type="password" name="confirmar_contrasena" class="form-control"
                           id="pass2" placeholder="Repite la contraseña" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePass2">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passMsg" class="small mt-1"></div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" name="enviar" class="btn btn-primary btn-lg fw-semibold">
                <i class="fas fa-user-plus"></i> Crear mi cuenta
            </button>
        </div>
    </form>

    <p class="text-center mt-3 mb-0 text-muted small">
        ¿Ya tienes cuenta?
        <a href="login.php" class="text-decoration-none fw-semibold">Inicia sesión aquí</a>
    </p>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle mostrar/ocultar contraseña
['1','2'].forEach(n => {
    document.getElementById('togglePass'+n).addEventListener('click', function() {
        const inp = document.getElementById('pass'+n);
        const ico = this.querySelector('i');
        inp.type = inp.type === 'password' ? 'text' : 'password';
        ico.classList.toggle('fa-eye');
        ico.classList.toggle('fa-eye-slash');
    });
});

// Validar que las contraseñas coincidan en tiempo real
const p1 = document.getElementById('pass1');
const p2 = document.getElementById('pass2');
const msg = document.getElementById('passMsg');

function chkPass() {
    if (!p2.value) { msg.textContent = ''; return; }
    if (p1.value === p2.value) {
        msg.innerHTML = '<span class="text-success"><i class="fas fa-check"></i> Las contraseñas coinciden</span>';
        p2.classList.remove('is-invalid');
        p2.classList.add('is-valid');
    } else {
        msg.innerHTML = '<span class="text-danger"><i class="fas fa-times"></i> Las contraseñas no coinciden</span>';
        p2.classList.remove('is-valid');
        p2.classList.add('is-invalid');
    }
}
p1.addEventListener('input', chkPass);
p2.addEventListener('input', chkPass);
</script>
</body>
</html>
