<div class="container mt-4" style="max-width:600px">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-edit"></i>
                Actualizar Orden #<?= str_pad($orden['id_orden'], 4, '0', STR_PAD_LEFT) ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="orden.php?accion=actualizar&id=<?= $orden['id_orden'] ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="estado_orden" class="form-select">
                        <?php foreach (['pendiente','procesando','completada','cancelada'] as $est): ?>
                        <option value="<?= $est ?>" <?= $orden['estado_orden'] === $est ? 'selected' : '' ?>>
                            <?= ucfirst($est) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Método de pago</label>
                    <select name="metodo_pago" class="form-select">
                        <?php foreach (['Efectivo','Tarjeta de crédito','Tarjeta de débito','Transferencia','Pendiente'] as $mp): ?>
                        <option value="<?= $mp ?>" <?= ($orden['metodo_pago'] ?? '') === $mp ? 'selected' : '' ?>>
                            <?= $mp ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notas especiales</label>
                    <textarea name="notas_especiales" class="form-control" rows="3"><?= htmlspecialchars($orden['notas_especiales'] ?? '') ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="enviar" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="orden.php?accion=ver&id=<?= $orden['id_orden'] ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
