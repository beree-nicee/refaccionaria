<?php
require_once(__DIR__."/../sistema.class.php");

class Cita extends Sistema {
    
    public function leer() {
        $this->conectar();
        
        // Base de la consulta
        $sql = "SELECT ci.*, 
                       u.email as cliente_email,
                       CONCAT(v.marca, ' ', v.modelo, ' (', v.placas, ')') as vehiculo_info,
                       s.nombre_servicio
                FROM Cita ci
                LEFT JOIN Usuario u ON ci.id_usuario = u.id_usuario
                LEFT JOIN Vehiculo v ON ci.id_vehiculo = v.id_vehiculo
                LEFT JOIN Servicio s ON ci.id_servicio = s.id_servicio";
        
        $params = [];

        // --- FILTRO DE SEGURIDAD PARA CLIENTES ---
        // Si el usuario es un cliente, solo agregamos el WHERE para su ID
        if ($this->esCliente()) {
            $sql .= " WHERE ci.id_usuario = :id_u";
            $params[':id_u'] = $this->obtenerIdUsuario();
        }
        
        $sql .= " ORDER BY ci.fecha_cita DESC, ci.hora_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerUno($id) {
        $this->conectar();
        $sql = "SELECT * FROM Cita WHERE id_cita = :id";
        
        // Si es cliente, verificamos que la cita le pertenezca
        if ($this->esCliente()) {
            $sql .= " AND id_usuario = :id_u";
            $params = [':id' => $id, ':id_u' => $this->obtenerIdUsuario()];
        } else {
            $params = [':id' => $id];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $this->conectar();
        $data = $this->sanitizar($data);
        
        // Si es cliente, forzamos que el id_usuario sea el suyo de la sesión
        $id_usuario = $this->esCliente() ? $this->obtenerIdUsuario() : $data['id_usuario'];
        
        $sql = "INSERT INTO Cita (id_usuario, id_vehiculo, id_servicio, fecha_cita, hora_inicio, estado_cita, notas_cliente) 
                VALUES (:id_u, :id_v, :id_s, :fecha, :hora, 'pendiente', :notas)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_u'   => $id_usuario,
            ':id_v'   => $data['id_vehiculo'],
            ':id_s'   => $data['id_servicio'],
            ':fecha'  => $data['fecha_cita'],
            ':hora'   => $data['hora_inicio'],
            ':notas'  => $data['notas_cliente'] ?? null
        ]);
    }

    public function actualizar($id, $data) {
        $this->conectar();
        $data = $this->sanitizar($data);
        
        $sql = "UPDATE Cita SET 
                    id_usuario = :id_u, id_vehiculo = :id_v, id_servicio = :id_s, 
                    fecha_cita = :fecha, hora_inicio = :hora, estado_cita = :estado, 
                    diagnostico_tecnico = :diag
                WHERE id_cita = :id";
        
        $params = [
            ':id_u'   => $data['id_usuario'],
            ':id_v'   => $data['id_vehiculo'],
            ':id_s'   => $data['id_servicio'],
            ':fecha'  => $data['fecha_cita'],
            ':hora'   => $data['hora_inicio'],
            ':estado' => $data['estado_cita'],
            ':diag'   => $data['diagnostico_tecnico'] ?? null,
            ':id'     => $id
        ];

        // Si es cliente, aseguramos que solo actualice la suya
        if ($this->esCliente()) {
            $sql .= " AND id_usuario = :mi_id";
            $params[':mi_id'] = $this->obtenerIdUsuario();
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function borrar($id) {
        $this->conectar();
        $sql = "DELETE FROM Cita WHERE id_cita = :id";
        
        $params = [':id' => $id];
        if ($this->esCliente()) {
            $sql .= " AND id_usuario = :id_u";
            $params[':id_u'] = $this->obtenerIdUsuario();
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function obtenerUsuarios() {
        $this->conectar();
        $sql = "SELECT id_usuario, email FROM Usuario ORDER BY email ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerServicios() {
        $this->conectar();
        $sql = "SELECT id_servicio, nombre_servicio FROM Servicio WHERE estado = 'activo' ORDER BY nombre_servicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerVehiculos() {
        $this->conectar();
        
        $sql = "SELECT v.id_vehiculo, v.marca, v.modelo, v.placas, u.email 
                FROM Vehiculo v
                LEFT JOIN Usuario u ON v.id_usuario = u.id_usuario";
        
        $params = [];
        // Filtro para que el cliente solo vea sus propios vehículos en el select
        if ($this->esCliente()) {
            $sql .= " WHERE v.id_usuario = :id_u";
            $params[':id_u'] = $this->obtenerIdUsuario();
        }

        $sql .= " ORDER BY u.email ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTecnicos() {
        $this->conectar();
        // Ajustado para usar la tabla de roles si id_rol = 2 es técnico
        return $this->db->query("SELECT e.id_empleado, e.nombre FROM Empleado e INNER JOIN Usuario u ON e.id_usuario = u.id_usuario WHERE u.id_rol = 2")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>