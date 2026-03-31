<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><?php echo ($accion == 'actualizar') ? 'Editar Cliente' : 'Nuevo Cliente'; ?></h4>
        </div>
        <div class="card-body">
            <form action="cliente.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" 
                  method="POST" 
                  class="needs-validation" 
                  novalidate>
                
                <div class="alert alert-info mb-3">
                    <strong>* Campos obligatorios:</strong> Email, Contraseña, Nombre, Apellido Materno y Fecha de Nacimiento.
                </div>
                
                <h5 class="border-bottom pb-2 mb-3">Datos de Cuenta</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo $data['email'] ?? ''; ?>" required>
                        <div class="invalid-feedback">Ingrese un correo válido</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña <?php echo ($accion == 'crear') ? '*' : '(Vacío para no cambiar)'; ?></label>
                        <input type="password" name="contrasena" class="form-control" 
                               minlength="6" <?php echo ($accion == 'crear') ? 'required' : ''; ?>>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>
                </div>

                <h5 class="border-bottom pb-2 mb-3 mt-3">Datos Personales</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre(s) *</label>
                        <input type="text" name="nombre" class="form-control" 
                               value="<?php echo $data['nombre'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control" 
                               value="<?php echo $data['apellido_paterno'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Materno *</label>
                        <input type="text" name="apellido_materno" class="form-control" 
                               value="<?php echo $data['apellido_materno'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" pattern="[0-9]{10}"
                               placeholder="10 dígitos" value="<?php echo $data['telefono'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de Nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" 
                               value="<?php echo $data['fecha_nacimiento'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" 
                               value="<?php echo $data['direccion'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" 
                               value="<?php echo $data['ciudad'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                    <a href="cliente.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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