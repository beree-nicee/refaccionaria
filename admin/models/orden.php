<?php
require_once(__DIR__."/../sistema.class.php");

class Orden extends Sistema {
    
    function leer() {
        $this->conectar();
        
        $sql = "SELECT o.*, u.nombre, u.apellidos, u.email 
                FROM Orden_Compra o
                INNER JOIN Usuario u ON o.id_usuario = u.id_usuario";
        
        // Cliente solo ve sus órdenes
        if ($this->esCliente()) {
            $sql .= " WHERE o.id_usuario = :id_usuario";
        }
        
        $sql .= " ORDER BY o.fecha_orden DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($this->esCliente()) {
            $stmt->execute([':id_usuario' => $this->obtenerIdUsuario()]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function leerUno($id) {
        $this->conectar();
        
        $sql = "SELECT o.*, u.nombre, u.apellidos, u.email 
                FROM Orden_Compra o
                INNER JOIN Usuario u ON o.id_usuario = u.id_usuario
                WHERE o.id_orden = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cliente solo puede ver sus órdenes
        if ($this->esCliente() && $orden['id_usuario'] != $this->obtenerIdUsuario()) {
            throw new Exception("No tienes permiso para ver esta orden");
        }
        
        return $orden;
    }
    
    function obtenerDetalles($id_orden) {
        $this->conectar();
        
        // Detalles de refacciones
        $sql = "SELECT d.*, r.nombre, r.codigo_producto 
                FROM Detalle_Orden_Refaccion d
                INNER JOIN Refaccion r ON d.id_refaccion = r.id_refaccion
                WHERE d.id_orden = :id_orden";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_orden' => $id_orden]);
        $refacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Detalles de servicios
        $sql = "SELECT d.*, s.nombre_servicio 
                FROM Detalle_Orden_Servicio d
                INNER JOIN Servicio s ON d.id_servicio = s.id_servicio
                WHERE d.id_orden = :id_orden";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_orden' => $id_orden]);
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'refacciones' => $refacciones,
            'servicios' => $servicios
        ];
    }
    
    function crear($data) {
        $this->requiereLogin();
        $data = $this->sanitizar($data);
        
        $this->conectar();
        $this->db->beginTransaction();
        
        try {
            $id_usuario = $this->esCliente() ? $this->obtenerIdUsuario() : $data['id_usuario'];
            
            // Calcular totales
            $total_refacciones = $data['total_refacciones'] ?? 0;
            $total_servicios = $data['total_servicios'] ?? 0;
            $total_general = $total_refacciones + $total_servicios;
            
            // Crear orden
            $sql = "INSERT INTO Orden_Compra (id_usuario, total_refacciones, total_servicios, 
                    total_general, metodo_pago, notas_especiales, estado_orden) 
                    VALUES (:id_usuario, :total_ref, :total_serv, :total_gen, :metodo, :notas, :estado)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':total_ref' => $total_refacciones,
                ':total_serv' => $total_servicios,
                ':total_gen' => $total_general,
                ':metodo' => $data['metodo_pago'] ?? 'Pendiente',
                ':notas' => $data['notas_especiales'] ?? null,
                ':estado' => 'pendiente'
            ]);
            
            $id_orden = $this->db->lastInsertId();
            
            // Insertar detalles de refacciones
            if (!empty($data['refacciones'])) {
                require_once(__DIR__."/refaccion.php");
                $refaccionObj = new Refaccion();
                
                foreach ($data['refacciones'] as $item) {
                    $refaccion = $refaccionObj->leerUno($item['id_refaccion']);
                    $subtotal = $refaccion['precio'] * $item['cantidad'];
                    
                    $sql = "INSERT INTO Detalle_Orden_Refaccion 
                            (id_orden, id_refaccion, cantidad, precio_unitario, subtotal) 
                            VALUES (:id_orden, :id_refaccion, :cantidad, :precio, :subtotal)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':id_orden' => $id_orden,
                        ':id_refaccion' => $item['id_refaccion'],
                        ':cantidad' => $item['cantidad'],
                        ':precio' => $refaccion['precio'],
                        ':subtotal' => $subtotal
                    ]);
                    
                    // Descontar stock
                    $refaccionObj->descontarStock($item['id_refaccion'], $item['cantidad']);
                }
            }
            
            // Insertar detalles de servicios
            if (!empty($data['servicios'])) {
                require_once(__DIR__."/servicio.php");
                $servicioObj = new Servicio();
                
                foreach ($data['servicios'] as $item) {
                    $servicio = $servicioObj->leerUno($item['id_servicio']);
                    
                    $sql = "INSERT INTO Detalle_Orden_Servicio 
                            (id_orden, id_servicio, precio_servicio) 
                            VALUES (:id_orden, :id_servicio, :precio)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':id_orden' => $id_orden,
                        ':id_servicio' => $item['id_servicio'],
                        ':precio' => $servicio['precio_mano_obra']
                    ]);
                }
            }
            
            $this->db->commit();
            return $id_orden;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    function actualizar($id, $data) {
        $orden = $this->leerUno($id);
        
        // Solo admin o técnico pueden actualizar
        if ($this->esCliente()) {
            throw new Exception("No tienes permiso para actualizar órdenes");
        }
        
        $data = $this->sanitizar($data);
        $this->conectar();
        
        $campos = [];
        $params = [':id' => $id];
        
        if (isset($data['estado_orden'])) {
            $campos[] = "estado_orden = :estado";
            $params[':estado'] = $data['estado_orden'];
        }
        
        if (isset($data['metodo_pago'])) {
            $campos[] = "metodo_pago = :metodo";
            $params[':metodo'] = $data['metodo_pago'];
        }
        
        if (isset($data['notas_especiales'])) {
            $campos[] = "notas_especiales = :notas";
            $params[':notas'] = $data['notas_especiales'];
        }
        
        if (empty($campos)) return 0;
        
        $sql = "UPDATE Orden_Compra SET " . implode(', ', $campos) . " WHERE id_orden = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    function borrar($id) {
        $this->validarAcceso('orden.eliminar');
        $this->conectar();
        
        $sql = "DELETE FROM Orden_Compra WHERE id_orden = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->rowCount();
    }
    
    function cancelar($id) {
        $orden = $this->leerUno($id);
        
        // Cliente solo puede cancelar sus órdenes pendientes
        if ($this->esCliente()) {
            if ($orden['id_usuario'] != $this->obtenerIdUsuario()) {
                throw new Exception("No tienes permiso");
            }
            if ($orden['estado_orden'] != 'pendiente') {
                throw new Exception("Solo puedes cancelar órdenes pendientes");
            }
        }
        
        return $this->actualizar($id, ['estado_orden' => 'cancelada']);
    }
}
?>