<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-boxes"></i> Paquetes de Promoción</h2>
        <?php if ($app->verificarPermiso('paquete_crear')): ?>
        <a href="paquete.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Paquete
        </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:80px">Imagen</th>
                        <th>Paquete</th>
                        <th>Descripción</th>
                        <th class="text-center">Descuento</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay paquetes registrados</td></tr>
                    <?php else: foreach ($registros as $r): ?>
                    <tr>
                        <td>
                            <?php if (!empty($r['imagen_paquete'])): ?>
                            <img src="../uploads/paquetes/<?= htmlspecialchars($r['imagen_paquete']) ?>"
                                 style="width:60px;height:60px;object-fit:cover;border-radius:6px"
                                 onerror="this.style.display='none'">
                            <?php else: ?>
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                 style="width:60px;height:60px">
                                <i class="fas fa-box text-white"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($r['nombre_paquete']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($r['descripcion'] ?? '') ?></small></td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark">
                                <?= $r['descuento_porcentaje'] ?>%
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= ($r['estado'] == 'activo') ? 'success' : 'secondary' ?>">
                                <?= ucfirst($r['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <?php if ($app->verificarPermiso('paquete_editar') || $app->verificarPermiso('paquete_eliminar')): ?>
                            <div class="btn-group btn-group-sm">
                                <?php if ($app->verificarPermiso('paquete_editar')): ?>
                                <a href="paquete.php?accion=actualizar&id=<?= $r['id_paquete'] ?>"
                                   class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($app->verificarPermiso('paquete_eliminar')): ?>
                                <a href="paquete.php?accion=borrar&id=<?= $r['id_paquete'] ?>"
                                   class="btn btn-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar paquete?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>