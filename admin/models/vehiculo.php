<?php
require_once(__DIR__."/../sistema.class.php");

class Vehiculo extends Sistema {
    /*
    function leer() {
        $this->conectar();
        
        // Cambiamos a LEFT JOIN para que si falta el nombre o el email, 
        // al menos el vehículo aparezca en la lista
        $sql = "SELECT v.*, 
                    COALESCE(c.nombre, e.nombre, 'Sin nombre') as nombre_dueno,
                    COALESCE(u.email, 'Sin email') as email_dueno
                FROM Vehiculo v 
                LEFT JOIN Usuario u ON v.id_usuario = u.id_usuario
                LEFT JOIN Cliente c ON u.id_usuario = c.id_usuario
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario";
        
        // Si eres cliente, filtramos
        if ($this->esCliente()) {
            $sql .= " WHERE v.id_usuario = :id_usuario";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($this->esCliente()) {
            $stmt->execute([':id_usuario' => $this->obtenerIdUsuario()]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    */
    function leer() {
        $this->conectar();
        
        // Quitamos el filtro de id_usuario para ver TODOS los coches
        $sql = "SELECT v.*, u.email as email_dueno 
                FROM Vehiculo v 
                LEFT JOIN Usuario u ON v.id_usuario = u.id_usuario 
                ORDER BY v.id_vehiculo DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function crear($data) {
        //$this->requiereLogin();
        $data = $this->sanitizar($data);
        
        // Validaciones
        if (empty($data['marca']) || empty($data['modelo'])) {
            throw new Exception("Marca y modelo son obligatorios");
        }
        
        $anio_actual = date('Y');
        if ($data['anio'] < 1990 || $data['anio'] > ($anio_actual + 1)) {
            throw new Exception("Año inválido");
        }
        
        // VIN debe ser 17 caracteres si se proporciona
        if (!empty($data['numero_serie_vin']) && strlen($data['numero_serie_vin']) != 17) {
            throw new Exception("El VIN debe tener 17 caracteres");
        }
        
        $this->conectar();
        
        // Cliente crea para sí mismo
        //$id_usuario = $this->esCliente() ? $this->obtenerIdUsuario() : $data['id_usuario'];
        
        $sql = "INSERT INTO Vehiculo (id_usuario, marca, modelo, anio, numero_serie_vin, placas, notas) 
                VALUES (:id_usuario, :marca, :modelo, :anio, :vin, :placas, :notas)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':marca' => $data['marca'],
            ':modelo' => $data['modelo'],
            ':anio' => $data['anio'],
            ':vin' => $data['numero_serie_vin'] ?? null,
            ':placas' => $data['placas'] ?? null,
            ':notas' => $data['notas'] ?? null
        ]);
        
        return $stmt->rowCount();
    }
    
    function actualizar($id, $data) {
        $vehiculo = $this->leerUno($id);
        
        // Cliente solo puede editar sus vehículos
        //if ($this->esCliente() && $vehiculo['id_usuario'] != $this->obtenerIdUsuario()) {
          //  throw new Exception("No tienes permiso para editar este vehículo");
        //}
        
        $data = $this->sanitizar($data);
        $this->conectar();
        
        $sql = "UPDATE Vehiculo SET 
                marca = :marca, 
                modelo = :modelo, 
                anio = :anio, 
                numero_serie_vin = :vin, 
                placas = :placas, 
                notas = :notas 
                WHERE id_vehiculo = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':marca' => $data['marca'],
            ':modelo' => $data['modelo'],
            ':anio' => $data['anio'],
            ':vin' => $data['numero_serie_vin'] ?? null,
            ':placas' => $data['placas'] ?? null,
            ':notas' => $data['notas'] ?? null,
            ':id' => $id
        ]);
        
        return $stmt->rowCount();
    }
    
    function borrar($id) {
        $vehiculo = $this->leerUno($id);
        
        // Cliente solo puede eliminar sus vehículos
        //if ($this->esCliente() && $vehiculo['id_usuario'] != $this->obtenerIdUsuario()) {
          //  throw new Exception("No tienes permiso para eliminar este vehículo");
        //}
        
        $this->conectar();
        $sql = "DELETE FROM Vehiculo WHERE id_vehiculo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->rowCount();
    }
    
    function obtenerMarcas() {
        $this->conectar();
        $sql = "SELECT DISTINCT marca FROM Vehiculo ORDER BY marca";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    public function obtenerUsuarios() {
        $this->conectar();
        // Traemos el ID y el Email para identificar al dueño en el select
        // Si prefieres mostrar el nombre, puedes hacer un JOIN aquí también
        $sql = "SELECT id_usuario, email FROM Usuario ORDER BY email ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>