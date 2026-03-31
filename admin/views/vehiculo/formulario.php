<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4><?php echo ($accion == 'actualizar') ? 'Editar Vehículo' : 'Nuevo Vehículo'; ?></h4>
        </div>
        <div class="card-body">
            <form action="vehiculo.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" 
                  method="POST" class="needs-validation" novalidate>
                
                <div class="mb-3">
                    <label class="form-label">Dueño (Usuario) *</label>
                    <select name="id_usuario" class="form-select" required>
                        <option value="">Seleccione un dueño...</option>
                        <?php foreach($usuarios as $u): ?>
                            <option value="<?php echo $u['id_usuario']; ?>" <?php echo (($data['id_usuario'] ?? '') == $u['id_usuario']) ? 'selected' : ''; ?>>
                                <?php echo $u['email']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Marca *</label>
                        <input type="text" name="marca" class="form-control" value="<?php echo $data['marca'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Modelo *</label>
                        <input type="text" name="modelo" class="form-control" value="<?php echo $data['modelo'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Año *</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo $data['anio'] ?? ''; ?>" min="1900" max="2099" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">VIN (Número de Serie)</label>
                        <input type="text" name="numero_serie_vin" class="form-control" value="<?php echo $data['numero_serie_vin'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Placas</label>
                        <input type="text" name="placas" class="form-control" value="<?php echo $data['placas'] ?? ''; ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="vehiculo.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>