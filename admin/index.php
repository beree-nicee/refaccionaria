<?php
require_once('config.php');
require_once('sistema.class.php');
$app = new Sistema();
$app->requiereLogin();

include_once(__DIR__.'/views/header.php');
?>
<div class="container mt-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <i class="fas fa-gauge-high fa-2x text-primary"></i>
        <div>
            <h2 class="mb-0">Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?: $_SESSION['email']) ?></h2>
            <small class="text-muted">Rol: <strong><?= ucfirst($_SESSION['rol'] ?? '') ?></strong>
                &mdash; <?= date('d/m/Y H:i') ?>
            </small>
        </div>
    </div>

    <div class="row g-3">
        <?php if($app->verificarPermiso('refaccion_leer')): ?>
        <div class="col-md-3">
            <a href="refaccion.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-cog fa-3x text-primary mb-3"></i>
                        <h6 class="fw-semibold">Refacciones</h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        <?php if($app->verificarPermiso('cita_leer')): ?>
        <div class="col-md-3">
            <a href="cita.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                        <h6 class="fw-semibold">Citas</h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        <?php if($app->verificarPermiso('orden_leer')): ?>
        <div class="col-md-3">
            <a href="orden.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-file-invoice fa-3x text-warning mb-3"></i>
                        <h6 class="fw-semibold">Órdenes</h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
        <?php if($app->esAdmin()): ?>
        <div class="col-md-3">
            <a href="rol_permiso.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-shield-alt fa-3x text-danger mb-3"></i>
                        <h6 class="fw-semibold">Seguridad</h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include_once(__DIR__.'/views/footer.php'); ?>
