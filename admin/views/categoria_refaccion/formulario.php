<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h4><?php echo isset($id) ? 'Editar' : 'Nueva'; ?> Categoría de Refacciones</h4>
        </div>
        <div class="card-body">
            <form action="categoria_refaccion.php?accion=<?php echo isset($id) ? 'actualizar&id='.$id : 'crear'; ?>" 
                  method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label">Nombre de la Categoría</label>
                    <input type="text" name="nombre_categoria" class="form-control" 
                           value="<?php echo $data['nombre_categoria'] ?? ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo $data['descripcion'] ?? ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagen de la Categoría</label>
                    <input type="file" name="imagen_categoria" class="form-control" accept="image/*">
                    <?php if(isset($data['imagen_categoria'])): ?>
                        <div class="mt-2">
                            <img src="../uploads/categorias/<?php echo $data['imagen_categoria']; ?>" width="100" class="img-thumbnail">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-end">
                    <a href="categoria_refaccion.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="enviar" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>