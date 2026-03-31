<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-key"></i> Permisos del Sistema</h2>
        <?php if($app->verificarPermiso('permiso_crear')): ?>
        <a href="permiso.php?accion=crear" class="btn btn-success"><i class="fas fa-plus"></i> Nuevo Permiso</a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre del permiso</th>
                        <th class="text-center">Roles que lo tienen</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($registros)): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">No hay permisos registrados</td></tr>
                <?php else: foreach($registros as $p): ?>
                    <tr>
                        <td><?= $p['id_permiso'] ?></td>
                        <td><code><?= htmlspecialchars($p['permiso']) ?></code></td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark"><?= $p['total_roles'] ?></span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <?php if($app->verificarPermiso('permiso_editar')): ?>
                                <a href="permiso.php?accion=actualizar&id=<?= $p['id_permiso'] ?>"
                                   class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if($app->verificarPermiso('permiso_eliminar')): ?>
                                <a href="permiso.php?accion=borrar&id=<?= $p['id_permiso'] ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Eliminar este permiso?')">
                                   <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
