<?php 
echo "<pre>"; 
print_r($registros[0]); 
echo "</pre>"; 
?><div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-user-tag"></i> Roles del Sistema</h2>
        <?php if($app->verificarPermiso('rol.crear')): ?>
        <a href="rol.php?accion=crear" class="btn btn-success"><i class="fas fa-plus"></i> Nuevo Rol</a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre del Rol</th>
                        <th class="text-center">Permisos asignados</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($registros)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No hay roles registrados</td></tr>
                    <?php else: foreach($registros as $r): ?>
                        <tr>
                            <td><?= $r['id_rol'] ?></td>
                            <td><span class="badge bg-secondary fs-6"><?= ucfirst($r['rol']) ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">
                                    <?= (isset($r['total_permisos']) ? $r['total_permisos'] : ($r['TOTAL_PERMISOS'] ?? 0)) ?> permisos
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm pe-2">
                                    <a href="rol_permiso.php?accion=editar&id_rol=<?= $r['id_rol'] ?>"
                                       class="btn btn-outline-primary" title="Gestionar permisos">
                                       <i class="fas fa-key"></i>
                                    </a>
                                    <?php if($app->verificarPermiso('rol.editar')): ?>
                                    <a href="rol.php?accion=actualizar&id=<?= $r['id_rol'] ?>"
                                       class="btn btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if($app->verificarPermiso('rol.eliminar')): ?>
                                    <a href="rol.php?accion=borrar&id=<?= $r['id_rol'] ?>"
                                       class="btn btn-danger" title="Eliminar"
                                       onclick="return confirm('¿Eliminar este rol?')">
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
