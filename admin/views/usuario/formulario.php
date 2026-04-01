<?php
$esEditar   = ($accion === 'actualizar');
$carpeta    = ($data['carpeta_foto'] ?? 'empleados');
$avatar = !empty($data['fotografia'])
    ? "../uploads/{$carpeta}/" . htmlspecialchars($data['fotografia'])
    : "../images/default-avatar.jpg";
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-<?= $esEditar ? 'user-edit' : 'user-plus' ?>"></i>
                        <?= $esEditar ? 'Editar Usuario' : 'Nuevo Usuario' ?>
                    </h5>
                </div>
                <div class="card-body">

                    <?php if ($esEditar): ?>
                    <div class="text-center mb-4">
                        <img src="<?= $avatar ?>"
                             alt="Avatar"
                             class="rounded-circle border shadow-sm"
                             style="width:90px;height:90px;object-fit:cover"
                             onerror="this.src='../images/default-avatar.jpg'"
                             id="avatarPreview">
                        <p class="text-muted small mt-2 mb-0">
                            <?= htmlspecialchars($data['nombre'] ?? '') ?>
                            <span class="badge bg-secondary ms-1">
                                <?= htmlspecialchars($data['nombre_rol'] ?? '') ?>
                            </span>
                        </p>
                    </div>
                    <?php endif; ?>

                    <form action="usuario.php?accion=<?= $accion ?><?= $esEditar ? '&id='.$id : '' ?>"
                          method="POST" enctype="multipart/form-data"
                          class="needs-validation" novalidate>

                        <!-- Datos personales -->
                        <h6 class="border-bottom pb-2 mb-3 text-muted text-uppercase"
                            style="font-size:.75rem;letter-spacing:.05em">
                            Datos personales
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nombre(s)</label>
                                <input type="text" name="nombre" class="form-control"
                                       value="<?= htmlspecialchars($data['nombre'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Apellido paterno</label>
                                <input type="text" name="apellido_paterno" class="form-control"
                                       value="<?= htmlspecialchars($data['apellido_paterno'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Apellido materno</label>
                                <input type="text" name="apellido_materno" class="form-control"
                                       value="<?= htmlspecialchars($data['apellido_materno'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control"
                                       value="<?= htmlspecialchars($data['telefono'] ?? '') ?>"
                                       placeholder="10 dígitos">
                            </div>
                            <?php if ($esEditar): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fotografía</label>
                                <input type="file" name="fotografia" class="form-control"
                                       accept="image/*" id="inputFoto">
                                <small class="text-muted">Sube una nueva para reemplazar la actual</small>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Datos de acceso -->
                        <h6 class="border-bottom pb-2 mb-3 text-muted text-uppercase"
                            style="font-size:.75rem;letter-spacing:.05em">
                            Datos de acceso
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Contraseña
                                    <?= $esEditar
                                        ? '<small class="text-muted fw-normal">(vacío = no cambiar)</small>'
                                        : '<span class="text-danger">*</span>' ?>
                                </label>
                                <input type="password" name="contrasena" class="form-control"
                                       placeholder="Mínimo 6 caracteres"
                                       <?= !$esEditar ? 'required minlength="6"' : '' ?>>
                            </div>
                        </div>

                        <!-- Rol y estado (solo admin) -->
                        <?php if ($app->esAdmin()): ?>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rol</label>
                                <select name="id_rol" class="form-select">
                                    <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id_rol'] ?>"
                                        <?= ($data['id_rol'] ?? '') == $r['id_rol'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['rol']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($esEditar): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado de cuenta</label>
                                <select name="estado_cuenta" class="form-select">
                                    <?php foreach (['activa','suspendida','eliminada'] as $est): ?>
                                    <option value="<?= $est ?>"
                                        <?= ($data['estado_cuenta'] ?? 'activa') === $est ? 'selected' : '' ?>>
                                        <?= ucfirst($est) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <button type="submit" name="enviar" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="usuario.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('inputFoto')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
});

document.querySelectorAll('.needs-validation').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
        form.classList.add('was-validated');
    });
});
</script>