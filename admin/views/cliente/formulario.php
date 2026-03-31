<?php
$esEditar        = ($accion === 'actualizar');
$esPropioCliente = $app->esCliente();
?>
<div class="container mt-4" style="max-width:700px">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-<?= $esPropioCliente ? 'user-edit' : 'user' ?>"></i>
                <?php
                if ($esPropioCliente) echo 'Mi perfil';
                elseif ($esEditar)    echo 'Editar cliente';
                else                  echo 'Nuevo cliente';
                ?>
            </h5>
        </div>
        <div class="card-body">
            <form action="cliente.php?accion=<?= $accion ?><?= $esEditar ? '&id='.$id : '' ?>"
                  method="POST" class="needs-validation" novalidate>

                <h6 class="border-bottom pb-2 mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.05em">
                    Datos de acceso
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Contraseña
                            <?= $esEditar ? '<small class="text-muted fw-normal">(vacío = no cambiar)</small>' : '<span class="text-danger">*</span>' ?>
                        </label>
                        <input type="password" name="contrasena" class="form-control"
                               minlength="6" placeholder="Mínimo 6 caracteres"
                               <?= !$esEditar ? 'required' : '' ?>>
                    </div>
                </div>

                <h6 class="border-bottom pb-2 mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.05em">
                    Datos personales
                </h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nombre(s) <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Apellido paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control"
                               value="<?= htmlspecialchars($data['apellido_paterno'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Apellido materno <span class="text-danger">*</span></label>
                        <input type="text" name="apellido_materno" class="form-control"
                               value="<?= htmlspecialchars($data['apellido_materno'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control"
                               pattern="[0-9]{10}" placeholder="10 dígitos"
                               value="<?= htmlspecialchars($data['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha de nacimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="<?= htmlspecialchars($data['fecha_nacimiento'] ?? '') ?>"
                               max="<?= date('Y-m-d', strtotime('-16 years')) ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               value="<?= htmlspecialchars($data['direccion'] ?? '') ?>"
                               placeholder="Calle, número, colonia">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control"
                               value="<?= htmlspecialchars($data['ciudad'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $esPropioCliente ? 'Guardar cambios' : 'Guardar cliente' ?>
                    </button>
                    <a href="<?= $esPropioCliente ? 'index.php' : 'cliente.php' ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    document.querySelectorAll('.needs-validation').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    });
})();
</script>