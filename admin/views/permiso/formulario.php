<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-cog"></i> Inventario de Refacciones</h2>
        <?php if($app->verificarPermiso('refaccion_crear')): ?>
        <a href="refaccion.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Refacción
        </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:70px">Imagen</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($refacciones)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No hay refacciones registradas</td></tr>
                <?php else: foreach($refacciones as $r): ?>
                    <tr>
                        <td>
                            <?php if (!empty($r['imagen'])): ?>
                                <img src="../uploads/refacciones/<?= htmlspecialchars($r['imagen']) ?>"
                                     alt="img" class="img-thumbnail"
                                     style="width:50px;height:50px;object-fit:cover;">
                            <?php else: ?>
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                     style="width:50px;height:50px">
                                    <i class="fas fa-image text-white"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><code><?= htmlspecialchars($r['codigo_producto']) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($r['nombre']) ?></strong>
                            <?php if (!empty($r['marca_refaccion'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($r['marca_refaccion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['nombre_categoria']) ?></span></td>
                        <td><?= htmlspecialchars($r['marca_refaccion'] ?? '—') ?></td>
                        <td class="text-end fw-semibold">$<?= number_format($r['precio'], 2) ?></td>
                        <td class="text-center">
                            <?php
                            $stock = $r['stock_actual'];
                            $min   = $r['stock_minimo'];
                            $color = $stock <= 0 ? 'danger' : ($stock <= $min ? 'warning' : 'success');
                            ?>
                            <span class="badge bg-<?= $color ?>"><?= $stock ?></span>
                        </td>
                        <td class="text-center">
                            <?php
                            $estados = ['disponible' => 'success', 'agotado' => 'danger', 'descontinuado' => 'secondary'];
                            $est = $r['estado_producto'] ?? 'disponible';
                            ?>
                            <span class="badge bg-<?= $estados[$est] ?>">
                                <?= ucfirst($est) ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <?php if($app->verificarPermiso('refaccion_editar')): ?>
                                <a href="refaccion.php?accion=actualizar&id=<?= $r['id_refaccion'] ?>"
                                   class="btn btn-warning" title="Editar">
                                   <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if($app->verificarPermiso('refaccion_eliminar')): ?>
                                <a href="refaccion.php?accion=borrar&id=<?= $r['id_refaccion'] ?>"
                                   class="btn btn-danger" title="Eliminar"
                                   onclick="return confirm('¿Eliminar esta refacción?')">
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
