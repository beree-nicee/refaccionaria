<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-users"></i> Usuarios</h2>
        <?php if($app->esAdmin()): ?>
        <a href="usuario.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($usuarios)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay usuarios registrados</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($usuarios as $u): ?>
                            <tr>
                                <td><?php echo $u['id_usuario']; ?></td>
                                <td><?php echo $u['nombre'] . ' ' . $u['apellidos']; ?></td>
                                <td><?php echo $u['email']; ?></td>
                                <td><?php echo $u['telefono'] ?? 'N/A'; ?></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'admin' => 'danger',
                                        'tecnico' => 'warning',
                                        'cliente' => 'primary'
                                    ];
                                    $badge = $badges[$u['nombre_rol']] ?? 'secondary';
                                    
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        
                                        <?php echo ucfirst($u['nombre_rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $estadoBadge = [
                                        'activa' => 'success',
                                        'suspendida' => 'warning',
                                        'eliminada' => 'danger'
                                    ];
                                    $badge = $estadoBadge[$u['estado_cuenta']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst($u['estado_cuenta']); ?>
                                    </span>
                                </td>
                                <td><?php echo $app->formatearFecha($u['fecha_registro']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="usuario.php?accion=actualizar&id=<?php echo $u['id_usuario']; ?>" 
                                           class="btn btn-warning" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($app->esAdmin()): ?>
                                        <a href="usuario.php?accion=borrar&id=<?php echo $u['id_usuario']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Eliminar usuario?')"
                                           title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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