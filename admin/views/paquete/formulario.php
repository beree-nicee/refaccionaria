<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h4><?php echo ($accion == 'actualizar') ? 'Editar Paquete' : 'Crear Nuevo Paquete'; ?></h4>
        </div>
        <div class="card-body">
            <form action="paquete.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" 
                  method="POST" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Nombre del Paquete *</label>
                        <input type="text" name="nombre_paquete" class="form-control" 
                               value="<?php echo $data['nombre_paquete'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Descuento (%)</label>
                        <input type="number" name="descuento_porcentaje" class="form-control" 
                               value="<?php echo $data['descuento_porcentaje'] ?? '0'; ?>" min="0" max="100">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo $data['descripcion'] ?? ''; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Imagen del Paquete</label>
                        <input type="file" name="imagen_paquete" class="form-control" accept="image/*">
                        <?php if(!empty($data['imagen_paquete'])): ?>
                            <div class="mt-2 text-muted">Imagen actual: <?php echo $data['imagen_paquete']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="activo" <?php echo (($data['estado'] ?? '') == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (($data['estado'] ?? '') == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Paquete
                    </button>
                    <a href="paquete.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>