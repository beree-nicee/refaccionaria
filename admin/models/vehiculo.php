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
        if ($this->esCliente()) $sql .= " WHERE v.id_usuario = :id";
        $sql .= " ORDER BY v.id_vehiculo DESC";
        $stmt = $this->db->prepare($sql);
        $this->esCliente()
            ? $stmt->execute([':id' => $this->obtenerIdUsuario()])
            : $stmt->execute();
        return $stmt->fetchAll();
    }

    function leerUno($id) {
        $this->validarAcceso('vehiculo_leer');
        $this->conectar();
        $stmt = $this->db->prepare(
            "SELECT v.*,
                COALESCE(c.nombre, e.nombre, '') as nombre_dueno,
                u.email as email_dueno
             FROM Vehiculo v
             LEFT JOIN Usuario  u ON v.id_usuario = u.id_usuario
             LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
             LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
             WHERE v.id_vehiculo = :id"
        );
        $stmt->execute([':id' => $id]);
        $v = $stmt->fetch();
        if ($this->esCliente() && $v && $v['id_usuario'] != $this->obtenerIdUsuario())
            throw new Exception("No tienes permiso para ver este vehículo");
        return $v;
    }

    function crear($data) {
        $this->validarAcceso('vehiculo_crear');
        $this->conectar();
        $data = $this->sanitizar($data);
        $this->_validarDatos($data);

        $id_usuario = $this->esCliente()
            ? $this->obtenerIdUsuario()
            : ($data['id_usuario'] ?? $this->obtenerIdUsuario());

        $stmt = $this->db->prepare(
            "INSERT INTO Vehiculo (id_usuario, marca, modelo, anio, numero_serie_vin, placas, notas)
             VALUES (:id_u, :marca, :modelo, :anio, :vin, :placas, :notas)"
        );
        $stmt->execute([
            ':id_u'   => $id_usuario,
            ':marca'  => strtoupper($data['marca']),
            ':modelo' => $data['modelo'],
            ':anio'   => $data['anio'],
            ':vin'    => !empty($data['numero_serie_vin']) ? strtoupper($data['numero_serie_vin']) : null,
            ':placas' => !empty($data['placas']) ? strtoupper($data['placas']) : null,
            ':notas'  => $data['notas'] ?? null,
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('vehiculo_editar');
        $this->conectar();
        $data   = $this->sanitizar($data);
        $actual = $this->leerUno($id);

        if ($this->esCliente() && $actual['id_usuario'] != $this->obtenerIdUsuario())
            throw new Exception("No tienes permiso para editar este vehículo");

        $this->_validarDatos($data, false);

        // VIN solo editable por admin/técnico
        $puedeVIN = $this->esAdmin() || $this->esTecnico();
        $vin = $puedeVIN
            ? (!empty($data['numero_serie_vin']) ? strtoupper($data['numero_serie_vin']) : null)
            : $actual['numero_serie_vin'];

        $stmt = $this->db->prepare(
            "UPDATE Vehiculo SET
                marca=:marca, modelo=:modelo, anio=:anio,
                numero_serie_vin=:vin, placas=:placas, notas=:notas
             WHERE id_vehiculo=:id"
        );
        $stmt->execute([
            ':marca'  => strtoupper($data['marca']),
            ':modelo' => $data['modelo'],
            ':anio'   => $data['anio'],
            ':vin'    => $vin,
            ':placas' => !empty($data['placas']) ? strtoupper($data['placas']) : null,
            ':notas'  => $data['notas'] ?? null,
            ':id'     => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('vehiculo_eliminar');
        $this->conectar();
        $stmt = $this->db->prepare("DELETE FROM Vehiculo WHERE id_vehiculo = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    function obtenerUsuarios() {
        $this->conectar();
        $stmt = $this->db->prepare(
            "SELECT u.id_usuario,
                    COALESCE(c.nombre, e.nombre, u.email) as nombre,
                    u.email
             FROM Usuario u
             LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
             LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
             WHERE u.estado_cuenta = 'activa'
             ORDER BY nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function _validarDatos($data, $validarRequeridos = true) {
        if ($validarRequeridos) {
            if (empty($data['marca']))  throw new Exception("La marca es obligatoria");
            if (empty($data['modelo'])) throw new Exception("El modelo es obligatorio");
            if (empty($data['anio']))   throw new Exception("El año es obligatorio");
        }
        if (!empty($data['anio']) && ($data['anio'] < 1900 || $data['anio'] > date('Y') + 1))
            throw new Exception("El año no es válido");
        if (!empty($data['numero_serie_vin']) && !$this->validarVIN($data['numero_serie_vin']))
            throw new Exception("El VIN debe tener exactamente 17 caracteres alfanuméricos válidos");
        if (!empty($data['placas']) && !$this->validarPlacas($data['placas']))
            throw new Exception("Las placas no tienen el formato correcto");
    }
}