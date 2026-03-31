<?php
require_once(__DIR__."/../sistema.class.php");

class Cita extends Sistema {
    public function leer() {
        $this->conectar();
        // Unimos Cita con Usuario, Vehiculo y Servicio
        $sql = "SELECT ci.*, 
                       u.email as cliente_email,
                       CONCAT(v.marca, ' ', v.modelo, ' (', v.placas, ')') as vehiculo_info,
                       s.nombre_servicio
                FROM Cita ci
                LEFT JOIN Usuario u ON ci.id_usuario = u.id_usuario
                LEFT JOIN Vehiculo v ON ci.id_vehiculo = v.id_vehiculo
                LEFT JOIN Servicio s ON ci.id_servicio = s.id_servicio
                ORDER BY ci.fecha_cita DESC, ci.hora_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerUno($id) {
        $this->conectar();
        $sql = "SELECT * FROM Cita WHERE id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $this->conectar();
        $data = $this->sanitizar($data);
        
        $sql = "INSERT INTO Cita (id_usuario, id_vehiculo, id_servicio, fecha_cita, hora_inicio, estado_cita, notas_cliente) 
                VALUES (:id_u, :id_v, :id_s, :fecha, :hora, 'pendiente', :notas)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_u'   => $data['id_usuario'],
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
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_u'   => $data['id_usuario'],
            ':id_v'   => $data['id_vehiculo'],
            ':id_s'   => $data['id_servicio'],
            ':fecha'  => $data['fecha_cita'],
            ':hora'   => $data['hora_inicio'],
            ':estado' => $data['estado_cita'],
            ':diag'   => $data['diagnostico_tecnico'] ?? null,
            ':id'     => $id
        ]);
    }

    public function borrar($id) {
        $this->conectar();
        $sql = "DELETE FROM Cita WHERE id_cita = :id";
        return $this->db->prepare($sql)->execute([':id' => $id]);
    }

    // Para llenar el select de Clientes
    public function obtenerUsuarios() {
        $this->conectar();
        $sql = "SELECT id_usuario, email FROM Usuario ORDER BY email ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para llenar el select de Servicios (ESTA ES LA QUE TE MARCA EL ERROR)
    public function obtenerServicios() {
        $this->conectar();
        $sql = "SELECT id_servicio, nombre_servicio FROM Servicio WHERE estado = 'activo' ORDER BY nombre_servicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para llenar el select de Vehículos
    public function obtenerVehiculos() {
        $this->conectar();
        $sql = "SELECT v.id_vehiculo, v.marca, v.modelo, v.placas, u.email 
                FROM Vehiculo v
                LEFT JOIN Usuario u ON v.id_usuario = u.id_usuario
                ORDER BY u.email ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTecnicos() {
        $this->conectar();
        return $this->db->query("SELECT e.id_empleado, e.nombre FROM Empleado e INNER JOIN Usuario u ON e.id_usuario = u.id_usuario WHERE u.id_rol = 2")->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>