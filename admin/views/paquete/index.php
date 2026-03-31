<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-boxes"></i> Paquetes de Promoción</h2>
        <a href="paquete.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Paquete
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        
                        <th>Paquete</th>
                        <th>Descripción</th>
                        <th class="text-center">Descuento</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($registros)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay paquetes registrados</td></tr>
                    <?php else: foreach($registros as $r): ?>
                        <tr>
                            <td><strong><?php echo $r['nombre_paquete']; ?></strong></td>
                            <td><small class="text-muted"><?php echo $r['descripcion']; ?></small></td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">
                                    <?php echo $r['descuento_porcentaje']; ?>%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo ($r['estado']=='activo') ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($r['estado']); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm pe-2">
                                    <a href="paquete.php?accion=actualizar&id=<?php echo $r['id_paquete']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="paquete.php?accion=borrar&id=<?php echo $r['id_paquete']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar paquete?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>