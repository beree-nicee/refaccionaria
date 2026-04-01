<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-tags"></i> Categorías de Refacciones</h2>
        <?php if ($app->verificarPermiso('categoria_crear')): ?>
        <a href="categoria_refaccion.php?accion=crear" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Categoría
        </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if (empty($registros)): ?>
        <div class="col-12">
            <div class="alert alert-info">No hay categorías registradas</div>
        </div>
        <?php else: foreach ($registros as $r): ?>
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($r['imagen_categoria'])): ?>
                <img src="../uploads/categorias/<?= htmlspecialchars($r['imagen_categoria']) ?>"
                     class="card-img-top"
                     style="height:150px;object-fit:cover"
                     onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($r['nombre_categoria']) ?></h5>
                    <p class="card-text">
                        <small class="text-muted"><?= htmlspecialchars($r['descripcion'] ?? '') ?></small>
                    </p>
                </div>
                <?php if ($app->verificarPermiso('categoria_editar') || $app->verificarPermiso('categoria_eliminar')): ?>
                <div class="card-footer bg-white border-0 text-end">
                    <?php if ($app->verificarPermiso('categoria_editar')): ?>
                    <a href="categoria_refaccion.php?accion=actualizar&id=<?= $r['id_categoria'] ?>"
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($app->verificarPermiso('categoria_eliminar')): ?>
                    <a href="categoria_refaccion.php?accion=borrar&id=<?= $r['id_categoria'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('¿Eliminar categoría?')">
                        <i class="fas fa-trash"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>