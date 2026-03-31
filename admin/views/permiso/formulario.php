<div class="container mt-4" style="max-width:500px">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-key"></i>
                <?= isset($data['id_permiso']) ? 'Editar Permiso' : 'Nuevo Permiso' ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST"
                action="permiso.php?accion=<?= isset($data['id_permiso']) ? 'actualizar&id='.$data['id_permiso'] : 'crear' ?>">

                <div class="mb-3">
                    <label class="form-label">Nombre del permiso <span class="text-danger">*</span></label>
                    <input type="text" name="permiso" class="form-control"
                           value="<?= htmlspecialchars($data['permiso'] ?? '') ?>"
                           placeholder="Ej: refaccion_leer" required>
                    <small class="text-muted">Formato: modulo_accion (ej: usuario_crear)</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="permiso.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
