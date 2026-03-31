<?php
require_once(__DIR__."/../sistema.class.php");

class Carrito extends Sistema {
    
    private function obtenerOCrearCarrito() {
        $this->requiereLogin();
        $this->conectar();
        
        $id_usuario = $this->obtenerIdUsuario();
        
        // Buscar carrito existente
        $sql = "SELECT id_carrito FROM Carrito WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $carrito = $stmt->fetch();
        
        if ($carrito) {
            return $carrito['id_carrito'];
        }
        
        // Crear nuevo carrito
        $sql = "INSERT INTO Carrito (id_usuario) VALUES (:id_usuario)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        
        return $this->db->lastInsertId();
    }
    
    function leer() {
        $this->requiereLogin();
        $this->conectar();
        
        $id_carrito = $this->obtenerOCrearCarrito();
        
        $sql = "SELECT ci.*, r.nombre, r.precio, r.imagen, r.stock_actual,
                (ci.cantidad * r.precio) as subtotal
                FROM Carrito_Item ci
                INNER JOIN Refaccion r ON ci.id_refaccion = r.id_refaccion
                WHERE ci.id_carrito = :id_carrito
                ORDER BY ci.fecha_agregado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_carrito' => $id_carrito]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function agregar($id_refaccion, $cantidad = 1) {
        $this->requiereLogin();
        
        if ($cantidad <= 0) {
            throw new Exception("La cantidad debe ser mayor a 0");
        }
        
        // Verificar stock
        require_once(__DIR__."/refaccion.php");
        $refaccionObj = new Refaccion();
        
        if (!$refaccionObj->verificarStock($id_refaccion, $cantidad)) {
            throw new Exception("Stock insuficiente");
        }
        
        $this->conectar();
        $id_carrito = $this->obtenerOCrearCarrito();
        
        // Verificar si ya existe
        $sql = "SELECT id_item, cantidad FROM Carrito_Item 
                WHERE id_carrito = :id_carrito AND id_refaccion = :id_refaccion";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_carrito' => $id_carrito,
            ':id_refaccion' => $id_refaccion
        ]);
        
        $item = $stmt->fetch();
        
        if ($item) {
            // Actualizar cantidad
            $nueva_cantidad = $item['cantidad'] + $cantidad;
            
            if (!$refaccionObj->verificarStock($id_refaccion, $nueva_cantidad)) {
                throw new Exception("Stock insuficiente para esa cantidad");
            }
            
            $sql = "UPDATE Carrito_Item SET cantidad = :cantidad WHERE id_item = :id_item";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cantidad' => $nueva_cantidad,
                ':id_item' => $item['id_item']
            ]);
        } else {
            // Insertar nuevo
            $sql = "INSERT INTO Carrito_Item (id_carrito, id_refaccion, cantidad) 
                    VALUES (:id_carrito, :id_refaccion, :cantidad)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_carrito' => $id_carrito,
                ':id_refaccion' => $id_refaccion,
                ':cantidad' => $cantidad
            ]);
        }
        
        return true;
    }
    
    function actualizar($id_item, $cantidad) {
        $this->requiereLogin();
        $this->conectar();
        
        if ($cantidad <= 0) {
            return $this->eliminar($id_item);
        }
        
        $sql = "UPDATE Carrito_Item SET cantidad = :cantidad WHERE id_item = :id_item";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cantidad' => $cantidad,
            ':id_item' => $id_item
        ]);
        
        return $stmt->rowCount();
    }
    
    function eliminar($id_item) {
        $this->requiereLogin();
        $this->conectar();
        
        $sql = "DELETE FROM Carrito_Item WHERE id_item = :id_item";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_item' => $id_item]);
        
        return $stmt->rowCount();
    }
    
    function vaciar() {
        $this->requiereLogin();
        $this->conectar();
        
        $id_carrito = $this->obtenerOCrearCarrito();
        
        $sql = "DELETE FROM Carrito_Item WHERE id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_carrito' => $id_carrito]);
        
        return $stmt->rowCount();
    }
    
    function contarItems() {
        $this->requiereLogin();
        $this->conectar();
        
        $id_carrito = $this->obtenerOCrearCarrito();
        
        $sql = "SELECT SUM(cantidad) as total FROM Carrito_Item WHERE id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_carrito' => $id_carrito]);
        
        $resultado = $stmt->fetch();
        return $resultado['total'] ?? 0;
    }
    
    function calcularTotal() {
        $items = $this->leer();
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }
}
?>