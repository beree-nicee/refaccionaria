<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-car"></i> Vehículos</h2>
        <a href="vehiculo.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Vehículo
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Dueño</th>
                            <th>Marca/Modelo</th>
                            <th>Año</th>
                            <th>Placas</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($registros)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No hay registros</td></tr>
                        <?php else: foreach($registros as $r): ?>
                        <tr>
                            <td><?php echo $r['id_vehiculo']; ?></td>
                            <td><?php echo $r['email_dueno']; ?></td>
                            <td><?php echo $r['marca'] . ' ' . $r['modelo']; ?></td>
                            <td><?php echo $r['anio']; ?></td>
                            <td><span class="badge bg-dark"><?php echo $r['placas'] ?? 'S/P'; ?></span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="vehiculo.php?accion=actualizar&id=<?php echo $r['id_vehiculo']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="vehiculo.php?accion=borrar&id=<?php echo $r['id_vehiculo']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></a>
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