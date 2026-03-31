<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-calendar-check"></i> Agenda de Citas</h2>
        <a href="cita.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Cita
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Cliente</th>
                            <th>Vehículo</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($registros)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No hay citas programadas</td></tr>
                        <?php else: foreach($registros as $r): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('d/m/Y', strtotime($r['fecha_cita'])); ?></strong><br>
                                <small class="text-muted"><?php echo $r['hora_inicio']; ?></small>
                            </td>
                            <td><?php echo $r['cliente_email']; ?></td>
                            <td><?php echo $r['vehiculo_info'] ?? 'N/A'; ?></td>
                            <td><?php echo $r['nombre_servicio']; ?></td>
                            <td>
                                <?php 
                                    $colores = ['pendiente'=>'warning', 'confirmada'=>'primary', 'terminada'=>'success', 'cancelada'=>'danger'];
                                    $color = $colores[$r['estado_cita']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst($r['estado_cita']); ?></span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="cita.php?accion=actualizar&id=<?php echo $r['id_cita']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="cita.php?accion=borrar&id=<?php echo $r['id_cita']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar cita?')"><i class="fas fa-trash"></i></a>
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