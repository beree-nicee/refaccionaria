<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h4><?php echo ($accion == 'actualizar') ? 'Editar Empleado' : 'Nuevo Empleado'; ?></h4>
        </div>
        <div class="card-body">
            <form action="empleado.php?accion=<?php echo $accion; echo ($accion == 'actualizar') ? '&id=' . $id : ''; ?>" 
                  method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                
                <div class="alert alert-info mb-3">
                    <strong>* Obligatorios:</strong> Email, Contraseña, Nombre, Apellido Materno, Fecha Nacimiento.
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $data['email'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña <?php echo ($accion == 'crear') ? '*' : '(Vacío para no cambiar)'; ?></label>
                        <input type="password" name="contrasena" class="form-control" <?php echo ($accion == 'crear') ? 'required' : ''; ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo $data['nombre'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control" value="<?php echo $data['apellido_paterno'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Materno *</label>
                        <input type="text" name="apellido_materno" class="form-control" value="<?php echo $data['apellido_materno'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" class="form-control" value="<?php echo $data['rfc'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">CURP</label>
                        <input type="text" name="curp" class="form-control" value="<?php echo $data['curp'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha Nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $data['fecha_nacimiento'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" 
                        name="telefono"  class="form-control" 
                        value="<?php echo $data['telefono'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Fotografía</label>
                    <input type="file" name="fotografia" class="form-control" accept="image/*">
                    <?php if(!empty($data['fotografia'])): ?>
                        <img src="uploads/empleados/<?php echo $data['fotografia']; ?>" width="100" class="mt-2 border">
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="empleado.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>