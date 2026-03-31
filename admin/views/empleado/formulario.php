<?php
$esEditar   = ($accion === 'actualizar');
$esPropio   = false;

// Detectar si está editando su propio perfil
if ($esEditar && isset($data['id_usuario'])) {
    $esPropio = ((int)$data['id_usuario'] === (int)$app->obtenerIdUsuario());
}

// Solo admin puede editar RFC/CURP de OTROS empleados
$puedeRFCCURP = $app->esAdmin() && !$esPropio;
?>
<div class="container mt-4" style="max-width:700px">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-<?= $esPropio ? 'user-edit' : 'id-badge' ?>"></i>
                <?php
                if ($esPropio)       echo 'Mi perfil';
                elseif ($esEditar)   echo 'Editar empleado';
                else                 echo 'Nuevo empleado';
                ?>
            </h5>
        </div>
        <div class="card-body">
            <form action="empleado.php?accion=<?= $accion ?><?= $esEditar ? '&id='.$id : '' ?>"
                  method="POST" enctype="multipart/form-data"
                  class="needs-validation" novalidate>

                <!-- Datos de acceso -->
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
                               placeholder="Mínimo 6 caracteres"
                               <?= !$esEditar ? 'required' : '' ?>>
                    </div>
                </div>

                <!-- Datos personales -->
                <h6 class="border-bottom pb-2 mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.05em">
                    Datos personales
                </h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
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
                               value="<?= htmlspecialchars($data['telefono'] ?? '') ?>"
                               placeholder="10 dígitos">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha de nacimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_nacimiento" class="form-control"
                               value="<?= htmlspecialchars($data['fecha_nacimiento'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- RFC y CURP: editable solo por admin editando a OTRO -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            RFC
                            <?php if (!$puedeRFCCURP && $esEditar): ?>
                            <span class="badge bg-secondary ms-1" style="font-size:.65rem">Solo admin</span>
                            <?php endif; ?>
                        </label>
                        <?php if ($puedeRFCCURP || !$esEditar): ?>
                        <input type="text" name="rfc" class="form-control text-uppercase"
                               value="<?= htmlspecialchars($data['rfc'] ?? '') ?>"
                               maxlength="13" placeholder="13 caracteres">
                        <?php else: ?>
                        <input type="text" class="form-control font-monospace" readonly
                               value="<?= htmlspecialchars($data['rfc'] ?? '—') ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            CURP
                            <?php if (!$puedeRFCCURP && $esEditar): ?>
                            <span class="badge bg-secondary ms-1" style="font-size:.65rem">Solo admin</span>
                            <?php endif; ?>
                        </label>
                        <?php if ($puedeRFCCURP || !$esEditar): ?>
                        <input type="text" name="curp" class="form-control text-uppercase"
                               value="<?= htmlspecialchars($data['curp'] ?? '') ?>"
                               maxlength="18" placeholder="18 caracteres">
                        <?php else: ?>
                        <input type="text" class="form-control font-monospace" readonly
                               value="<?= htmlspecialchars($data['curp'] ?? '—') ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fotografía -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Fotografía</label>
                    <?php if (!empty($data['fotografia'])): ?>
                    <div class="mb-2">
                        <img src="uploads/empleados/<?= htmlspecialchars($data['fotografia']) ?>"
                             class="rounded border" style="width:80px;height:80px;object-fit:cover">
                        <small class="text-muted ms-2">Foto actual</small>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="fotografia" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG, WEBP — máx. 5MB. Sube una nueva para reemplazar.</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $esPropio ? 'Guardar cambios' : 'Guardar empleado' ?>
                    </button>
                    <a href="<?= $esPropio ? 'index.php' : 'empleado.php' ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.needs-validation').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
        form.classList.add('was-validated');
    });
});
</script>
