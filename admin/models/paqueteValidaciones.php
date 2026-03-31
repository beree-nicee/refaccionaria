<?php
require_once(__DIR__."/../sistema.class.php");

class Paquete extends Sistema {
    
    function leer() {
        $this->conectar();
        
        $sql = "SELECT * FROM Paquete_Reparacion";
        
        // Cliente solo ve activos
        if ($this->esCliente()) {
            $sql .= " WHERE estado = 'activo'";
        }
        
        $sql .= " ORDER BY fecha_creacion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function leerUno($id) {
        $this->conectar();
        $sql = "SELECT * FROM Paquete_Reparacion WHERE id_paquete = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function obtenerServicios($id_paquete) {
        $this->conectar();
        
        $sql = "SELECT ps.*, s.nombre_servicio, s.precio_mano_obra 
                FROM Paquete_Servicio ps
                INNER JOIN Servicio s ON ps.id_servicio = s.id_servicio
                WHERE ps.id_paquete = :id_paquete";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_paquete' => $id_paquete]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function obtenerRefacciones($id_paquete) {
        $this->conectar();
        
        $sql = "SELECT pr.*, r.nombre, r.precio 
                FROM Paquete_Refaccion pr
                INNER JOIN Refaccion r ON pr.id_refaccion = r.id_refaccion
                WHERE pr.id_paquete = :id_paquete";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_paquete' => $id_paquete]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function crear($data) {
        $this->validarAcceso('paquete.crear');
        $data = $this->sanitizar($data);
        
        if (empty($data['nombre_paquete'])) {
            throw new Exception("El nombre del paquete es obligatorio");
        }
        
        $this->conectar();
        $this->db->beginTransaction();
        
        try {
            // Subir imagen
            $imagen = null;
            if (isset($_FILES['imagen_paquete']) && $_FILES['imagen_paquete']['error'] === UPLOAD_ERR_OK) {
                $imagen = $this->subirImagen($_FILES['imagen_paquete'], 'paquetes');
            }
            
            // Crear paquete
            $sql = "INSERT INTO Paquete_Reparacion (nombre_paquete, descripcion, 
                    descuento_porcentaje, imagen_paquete, estado) 
                    VALUES (:nombre, :desc, :descuento, :imagen, :estado)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre_paquete'],
                ':desc' => $data['descripcion'] ?? null,
                ':descuento' => $data['descuento_porcentaje'] ?? 0,
                ':imagen' => $imagen,
                ':estado' => $data['estado'] ?? 'activo'
            ]);
            
            $id_paquete = $this->db->lastInsertId();
            
            // Agregar servicios al paquete
            if (!empty($data['servicios'])) {
                foreach ($data['servicios'] as $servicio) {
                    $sql = "INSERT INTO Paquete_Servicio (id_paquete, id_servicio, cantidad) 
                            VALUES (:id_paquete, :id_servicio, :cantidad)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':id_paquete' => $id_paquete,
                        ':id_servicio' => $servicio['id_servicio'],
                        ':cantidad' => $servicio['cantidad'] ?? 1
                    ]);
                }
            }
            
            // Agregar refacciones al paquete
            if (!empty($data['refacciones'])) {
                foreach ($data['refacciones'] as $refaccion) {
                    $sql = "INSERT INTO Paquete_Refaccion (id_paquete, id_refaccion, cantidad_requerida) 
                            VALUES (:id_paquete, :id_refaccion, :cantidad)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':id_paquete' => $id_paquete,
                        ':id_refaccion' => $refaccion['id_refaccion'],
                        ':cantidad' => $refaccion['cantidad_requerida'] ?? 1
                    ]);
                }
            }
            
            $this->db->commit();
            return $id_paquete;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            if (isset($imagen)) {
                $this->eliminarImagen($imagen, 'paquetes');
            }
            throw $e;
        }
    }
    
    function actualizar($id, $data) {
        $this->validarAcceso('paquete.actualizar');
        $data = $this->sanitizar($data);
        $this->conectar();
        
        // Procesar imagen
        if (isset($_FILES['imagen_paquete']) && $_FILES['imagen_paquete']['error'] === UPLOAD_ERR_OK) {
            $paquete = $this->leerUno($id);
            if ($paquete['imagen_paquete']) {
                $this->eliminarImagen($paquete['imagen_paquete'], 'paquetes');
            }
            $data['imagen_paquete'] = $this->subirImagen($_FILES['imagen_paquete'], 'paquetes');
        }
        
        $campos = [];
        $params = [':id' => $id];
        
        $camposPermitidos = ['nombre_paquete', 'descripcion', 'descuento_porcentaje', 
                             'imagen_paquete', 'estado'];
        
        foreach ($camposPermitidos as $campo) {
            if (isset($data[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $data[$campo];
            }
        }
        
        if (empty($campos)) return 0;
        
        $sql = "UPDATE Paquete_Reparacion SET " . implode(', ', $campos) . " WHERE id_paquete = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    function borrar($id) {
        $this->validarAcceso('paquete.eliminar');
        $this->conectar();
        
        $paquete = $this->leerUno($id);
        
        $sql = "DELETE FROM Paquete_Reparacion WHERE id_paquete = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        if ($paquete['imagen_paquete']) {
            $this->eliminarImagen($paquete['imagen_paquete'], 'paquetes');
        }
        
        return $stmt->rowCount();
    }
    
    function calcularPrecioTotal($id_paquete) {
        $servicios = $this->obtenerServicios($id_paquete);
        $refacciones = $this->obtenerRefacciones($id_paquete);
        $paquete = $this->leerUno($id_paquete);
        
        $total = 0;
        
        foreach ($servicios as $s) {
            $total += $s['precio_mano_obra'] * $s['cantidad'];
        }
        
        foreach ($refacciones as $r) {
            $total += $r['precio'] * $r['cantidad_requerida'];
        }
        
        // Aplicar descuento
        if ($paquete['descuento_porcentaje'] > 0) {
            $total = $total * (1 - ($paquete['descuento_porcentaje'] / 100));
        }
        
        return $total;
    }
}
?>