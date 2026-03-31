<?php
$esEditar  = !empty($data['id_refaccion']);
$accionUrl = $esEditar
    ? "refaccion.php?accion=actualizar&id={$data['id_refaccion']}"
    : "refaccion.php?accion=crear";
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
            <i class="fas fa-<?= $esEditar ? 'edit' : 'plus-circle' ?>"></i>
            <h5 class="mb-0"><?= $esEditar ? 'Editar Refacción' : 'Nueva Refacción' ?></h5>
        </div>
        <div class="card-body">
            <form action="<?= $accionUrl ?>" method="POST" enctype="multipart/form-data">

                <!-- Fila 1: Código, Nombre, Categoría -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo_producto" class="form-control"
                               value="<?= htmlspecialchars($data['codigo_producto'] ?? '') ?>" required
                               placeholder="Ej. REF-001">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required
                               placeholder="Nombre de la refacción">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>"
                                <?= ($data['id_categoria'] ?? '') == $cat['id_categoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre_categoria']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Fila 2: Marca, Precio, Stock actual, Stock mínimo -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Marca</label>
                        <input type="text" name="marca_refaccion" class="form-control"
                               value="<?= htmlspecialchars($data['marca_refaccion'] ?? '') ?>"
                               placeholder="Ej. Bosch, NGK">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Precio <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="precio" class="form-control"
                                   value="<?= $data['precio'] ?? '' ?>" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Stock actual</label>
                        <input type="number" min="0" name="stock_actual" class="form-control"
                               value="<?= $data['stock_actual'] ?? 0 ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Stock mínimo</label>
                        <input type="number" min="0" name="stock_minimo" class="form-control"
                               value="<?= $data['stock_minimo'] ?? 5 ?>">
                    </div>
                </div>

                <!-- Fila 3: Peso, Estado -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Peso (kg)</label>
                        <input type="number" step="0.01" min="0" name="peso" class="form-control"
                               value="<?= $data['peso'] ?? '' ?>" placeholder="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado_producto" class="form-select">
                            <?php foreach (['disponible' => 'Disponible', 'agotado' => 'Agotado', 'descontinuado' => 'Descontinuado'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($data['estado_producto'] ?? 'disponible') === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                              placeholder="Descripción general de la refacción"><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
                </div>

                <!-- Especificaciones técnicas -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Especificaciones técnicas</label>
                    <textarea name="especificaciones_tecnicas" class="form-control" rows="3"
                              placeholder="Ej. Voltaje: 12V, Diámetro: 14mm..."><?= htmlspecialchars($data['especificaciones_tecnicas'] ?? '') ?></textarea>
                </div>

                <!-- Imagen -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Imagen del producto</label>

                    <?php if (!empty($data['imagen'])): ?>
                    <div class="mb-2 d-flex align-items-center gap-3">
                        <img src="../uploads/refacciones/<?= htmlspecialchars($data['imagen']) ?>"
                             alt="Imagen actual" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
                        <div>
                            <p class="mb-1 small text-muted">Imagen actual</p>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="eliminar_imagen" id="eliminarImg" value="1">
                                <label class="form-check-label text-danger small" for="eliminarImg">
                                    Eliminar imagen actual
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <input type="file" name="imagen" class="form-control" accept="image/*"
                           id="inputImagen">
                    <small class="text-muted">JPG, PNG, WEBP — máx. 5MB.
                        <?= !empty($data['imagen']) ? 'Sube una nueva para reemplazar la actual.' : '' ?>
                    </small>

                    <!-- Vista previa -->
                    <div id="preview" class="mt-2" style="display:none">
                        <img id="previewImg" src="" alt="Vista previa"
                             class="img-thumbnail" style="width:120px;height:120px;object-fit:cover;">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $esEditar ? 'Guardar cambios' : 'Agregar refacción' ?>
                    </button>
                    <a href="refaccion.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('inputImagen').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('preview').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>

<?php if ($esEditar): ?>
<!-- ===== SECCIÓN COMPATIBILIDAD ===== -->
<div class="container mt-4 px-0">
    <div class="card border-warning shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-car"></i> Compatibilidad de Vehículos</h6>
            <?php if($app->verificarPermiso('compatibilidad_gestionar')): ?>
            <button type="button" class="btn btn-sm btn-dark"
                    data-bs-toggle="modal" data-bs-target="#modalCompatibilidad">
                <i class="fas fa-plus"></i> Agregar
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th class="text-center">Años</th>
                        <?php if($app->verificarPermiso('compatibilidad_gestionar')): ?>
                        <th class="text-end">Eliminar</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($compatibilidades)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class="fas fa-car-side"></i> Sin compatibilidades registradas
                            </td>
                        </tr>
                    <?php else: foreach ($compatibilidades as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['marca_vehiculo']) ?></strong></td>
                            <td><?= htmlspecialchars($c['modelo_vehiculo']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    <?= $c['anio_inicio'] ?> – <?= $c['anio_fin'] ?>
                                </span>
                            </td>
                            <?php if($app->verificarPermiso('compatibilidad_gestionar')): ?>
                            <td class="text-end pe-3">
                                <a href="compatibilidad.php?accion=borrar&id=<?= $c['id_compatibilidad'] ?>&id_refaccion=<?= $data['id_refaccion'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Eliminar este vehículo compatible?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal agregar compatibilidad -->
<?php if($app->verificarPermiso('compatibilidad_gestionar')): ?>
<div class="modal fade" id="modalCompatibilidad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="compatibilidad.php?accion=guardar" method="POST">
                <input type="hidden" name="id_refaccion" value="<?= $data['id_refaccion'] ?>">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-car"></i> Nueva Compatibilidad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                        <input type="text" name="marca_vehiculo" class="form-control"
                               placeholder="Ej. NISSAN" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                        <input type="text" name="modelo_vehiculo" class="form-control"
                               placeholder="Ej. Sentra" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Año inicio</label>
                            <input type="number" name="anio_inicio" class="form-control"
                                   min="1990" max="<?= date('Y')+1 ?>"
                                   placeholder="<?= date('Y')-5 ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Año fin</label>
                            <input type="number" name="anio_fin" class="form-control"
                                   min="1990" max="<?= date('Y')+1 ?>"
                                   placeholder="<?= date('Y') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="enviar" class="btn btn-warning text-dark">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; // fin $esEditar ?>
