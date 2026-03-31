<?php
require_once(__DIR__."/../sistema.class.php");

class Vehiculo extends Sistema {

    function leer() {
        $this->validarAcceso('vehiculo_leer');
        $this->conectar();

        $sql = "SELECT v.*,
                    COALESCE(c.nombre, e.nombre, '') as nombre_dueno,
                    COALESCE(c.apellido_paterno, e.apellido_paterno, '') as apellido_dueno,
                    u.email as email_dueno
                FROM Vehiculo v
                LEFT JOIN Usuario  u ON v.id_usuario = u.id_usuario
                LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario";

        // Cliente solo ve sus propios vehículos
        if ($this->esCliente()) {
            $sql .= " WHERE v.id_usuario = :id_usuario";
        }

        $sql .= " ORDER BY v.id_vehiculo DESC";
        $stmt = $this->db->prepare($sql);

        if ($this->esCliente()) {
            $stmt->execute([':id_usuario' => $this->obtenerIdUsuario()]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function leerUno($id) {
        $this->validarAcceso('vehiculo_leer');
        $this->conectar();

        $sql = "SELECT v.*,
                    COALESCE(c.nombre, e.nombre, '') as nombre_dueno,
                    u.email as email_dueno
                FROM Vehiculo v
                LEFT JOIN Usuario  u ON v.id_usuario = u.id_usuario
                LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                WHERE v.id_vehiculo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cliente solo puede ver sus propios vehículos
        if ($this->esCliente() && $vehiculo['id_usuario'] != $this->obtenerIdUsuario()) {
            throw new Exception("No tienes permiso para ver este vehículo");
        }
        return $vehiculo;
    }

    function crear($data) {
        $this->validarAcceso('vehiculo_crear');
        $data = $this->sanitizar($data);

        if (empty($data['marca']) || empty($data['modelo']))
            throw new Exception("Marca y modelo son obligatorios");

        $anio_actual = date('Y');
        if ($data['anio'] < 1990 || $data['anio'] > ($anio_actual + 1))
            throw new Exception("Año inválido");

        if (!empty($data['numero_serie_vin']) && strlen($data['numero_serie_vin']) != 17)
            throw new Exception("El VIN debe tener 17 caracteres");

        // Cliente registra el vehículo a su propio usuario
        $id_usuario = $this->esCliente()
            ? $this->obtenerIdUsuario()
            : ($data['id_usuario'] ?? $this->obtenerIdUsuario());

        $this->conectar();
        $sql = "INSERT INTO Vehiculo (id_usuario, marca, modelo, anio, numero_serie_vin, placas, notas)
                VALUES (:id_usuario, :marca, :modelo, :anio, :vin, :placas, :notas)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':marca'      => strtoupper($data['marca']),
            ':modelo'     => $data['modelo'],
            ':anio'       => $data['anio'],
            ':vin'        => $data['numero_serie_vin'] ?? null,
            ':placas'     => strtoupper($data['placas'] ?? ''),
            ':notas'      => $data['notas'] ?? null,
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('vehiculo_editar');
        $vehiculo = $this->leerUno($id);

        if ($this->esCliente() && $vehiculo['id_usuario'] != $this->obtenerIdUsuario())
            throw new Exception("No tienes permiso para editar este vehículo");

        $data = $this->sanitizar($data);
        $this->conectar();

        $sql = "UPDATE Vehiculo SET
                    marca = :marca, modelo = :modelo, anio = :anio,
                    numero_serie_vin = :vin, placas = :placas, notas = :notas
                WHERE id_vehiculo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':marca'  => strtoupper($data['marca']),
            ':modelo' => $data['modelo'],
            ':anio'   => $data['anio'],
            ':vin'    => $data['numero_serie_vin'] ?? null,
            ':placas' => strtoupper($data['placas'] ?? ''),
            ':notas'  => $data['notas'] ?? null,
            ':id'     => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('vehiculo_eliminar');
        $vehiculo = $this->leerUno($id);

        if ($this->esCliente() && $vehiculo['id_usuario'] != $this->obtenerIdUsuario())
            throw new Exception("No tienes permiso para eliminar este vehículo");

        $this->conectar();
        $stmt = $this->db->prepare("DELETE FROM Vehiculo WHERE id_vehiculo = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    function obtenerUsuarios() {
        $this->conectar();
        $sql = "SELECT u.id_usuario, u.email,
                       COALESCE(c.nombre, e.nombre, '') as nombre
                FROM Usuario u
                LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                WHERE u.estado_cuenta = 'activa'
                ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
