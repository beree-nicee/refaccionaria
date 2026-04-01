<?php
require_once(__DIR__."/../sistema.class.php");

class Empleado extends Sistema {

    public function leer() {
        $this->conectar();
        $sql = "SELECT e.*, u.email, r.rol
                FROM Empleado e
                INNER JOIN Usuario u ON e.id_usuario = u.id_usuario
                INNER JOIN Rol r ON u.id_rol = r.id_rol
                ORDER BY e.id_empleado DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leerUno($id) {
        $this->conectar();
        $sql = "SELECT e.*, u.email, u.id_rol
                FROM Empleado e
                INNER JOIN Usuario u ON e.id_usuario = u.id_usuario
                WHERE e.id_empleado = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $this->conectar();
        $this->db->beginTransaction();
        try {
            $data = $this->sanitizar($data);
            // 1. Crear Usuario
            $stmtU = $this->db->prepare(
                "INSERT INTO Usuario (email, contrasena_hash, id_rol, estado_cuenta)
                 VALUES (:email, :pass, :rol, 'activa')"
            );
            $stmtU->execute([
                ':email' => $data['email'],
                ':pass'  => md5($data['contrasena']),
                ':rol'   => $data['id_rol'] ?? 2,
            ]);
            $id_usuario = $this->db->lastInsertId();

            // 2. Subir foto usando RFC como nombre
            $fotografia = null;
            if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
                $nombreBase = !empty($data['rfc'])
                    ? preg_replace('/[^a-zA-Z0-9]/', '_', strtoupper($data['rfc']))
                    : 'empleado_' . $id_usuario;
                $fotografia = $this->subirImagenConNombre($_FILES['fotografia'], 'empleados', $nombreBase);
            }

            // 3. Crear Empleado
            $this->db->prepare(
                "INSERT INTO Empleado
                    (id_usuario, nombre, apellido_paterno, apellido_materno,
                     rfc, curp, fecha_nacimiento, fotografia, telefono)
                 VALUES
                    (:id_u, :nom, :ap1, :ap2, :rfc, :curp, :fnac, :foto, :tel)"
            )->execute([
                ':id_u' => $id_usuario,
                ':nom'  => $data['nombre'],
                ':ap1'  => $data['apellido_paterno'],
                ':ap2'  => $data['apellido_materno'],
                ':rfc'  => !empty($data['rfc'])  ? $data['rfc']  : null,
                ':curp' => !empty($data['curp']) ? $data['curp'] : null,
                ':fnac' => $data['fecha_nacimiento'],
                ':foto' => $fotografia,
                ':tel'  => !empty($data['telefono']) ? $data['telefono'] : null,
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizar($id, $data) {
        $this->conectar();
        $data = $this->sanitizar($data);

        // Obtener datos actuales ANTES de la transacción
        $actual     = $this->leerUno($id);
        $id_usuario = $actual['id_usuario'];

        // Manejar RFC/CURP: solo admin editando a otro puede cambiarlos
        $esPropio     = ((int)$actual['id_usuario'] === (int)$this->obtenerIdUsuario());
        $puedeRFCCURP = $this->esAdmin() && !$esPropio;
        $rfc  = $puedeRFCCURP ? (!empty($data['rfc'])  ? $data['rfc']  : null) : $actual['rfc'];
        $curp = $puedeRFCCURP ? (!empty($data['curp']) ? $data['curp'] : null) : $actual['curp'];

        // Subir foto fuera de la transacción
        $fotografia = $actual['fotografia'];
        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
            if ($fotografia) $this->eliminarImagen($fotografia, 'empleados');
            $nombreBase = !empty($rfc)
                ? preg_replace('/[^a-zA-Z0-9]/', '_', strtoupper($rfc))
                : 'empleado_' . $id_usuario;
            $fotografia = $this->subirImagenConNombre($_FILES['fotografia'], 'empleados', $nombreBase);
        }

        $this->db->beginTransaction();
        try {
            // 1. Actualizar Usuario
            $sqlU    = "UPDATE Usuario SET email = :email"
                     . (!empty($data['contrasena']) ? ", contrasena_hash = :pass" : "")
                     . " WHERE id_usuario = :id_u";
            $paramsU = [':email' => $data['email'], ':id_u' => $id_usuario];
            if (!empty($data['contrasena'])) $paramsU[':pass'] = md5($data['contrasena']);
            $this->db->prepare($sqlU)->execute($paramsU);

            // 2. Actualizar Empleado
            $this->db->prepare(
                "UPDATE Empleado SET
                    nombre           = :nom,
                    apellido_paterno = :ap1,
                    apellido_materno = :ap2,
                    rfc              = :rfc,
                    curp             = :curp,
                    fecha_nacimiento = :fnac,
                    fotografia       = :foto,
                    telefono         = :tel
                 WHERE id_empleado = :id_e"
            )->execute([
                ':nom'  => $data['nombre'],
                ':ap1'  => $data['apellido_paterno'],
                ':ap2'  => $data['apellido_materno'],
                ':rfc'  => $rfc,
                ':curp' => $curp,
                ':fnac' => $data['fecha_nacimiento'],
                ':foto' => $fotografia,
                ':tel'  => !empty($data['telefono']) ? $data['telefono'] : null,
                ':id_e' => $id,
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function borrar($id) {
        $this->conectar();
        $actual = $this->leerUno($id);
        if (!empty($actual['fotografia']))
            $this->eliminarImagen($actual['fotografia'], 'empleados');
        return $this->db->prepare("DELETE FROM Usuario WHERE id_usuario = :id_u")
            ->execute([':id_u' => $actual['id_usuario']]);
    }

    public function obtenerRoles() {
        $this->conectar();
        return $this->db->query("SELECT * FROM Rol")->fetchAll(PDO::FETCH_ASSOC);
    }
}
