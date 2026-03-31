<?php
require_once(__DIR__."/../sistema.class.php");

class Paquete extends Sistema {
    
    function leer() {
        $this->conectar();
        $sql = "SELECT * FROM Paquete_Reparacion ORDER BY id_paquete DESC";
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

    function crear($data) {
        $data = $this->sanitizar($data);
        $this->conectar();
        $imagen = null;
        if (isset($_FILES['imagen_paquete']) && $_FILES['imagen_paquete']['error'] === UPLOAD_ERR_OK) {
            $imagen = $this->subirImagen($_FILES['imagen_paquete'], 'paquetes');
        }
        
        $sql = "INSERT INTO Paquete_Reparacion (nombre_paquete, descripcion, descuento_porcentaje, imagen_paquete, estado) 
                VALUES (:nombre, :desc, :descuento, :imagen, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre_paquete'],
            ':desc' => $data['descripcion'] ?? null,
            ':descuento' => $data['descuento_porcentaje'] ?? 0,
            ':imagen' => $imagen,
            ':estado' => $data['estado'] ?? 'activo'
        ]);
    }

    function actualizar($id, $data) {
        $data = $this->sanitizar($data);
        $this->conectar();
        
        if (isset($_FILES['imagen_paquete']) && $_FILES['imagen_paquete']['error'] === UPLOAD_ERR_OK) {
            $paquete = $this->leerUno($id);
            if ($paquete['imagen_paquete']) $this->eliminarImagen($paquete['imagen_paquete'], 'paquetes');
            $data['imagen_paquete'] = $this->subirImagen($_FILES['imagen_paquete'], 'paquetes');
        }
        
        $sql = "UPDATE Paquete_Reparacion SET nombre_paquete=:nombre, descripcion=:desc, 
                descuento_porcentaje=:descuento, estado=:estado WHERE id_paquete=:id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre_paquete'],
            ':desc' => $data['descripcion'],
            ':descuento' => $data['descuento_porcentaje'],
            ':estado' => $data['estado'],
            ':id' => $id
        ]);
    }

    function borrar($id) {
        $this->conectar();
        $paquete = $this->leerUno($id);
        $sql = "DELETE FROM Paquete_Reparacion WHERE id_paquete = :id";
        if($this->db->prepare($sql)->execute([':id' => $id])){
            if ($paquete['imagen_paquete']) $this->eliminarImagen($paquete['imagen_paquete'], 'paquetes');
            return true;
        }
        return false;
    }
}
?>