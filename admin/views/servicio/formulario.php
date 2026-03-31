<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h4><?php echo ($accion == 'actualizar') ? 'Editar Servicio' : 'Nuevo Servicio'; ?></h4>
        </div>
        <div class="card-body">
            <form action="servicio.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" 
                  method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                
                <div class="mb-3">
                    <label class="form-label">Nombre del Servicio *</label>
                    <input type="text" name="nombre_servicio" class="form-control" value="<?php echo $data['nombre_servicio'] ?? ''; ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Precio Mano de Obra *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="precio_mano_obra" class="form-control" value="<?php echo $data['precio_mano_obra'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tiempo Estimado (Minutos)</label>
                        <input type="number" name="tiempo_estimado" class="form-control" value="<?php echo $data['tiempo_estimado'] ?? ''; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo $data['descripcion'] ?? ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagen del Servicio</label>
                    <input type="file" name="imagen_servicio" class="form-control" accept="image/*">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="servicio.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>