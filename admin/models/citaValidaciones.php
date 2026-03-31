<?php
require_once(__DIR__."/../sistema.class.php");

class Cita extends Sistema {
    
    function leer() {
        $this->conectar();
        
        $sql = "SELECT c.*, u.nombre, u.apellidos, s.nombre_servicio, 
                v.marca, v.modelo, v.anio,
                t.nombre as nombre_tecnico, t.apellidos as apellidos_tecnico
                FROM Cita c
                INNER JOIN Usuario u ON c.id_usuario = u.id_usuario
                INNER JOIN Servicio s ON c.id_servicio = s.id_servicio
                LEFT JOIN Vehiculo v ON c.id_vehiculo = v.id_vehiculo
                LEFT JOIN Usuario t ON c.id_tecnico_asignado = t.id_usuario";
        
        // Cliente solo ve sus citas
        if ($this->esCliente()) {
            $sql .= " WHERE c.id_usuario = :id_usuario";
        }
        
        $sql .= " ORDER BY c.fecha_cita DESC, c.hora_inicio DESC";
        
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
        
        $sql = "SELECT c.*, u.nombre, u.apellidos, s.nombre_servicio, 
                v.marca, v.modelo, v.anio,
                t.nombre as nombre_tecnico, t.apellidos as apellidos_tecnico
                FROM Cita c
                INNER JOIN Usuario u ON c.id_usuario = u.id_usuario
                INNER JOIN Servicio s ON c.id_servicio = s.id_servicio
                LEFT JOIN Vehiculo v ON c.id_vehiculo = v.id_vehiculo
                LEFT JOIN Usuario t ON c.id_tecnico_asignado = t.id_usuario
                WHERE c.id_cita = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Cliente solo puede ver sus citas
        if ($this->esCliente() && $cita['id_usuario'] != $this->obtenerIdUsuario()) {
            throw new Exception("No tienes permiso para ver esta cita");
        }
        
        return $cita;
    }
    
    function crear($data) {
        $this->requiereLogin();
        $data = $this->sanitizar($data);
        
        // Validaciones
        if (empty($data['fecha_cita']) || empty($data['hora_inicio'])) {
            throw new Exception("Fecha y hora son obligatorias");
        }
        
        // Validar fecha futura
        if (strtotime($data['fecha_cita']) < strtotime('today')) {
            throw new Exception("La fecha debe ser futura");
        }
        
        // Validar horario laboral (9am-7pm)
        $hora = strtotime($data['hora_inicio']);
        if ($hora < strtotime('09:00') || $hora >= strtotime('19:00')) {
            throw new Exception("Horario de atención: 9:00 AM - 7:00 PM");
        }
        
        // Verificar disponibilidad
        if ($this->horaOcupada($data['fecha_cita'], $data['hora_inicio'])) {
            throw new Exception("Ya existe una cita a esa hora");
        }
        
        $this->conectar();
        
        $id_usuario = $this->esCliente() ? $this->obtenerIdUsuario() : $data['id_usuario'];
        
        $sql = "INSERT INTO Cita (id_usuario, id_vehiculo, id_servicio, fecha_cita, 
                hora_inicio, hora_fin, notas_cliente, estado_cita) 
                VALUES (:id_usuario, :id_vehiculo, :id_servicio, :fecha, :hora_inicio, 
                :hora_fin, :notas, :estado)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':id_vehiculo' => $data['id_vehiculo'] ?? null,
            ':id_servicio' => $data['id_servicio'],
            ':fecha' => $data['fecha_cita'],
            ':hora_inicio' => $data['hora_inicio'],
            ':hora_fin' => $data['hora_fin'] ?? null,
            ':notas' => $data['notas_cliente'] ?? null,
            ':estado' => 'pendiente'
        ]);
        
        return $stmt->rowCount();
    }
    
    function actualizar($id, $data) {
        $cita = $this->leerUno($id);
        
        // Cliente solo puede actualizar sus citas pendientes
        if ($this->esCliente()) {
            if ($cita['id_usuario'] != $this->obtenerIdUsuario()) {
                throw new Exception("No tienes permiso");
            }
            if ($cita['estado_cita'] != 'pendiente') {
                throw new Exception("Solo puedes modificar citas pendientes");
            }
        }
        
        $data = $this->sanitizar($data);
        $this->conectar();
        
        $campos = [];
        $params = [':id' => $id];
        
        $camposPermitidos = ['fecha_cita', 'hora_inicio', 'hora_fin', 'notas_cliente', 
                             'estado_cita', 'diagnostico_tecnico', 'id_tecnico_asignado', 'id_vehiculo'];
        
        // Cliente no puede cambiar técnico ni diagnóstico
        if ($this->esCliente()) {
            $camposPermitidos = array_diff($camposPermitidos, ['diagnostico_tecnico', 'id_tecnico_asignado']);
        }
        
        foreach ($camposPermitidos as $campo) {
            if (isset($data[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $data[$campo];
            }
        }
        
        if (empty($campos)) return 0;
        
        $sql = "UPDATE Cita SET " . implode(', ', $campos) . " WHERE id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    function borrar($id) {
        $cita = $this->leerUno($id);
        
        // Cliente solo puede cancelar sus citas pendientes
        if ($this->esCliente()) {
            if ($cita['id_usuario'] != $this->obtenerIdUsuario()) {
                throw new Exception("No tienes permiso");
            }
            if ($cita['estado_cita'] != 'pendiente') {
                throw new Exception("Solo puedes cancelar citas pendientes");
            }
            // Cliente solo cancela, no elimina
            return $this->actualizar($id, ['estado_cita' => 'cancelada']);
        }
        
        $this->conectar();
        $sql = "DELETE FROM Cita WHERE id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->rowCount();
    }
    
    private function horaOcupada($fecha, $hora) {
        $this->conectar();
        
        $sql = "SELECT COUNT(*) as ocupada FROM Cita 
                WHERE fecha_cita = :fecha 
                AND hora_inicio = :hora 
                AND estado_cita NOT IN ('cancelada', 'completada')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha' => $fecha,
            ':hora' => $hora
        ]);
        
        $resultado = $stmt->fetch();
        return $resultado['ocupada'] > 0;
    }
    
    function obtenerTecnicos() {
        $this->conectar();
        
        $sql = "SELECT id_usuario, nombre, apellidos FROM Usuario WHERE rol = 'tecnico'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function obtenerServicios() {
        $this->conectar();
        
        $sql = "SELECT id_servicio, nombre_servicio, precio_mano_obra 
                FROM Servicio WHERE estado = 'activo' ORDER BY nombre_servicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function obtenerVehiculos() {
        $this->conectar();
        
        $sql = "SELECT id_vehiculo, marca, modelo, anio FROM Vehiculo";
        
        if ($this->esCliente()) {
            $sql .= " WHERE id_usuario = :id_usuario";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($this->esCliente()) {
            $stmt->execute([':id_usuario' => $this->obtenerIdUsuario()]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

