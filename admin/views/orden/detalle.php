<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-file-invoice"></i>
            Orden #<?= str_pad($orden['id_orden'], 4, '0', STR_PAD_LEFT) ?>
        </h2>
        <div class="d-flex gap-2">
            <a href="orden.php?accion=pdf&id=<?= $orden['id_orden'] ?>"
               class="btn btn-danger" target="_blank">
               <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
            <a href="orden.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Info general -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white"><i class="fas fa-info-circle"></i> Datos de la orden</div>
                <div class="card-body">
                    <?php
                    if (!isset($orden) || !$orden) {
                        echo "<div class='alert alert-danger'>Error: No se seleccionó una orden válida o la orden no existe.</div>";
                        return; // Detiene la carga de la vista para que no salgan los warnings
                    }
                    $colores = ['pendiente'=>'warning','procesando'=>'info','completada'=>'success','cancelada'=>'danger'];
                    $color = $colores[$orden['estado_orden']] ?? 'secondary';
                    ?>
                    <p><strong>Estado:</strong>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($orden['estado_orden']) ?></span>
                    </p>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($orden['fecha_orden'])) ?></p>
                    <p><strong>Método de pago:</strong> <?= htmlspecialchars($orden['metodo_pago'] ?? 'No especificado') ?></p>
                    <?php if (!empty($orden['notas_especiales'])): ?>
                    <p><strong>Notas:</strong> <?= htmlspecialchars($orden['notas_especiales']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info cliente -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white"><i class="fas fa-user"></i> Cliente</div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($orden['nombre'] ?? '') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($orden['email']) ?></p>
                </div>
            </div>
        </div>

        <!-- Refacciones -->
        <?php if (!empty($detalles['refacciones'])): ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white"><i class="fas fa-cog"></i> Refacciones</div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles['refacciones'] as $r): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($r['codigo_producto']) ?></code></td>
                                <td><?= htmlspecialchars($r['nombre']) ?></td>
                                <td class="text-center"><?= $r['cantidad'] ?></td>
                                <td class="text-end">$<?= number_format($r['precio_unitario'], 2) ?></td>
                                <td class="text-end fw-semibold">$<?= number_format($r['subtotal'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Servicios -->
        <?php if (!empty($detalles['servicios'])): ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white"><i class="fas fa-tools"></i> Servicios</div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th class="text-end">Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles['servicios'] as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['nombre_servicio']) ?></td>
                                <td class="text-end fw-semibold">$<?= number_format($s['precio_servicio'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Totales -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Subtotal refacciones</td>
                                    <td class="text-end">$<?= number_format($orden['total_refacciones'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td>Subtotal servicios</td>
                                    <td class="text-end">$<?= number_format($orden['total_servicios'], 2) ?></td>
                                </tr>
                                <tr class="table-dark fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-end">$<?= number_format($orden['total_general'], 2) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
