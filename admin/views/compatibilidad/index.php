<div class="card border-warning mt-4 shadow-sm">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-car"></i> Compatibilidad de Vehículos</h5>
        <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#modalCompatibilidad">
            <i class="fas fa-plus"></i> Agregar Auto
        </button>
    </div>
    <div class="card-body">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th class="text-center">Años</th>
                    <th class="text-end">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($compatibilidades)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No hay compatibilidades registradas</td></tr>
                <?php else: foreach($compatibilidades as $c): ?>
                    <tr>
                        <td><strong><?php echo $c['marca_vehiculo']; ?></strong></td>
                        <td><?php echo $c['modelo_vehiculo']; ?></td>
                        <td class="text-center"><?php echo $c['anio_inicio'] . " - " . $c['anio_fin']; ?></td>
                        <td class="text-end">
                            <a href="compatibilidad.php?accion=borrar&id=<?php echo $c['id_compatibilidad']; ?>&id_refaccion=<?php echo $id; ?>" 
                               class="text-danger" onclick="return confirm('¿Quitar este vehículo?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>