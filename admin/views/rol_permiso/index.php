<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-shield-alt"></i> Asignación Rol - Permisos</h2>
        <a href="rol.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Ver Roles</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Rol</th>
                        <th class="text-center">Total permisos</th>
                        <th>Permisos asignados</th>
                        <th class="text-end">Gestionar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($registros as $r): ?>
                    <tr>
                        <td><span class="badge bg-secondary fs-6"><?= ucfirst($r['rol']) ?></span></td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark"><?= $r['total_permisos'] ?? 0 ?></span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $r['permisos'] ? substr($r['permisos'], 0, 100) . (strlen($r['permisos']) > 100 ? '...' : '') : 'Sin permisos' ?>
                            </small>
                        </td>
                        <td class="text-end pe-3">
                            <a href="rol_permiso.php?accion=editar&id_rol=<?= $r['id_rol'] ?>"
                               class="btn btn-sm btn-primary">
                               <i class="fas fa-edit"></i> Editar permisos
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
