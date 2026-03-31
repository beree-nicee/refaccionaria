<div class="container mt-4" style="max-width:500px">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-user-tag"></i>
                <?= isset($data['id_rol']) ? 'Editar Rol' : 'Nuevo Rol' ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST"
                action="rol.php?accion=<?= isset($data['id_rol']) ? 'actualizar&id='.$data['id_rol'] : 'crear' ?>">

                <div class="mb-3">
                    <label class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                    <input type="text" name="rol" class="form-control"
                           value="<?= $data['rol'] ?? '' ?>"
                           placeholder="Ej: admin, tecnico, cliente" required>
                    <small class="text-muted">Se guardará en minúsculas</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="rol.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
