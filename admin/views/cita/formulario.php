<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h4><?php echo ($accion == 'actualizar') ? 'Editar Cita' : 'Programar Nueva Cita'; ?></h4>
        </div>
        <div class="card-body">
            <form action="cita.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" method="POST">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cliente (Usuario) *</label>
                        <select name="id_usuario" class="form-select" required>
                            <?php foreach($usuarios as $u): ?>
                                <option value="<?php echo $u['id_usuario']; ?>" <?php echo (($data['id_usuario'] ?? '') == $u['id_usuario']) ? 'selected' : ''; ?>>
                                    <?php echo $u['email']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Servicio Solicitado *</label>
                        <select name="id_servicio" class="form-select" required>
                            <?php foreach($servicios as $s): ?>
                                <option value="<?php echo $s['id_servicio']; ?>" <?php echo (($data['id_servicio'] ?? '') == $s['id_servicio']) ? 'selected' : ''; ?>>
                                    <?php echo $s['nombre_servicio']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vehículo</label>
                        <select name="id_vehiculo" class="form-select">
                            <option value="">-- Seleccionar Vehículo --</option>
                            <?php foreach($vehiculos as $v): ?>
                                <option value="<?php echo $v['id_vehiculo']; ?>" <?php echo (($data['id_vehiculo'] ?? '') == $v['id_vehiculo']) ? 'selected' : ''; ?>>
                                    <?php echo $v['marca'].' '.$v['modelo'].' ('.$v['placas'].')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de Cita *</label>
                        <input type="date" name="fecha_cita" class="form-control" value="<?php echo $data['fecha_cita'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Hora *</label>
                        <input type="time" name="hora_inicio" class="form-control" value="<?php echo $data['hora_inicio'] ?? ''; ?>" required>
                    </div>
                </div>

                <?php if($accion == 'actualizar'): ?>
                <div class="mb-3">
                    <label class="form-label">Estado de la Cita</label>
                    <select name="estado_cita" class="form-select">
                        <option value="pendiente" <?php echo ($data['estado_cita'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmada" <?php echo ($data['estado_cita'] == 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="terminada" <?php echo ($data['estado_cita'] == 'terminada') ? 'selected' : ''; ?>>Terminada</option>
                        <option value="cancelada" <?php echo ($data['estado_cita'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Notas / Diagnóstico</label>
                    <textarea name="notas_cliente" class="form-control" rows="3"><?php echo $data['notas_cliente'] ?? ''; ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">Guardar Cita</button>
                    <a href="cita.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>