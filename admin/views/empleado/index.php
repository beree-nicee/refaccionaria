<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-id-card"></i> Empleados</h2>
        <a href="empleado.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Empleado
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>RFC / CURP</th>
                            <th>Rol</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($registros)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No hay registros</td></tr>
                        <?php else: foreach($registros as $r): ?>
                        <tr>
                            <td><?php echo $r['id_empleado']; ?></td>
                            <td>
                                <img src="uploads/empleados/<?php echo $r['fotografia'] ?? 'default.png'; ?>" width="40" class="rounded-circle">
                            </td>
                            <td><?php echo $r['nombre'] . ' ' . $r['apellido_paterno'] . ' ' . $r['apellido_materno']; ?></td>
                            <td><?php echo $r['email']; ?></td>
                            <td><small><?php echo $r['rfc']; ?><br><?php echo $r['curp']; ?></small></td>
                            <td><span class="badge bg-info text-dark"><?php echo $r['rol']; ?></span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="empleado.php?accion=actualizar&id=<?php echo $r['id_empleado']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="empleado.php?accion=borrar&id=<?php echo $r['id_empleado']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></a>
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