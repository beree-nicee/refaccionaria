<?php
require_once(__DIR__."/../sistema.class.php");

class Servicio extends Sistema {
    
    function leer() {
        $this->conectar();
        
        $sql = "SELECT * FROM Servicio"; // Un espacio al final por seguridad
        
        if ($this->esCliente()) {
            $sql .= " WHERE estado = 'activo'";
        }
        
        // Agregamos un espacio antes de ORDER
        $sql .= " ORDER BY categoria_servicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agrega esta función para que el controlador no marque error
    public function obtenerCategorias() {
        $this->conectar();
        // Trae las categorías únicas para el select del formulario
        $sql = "SELECT DISTINCT categoria_servicio FROM Servicio WHERE categoria_servicio IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function leerUno($id) {
        $this->conectar();
        $sql = "SELECT * FROM Servicio WHERE id_servicio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function crear($data) {
        //$this->validarAcceso('servicio.crear');
        $data = $this->sanitizar($data);
        
        // Validaciones
        if (empty($data['nombre_servicio'])) {
            throw new Exception("El nombre del servicio es obligatorio");
        }
        
        if (!isset($data['precio_mano_obra']) || $data['precio_mano_obra'] < 0) {
            throw new Exception("El precio debe ser mayor o igual a 0");
        }
        
        $this->conectar();
        
        // Subir imagen si existe
        $imagen = null;
        if (isset($_FILES['imagen_servicio']) && $_FILES['imagen_servicio']['error'] === UPLOAD_ERR_OK) {
            $imagen = $this->subirImagen($_FILES['imagen_servicio'], 'servicios');
        }
        
        $sql = "INSERT INTO Servicio (nombre_servicio, descripcion, precio_mano_obra, 
                tiempo_estimado, imagen_servicio, categoria_servicio, estado) 
                VALUES (:nombre, :desc, :precio, :tiempo, :imagen, :categoria, :estado)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre_servicio'],
            ':desc' => $data['descripcion'] ?? null,
            ':precio' => $data['precio_mano_obra'],
            ':tiempo' => $data['tiempo_estimado'] ?? null,
            ':imagen' => $imagen,
            ':categoria' => $data['categoria_servicio'] ?? null,
            ':estado' => $data['estado'] ?? 'activo'
        ]);
        
        return $stmt->rowCount();
    }
    
    function actualizar($id, $data) {
        //$this->validarAcceso('servicio.actualizar');
        $data = $this->sanitizar($data);
        $this->conectar();
        
        // Procesar imagen
        if (isset($_FILES['imagen_servicio']) && $_FILES['imagen_servicio']['error'] === UPLOAD_ERR_OK) {
            $servicio = $this->leerUno($id);
            if ($servicio['imagen_servicio']) {
                $this->eliminarImagen($servicio['imagen_servicio'], 'servicios');
            }
            $data['imagen_servicio'] = $this->subirImagen($_FILES['imagen_servicio'], 'servicios');
        }
        
        $campos = [];
        $params = [':id' => $id];
        
        $camposPermitidos = ['nombre_servicio', 'descripcion', 'precio_mano_obra', 
                             'tiempo_estimado', 'categoria_servicio', 'estado', 'imagen_servicio'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($data[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $data[$campo];
            }
        }
        
        if (empty($campos)) return 0;
        
        $sql = "UPDATE Servicio SET " . implode(', ', $campos) . " WHERE id_servicio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    function borrar($id) {
        //$this->validarAcceso('servicio.eliminar');
        $this->conectar();
        
        $servicio = $this->leerUno($id);
        
        $sql = "DELETE FROM Servicio WHERE id_servicio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        if ($servicio['imagen_servicio']) {
            $this->eliminarImagen($servicio['imagen_servicio'], 'servicios');
        }
        
        return $stmt->rowCount();
    }
}
?>