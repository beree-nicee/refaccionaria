<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-file-invoice"></i> Órdenes de Compra</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#Orden</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th class="text-end">Refacciones</th>
                        <th class="text-end">Servicios</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Pago</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No hay órdenes registradas</td></tr>
                <?php else: foreach ($ordenes as $o): ?>
                    <?php
                    $colores = [
                        'pendiente'   => 'warning',
                        'procesando'  => 'info',
                        'completada'  => 'success',
                        'cancelada'   => 'danger',
                    ];
                    $color = $colores[$o['estado_orden']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><strong>#<?= str_pad($o['id_orden'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($o['nombre'] ?? '') ?>
                            <br><small class="text-muted"><?= htmlspecialchars($o['email']) ?></small>
                        </td>
                        <td><small><?= date('d/m/Y H:i', strtotime($o['fecha_orden'])) ?></small></td>
                        <td class="text-end">$<?= number_format($o['total_refacciones'], 2) ?></td>
                        <td class="text-end">$<?= number_format($o['total_servicios'], 2) ?></td>
                        <td class="text-end fw-bold">$<?= number_format($o['total_general'], 2) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $color ?>">
                                <?= ucfirst($o['estado_orden']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <small><?= htmlspecialchars($o['metodo_pago'] ?? '—') ?></small>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="orden.php?accion=ver&id=<?= $o['id_orden'] ?>"
                                   class="btn btn-outline-primary" title="Ver detalle">
                                   <i class="fas fa-eye"></i>
                                </a>
                                <a href="orden.php?accion=pdf&id=<?= $o['id_orden'] ?>"
                                   class="btn btn-outline-danger" title="Descargar PDF" target="_blank">
                                   <i class="fas fa-file-pdf"></i>
                                </a>
                                <?php if($app->esAdmin() || $app->esTecnico()): ?>
                                <a href="orden.php?accion=actualizar&id=<?= $o['id_orden'] ?>"
                                   class="btn btn-warning" title="Actualizar estado">
                                   <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if($o['estado_orden'] === 'pendiente'): ?>
                                <a href="orden.php?accion=cancelar&id=<?= $o['id_orden'] ?>"
                                   class="btn btn-outline-danger" title="Cancelar"
                                   onclick="return confirm('¿Cancelar esta orden?')">
                                   <i class="fas fa-times"></i>
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
