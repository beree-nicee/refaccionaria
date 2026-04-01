<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-tools"></i> Servicios</h2>
        <?php if ($app->verificarPermiso('servicio_crear')): ?>
        <a href="servicio.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Servicio
        </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:80px">Imagen</th>
                        <th>Servicio</th>
                        <th>Mano de Obra</th>
                        <th>Tiempo Est.</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay registros</td></tr>
                    <?php else: foreach ($registros as $r): ?>
                    <tr>
                        <td>
                            <?php if (!empty($r['imagen_servicio'])): ?>
                            <img src="../uploads/servicios/<?= htmlspecialchars($r['imagen_servicio']) ?>"
                                 style="width:60px;height:60px;object-fit:cover;border-radius:6px"
                                 onerror="this.style.display='none'">
                            <?php else: ?>
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                 style="width:60px;height:60px">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($r['nombre_servicio']) ?></strong>
                            <?php if (!empty($r['descripcion'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($r['descripcion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>$<?= number_format($r['precio_mano_obra'], 2) ?></td>
                        <td><?= $r['tiempo_estimado'] ?> min</td>
                        <td class="text-center">
                            <span class="badge bg-<?= ($r['estado'] == 'activo') ? 'success' : 'danger' ?>">
                                <?= ucfirst($r['estado']) ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <?php if ($app->verificarPermiso('servicio_editar') || $app->verificarPermiso('servicio_eliminar')): ?>
                            <div class="btn-group btn-group-sm">
                                <?php if ($app->verificarPermiso('servicio_editar')): ?>
                                <a href="servicio.php?accion=actualizar&id=<?= $r['id_servicio'] ?>"
                                   class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($app->verificarPermiso('servicio_eliminar')): ?>
                                <a href="servicio.php?accion=borrar&id=<?= $r['id_servicio'] ?>"
                                   class="btn btn-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <a href="cita.php?accion=crear&id_servicio=<?= $r['id_servicio'] ?>"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-calendar-plus"></i> Agendar
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>