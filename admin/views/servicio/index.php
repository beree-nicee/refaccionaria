<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-tools"></i> Servicios</h2>
        <a href="servicio.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Servicio
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Imagen</th>
                            <th>Servicio</th>
                            <th>Mano de Obra</th>
                            <th>Tiempo Est.</th>
                            <th>Estado</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($registros)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No hay registros</td></tr>
                        <?php else: foreach($registros as $r): ?>
                        <tr>
                            <td><img src="../uploads/servicios/<?= htmlspecialchars($r['imagen_servicio'] ?? 'default.jpg') ?>" width="150" onerror="this.style.display='none'"></td>
                            <td><strong><?php echo $r['nombre_servicio']; ?></strong></td>
                            <td>$<?php echo number_format($r['precio_mano_obra'], 2); ?></td>
                            <td><?php echo $r['tiempo_estimado']; ?> min</td>
                            <td><span class="badge bg-<?php echo ($r['estado'] == 'activo') ? 'success' : 'danger'; ?>"><?php echo $r['estado']; ?></span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="servicio.php?accion=actualizar&id=<?php echo $r['id_servicio']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="servicio.php?accion=borrar&id=<?php echo $r['id_servicio']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>