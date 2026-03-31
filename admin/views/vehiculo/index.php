<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-car"></i>
            <?= $app->esCliente() ? 'Mis Vehículos' : 'Vehículos' ?>
        </h2>
        <?php if($app->verificarPermiso('vehiculo_crear')): ?>
        <a href="vehiculo.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Vehículo
        </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <?php if(!$app->esCliente()): ?>
                        <th>Dueño</th>
                        <?php endif; ?>
                        <th>Marca / Modelo</th>
                        <th class="text-center">Año</th>
                        <th class="text-center">Placas</th>
                        <th>VIN</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="<?= $app->esCliente() ? 5 : 6 ?>" class="text-center py-4 text-muted">
                            <?= $app->esCliente() ? 'No tienes vehículos registrados' : 'No hay vehículos registrados' ?>
                        </td>
                    </tr>
                <?php else: foreach ($registros as $r): ?>
                    <tr>
                        <?php if(!$app->esCliente()): ?>
                        <td>
                            <strong><?= htmlspecialchars(trim($r['nombre_dueno'].' '.$r['apellido_dueno'])) ?: '—' ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($r['email_dueno']) ?></small>
                        </td>
                        <?php endif; ?>
                        <td>
                            <strong><?= htmlspecialchars($r['marca']) ?></strong>
                            <?= htmlspecialchars($r['modelo']) ?>
                        </td>
                        <td class="text-center"><?= $r['anio'] ?></td>
                        <td class="text-center">
                            <span class="badge bg-dark"><?= htmlspecialchars($r['placas'] ?: 'S/P') ?></span>
                        </td>
                        <td><small class="text-muted font-monospace"><?= htmlspecialchars($r['numero_serie_vin'] ?: '—') ?></small></td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <?php if($app->verificarPermiso('vehiculo_editar')): ?>
                                <a href="vehiculo.php?accion=actualizar&id=<?= $r['id_vehiculo'] ?>"
                                   class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if($app->verificarPermiso('vehiculo_eliminar')): ?>
                                <a href="vehiculo.php?accion=borrar&id=<?= $r['id_vehiculo'] ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Eliminar este vehículo?')">
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
