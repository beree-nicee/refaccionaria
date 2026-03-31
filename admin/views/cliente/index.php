<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-user-tie"></i> Clientes</h2>
        <a href="cliente.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Cliente
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email (Usuario)</th>
                            <th>Teléfono</th>
                            <th>Ciudad</th>
                            <th>Registro</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($registros)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay clientes registrados</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($registros as $r): ?>
                            <tr>
                                <td><?php echo $r['id_cliente']; ?></td>
                                <td><?php echo $r['nombre'] . ' ' . $r['apellido_paterno'] . ' ' . $r['apellido_materno']; ?></td>
                                <td><?php echo $r['email']; ?></td>
                                <td><?php echo $r['telefono'] ?? 'N/A'; ?></td>
                                <td><?php echo $r['ciudad'] ?? 'N/A'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($r['fecha_nacimiento'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="cliente.php?accion=actualizar&id=<?php echo $r['id_cliente']; ?>" 
                                           class="btn btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="cliente.php?accion=borrar&id=<?php echo $r['id_cliente']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Eliminar cliente y su cuenta de usuario?')"
                                           title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>