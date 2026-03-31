<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4><?php echo ($accion == 'actualizar') ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h4>
                </div>
                <div class="card-body">
                    <form action="usuario.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" 
                          method="POST" 
                          class="needs-validation" 
                          novalidate>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" 
                                       name="nombre" 
                                       class="form-control" 
                                       value="<?php echo $data['nombre'] ?? ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" 
                                       name="apellidos" 
                                       class="form-control" 
                                       value="<?php echo trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')); ?>">

                                 
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo $data['email'] ?? ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                Contraseña 
                                <?php echo ($accion == 'actualizar') ? '(Dejar vacío para no cambiar)' : '*'; ?>
                            </label>
                            <input type="password" 
                                   name="contrasena" 
                                   class="form-control" 
                                   minlength="6"
                                   <?php echo ($accion == 'crear') ? 'required' : ''; ?>>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" 
                                   name="telefono" 
                                   class="form-control" 
                                   pattern="[0-9]{10}"
                                   placeholder="10 dígitos"
                                   value="<?php echo $data['telefono'] ?? ''; ?>">
                        </div>
                        
                
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" 
                                   name="direccion" 
                                   class="form-control" 
                                   value="<?php echo $data['direccion'] ?? ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" 
                                       name="ciudad" 
                                       class="form-control" 
                                       value="<?php echo $data['ciudad'] ?? ''; ?>">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado</label>
                                <input type="text" 
                                       name="estado" 
                                       class="form-control" 
                                       value="<?php echo $data['estado'] ?? ''; ?>">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">C.P.</label>
                                <input type="text" 
                                       name="codigo_postal" 
                                       class="form-control" 
                                       pattern="[0-9]{5}"
                                       value="<?php echo $data['codigo_postal'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <?php if($app->esAdmin()): ?>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-select">
                                <option value="cliente" <?php echo (($data['rol'] ?? '') == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                <option value="tecnico" <?php echo (($data['rol'] ?? '') == 'tecnico') ? 'selected' : ''; ?>>Técnico</option>
                                <option value="admin" <?php echo (($data['rol'] ?? '') == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="enviar" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="usuario.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación Bootstrap
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>