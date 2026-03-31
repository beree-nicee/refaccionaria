<div class="modal fade" id="modalCompatibilidad" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="compatibilidad.php?accion=guardar" method="POST">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title">Nueva Compatibilidad</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_refaccion" value="<?php echo $id; ?>">
          
          <div class="mb-3">
            <label class="form-label">Marca</label>
            <input type="text" name="marca_vehiculo" class="form-control" placeholder="Ej. Nissan" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Modelo</label>
            <input type="text" name="modelo_vehiculo" class="form-control" placeholder="Ej. Sentra" required>
          </div>
          
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">Año Inicio</label>
              <input type="number" name="anio_inicio" class="form-control" placeholder="2010" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Año Fin</label>
              <input type="number" name="anio_fin" class="form-control" placeholder="2015" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" name="enviar" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>