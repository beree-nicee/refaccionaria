<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-shield-alt"></i>
            Permisos del rol: <span class="badge bg-secondary"><?= htmlspecialchars($rol['rol']) ?></span>
        </h2>
        <a href="rol_permiso.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <form method="POST" action="rol_permiso.php?accion=editar&id_rol=<?= $id_rol ?>">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-key"></i> Permisos disponibles</span>
                <button type="button" class="btn btn-sm btn-outline-light" id="btnTodos">
                    Seleccionar todos
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($todosPermisos as $p): ?>
                    <div class="col-md-3 col-sm-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input permiso-check"
                                   type="checkbox"
                                   name="permisos[]"
                                   value="<?= $p['id_permiso'] ?>"
                                   id="perm_<?= $p['id_permiso'] ?>"
                                   <?= in_array($p['id_permiso'], $permisosAsignados) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="perm_<?= $p['id_permiso'] ?>">
                                <code><?= htmlspecialchars($p['permiso']) ?></code>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" name="enviar" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar permisos
            </button>
            <a href="rol_permiso.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
const btn = document.getElementById('btnTodos');
let todos = false;
btn.addEventListener('click', function() {
    todos = !todos;
    document.querySelectorAll('.permiso-check').forEach(c => c.checked = todos);
    this.textContent = todos ? 'Desmarcar todos' : 'Seleccionar todos';
});
</script>
