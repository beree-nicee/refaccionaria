<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Categorías de Refacciones</h2>
        <a href="categoria_refaccion.php?accion=crear" class="btn btn-success">Nueva Categoría</a>
    </div>

    <div class="row">
        <?php foreach($registros as $r): ?>
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <!-- <?php if($r['imagen_categoria']): ?>
                    <img src="../uploads/categorias/<?php echo $r['imagen_categoria']; ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                <?php endif; ?> -->
                <div class="card-body">
                    <h5 class="card-title"><?php echo $r['nombre_categoria']; ?></h5>
                    <p class="card-text"><small><?php echo $r['descripcion']; ?></small></p>
                </div>
                <div class="card-footer bg-white border-0 text-end">
                    <a href="categoria_refaccion.php?accion=actualizar&id=<?php echo $r['id_categoria']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <a href="categoria_refaccion.php?accion=borrar&id=<?php echo $r['id_categoria']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar categoría?')"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

