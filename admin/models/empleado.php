<?php
require_once(__DIR__."/../sistema.class.php");

class Empleado extends Sistema {

    public function leer() {
        $this->validarAcceso('empleado_leer');
        $this->conectar();
        $stmt = $this->db->prepare(
            "SELECT e.*, u.email, u.estado_cuenta, r.rol
             FROM Empleado e
             INNER JOIN Usuario u ON e.id_usuario = u.id_usuario
             INNER JOIN Rol r    ON u.id_rol = r.id_rol
             ORDER BY e.id_empleado DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function leerUno($id) {
        $this->conectar();
        $stmt = $this->db->prepare(
            "SELECT e.*, u.email, u.id_rol, u.estado_cuenta
             FROM Empleado e
             INNER JOIN Usuario u ON e.id_usuario = u.id_usuario
             WHERE e.id_empleado = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear($data) {
        $this->validarAcceso('empleado_crear');
        $this->conectar();

        $data = $this->sanitizar($data);
        $this->_validarDatos($data, true);

        // Foto fuera de la transacción
        $fotografia = null;
        if (!empty($_FILES['fotografia']['name']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
            $base = !empty($data['rfc'])
                ? preg_replace('/[^A-Z0-9]/', '_', strtoupper($data['rfc']))
                : 'emp_' . time();
            $fotografia = $this->subirImagenConNombre($_FILES['fotografia'], 'empleados', $base);
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Usuario (email, contrasena_hash, id_rol, estado_cuenta)
                 VALUES (:email, :pass, :rol, 'activa')"
            );
            $stmt->execute([
                ':email' => $data['email'],
                ':pass'  => md5($data['contrasena']),
                ':rol'   => $data['id_rol'] ?? 2,
            ]);
            $id_usuario = $this->db->lastInsertId();

            // Insertar en usuario_rol
            $this->db->prepare("INSERT INTO usuario_rol (id_rol, id_usuario) VALUES (:rol, :usr)")
                ->execute([':rol' => $data['id_rol'] ?? 2, ':usr' => $id_usuario]);

            $this->db->prepare(
                "INSERT INTO Empleado
                    (id_usuario, nombre, apellido_paterno, apellido_materno,
                     rfc, curp, fecha_nacimiento, fotografia, telefono)
                 VALUES (:id_u,:nom,:ap1,:ap2,:rfc,:curp,:fnac,:foto,:tel)"
            )->execute([
                ':id_u' => $id_usuario,
                ':nom'  => $data['nombre'],
                ':ap1'  => $data['apellido_paterno']  ?? null,
                ':ap2'  => $data['apellido_materno']  ?? null,
                ':rfc'  => !empty($data['rfc'])  ? strtoupper($data['rfc'])  : null,
                ':curp' => !empty($data['curp']) ? strtoupper($data['curp']) : null,
                ':fnac' => $data['fecha_nacimiento'],
                ':foto' => $fotografia,
                ':tel'  => $data['telefono'] ?? null,
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
        $data   = $this->sanitizar($data);
        $actual = $this->leerUno($id);

        $esPropio     = ((int)$actual['id_usuario'] === (int)$this->obtenerIdUsuario());
        $puedeRFCCURP = $this->esAdmin() && !$esPropio;

        $this->_validarDatos($data, false, $puedeRFCCURP);

        // Foto fuera de la transacción
        $fotografia = $actual['fotografia'];
        if (!empty($_FILES['fotografia']['name']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
            $rfc  = $puedeRFCCURP ? ($data['rfc'] ?? $actual['rfc']) : $actual['rfc'];
            $base = !empty($rfc)
                ? preg_replace('/[^A-Z0-9]/', '_', strtoupper($rfc))
                : 'emp_' . $actual['id_usuario'];
            if ($fotografia) $this->eliminarImagen($fotografia, 'empleados');
            $fotografia = $this->subirImagenConNombre($_FILES['fotografia'], 'empleados', $base);
        }

        $rfc  = $puedeRFCCURP ? (!empty($data['rfc'])  ? strtoupper($data['rfc'])  : null) : $actual['rfc'];
        $curp = $puedeRFCCURP ? (!empty($data['curp']) ? strtoupper($data['curp']) : null) : $actual['curp'];

        $this->db->beginTransaction();
        try {
            // Actualizar Usuario
            $sqlU    = "UPDATE Usuario SET email = :email"
                     . (!empty($data['contrasena']) ? ", contrasena_hash = :pass" : "")
                     . " WHERE id_usuario = :id";
            $paramsU = [':email' => $data['email'], ':id' => $actual['id_usuario']];
            if (!empty($data['contrasena'])) $paramsU[':pass'] = md5($data['contrasena']);
            $this->db->prepare($sqlU)->execute($paramsU);

            // Actualizar Empleado
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
                 WHERE id_empleado = :id"
            )->execute([
                ':nom'  => $data['nombre'],
                ':ap1'  => $data['apellido_paterno'] ?? $actual['apellido_paterno'],
                ':ap2'  => $data['apellido_materno'] ?? $actual['apellido_materno'],
                ':rfc'  => $rfc,
                ':curp' => $curp,
                ':fnac' => $data['fecha_nacimiento'] ?? $actual['fecha_nacimiento'],
                ':foto' => $fotografia,
                ':tel'  => !empty($data['telefono']) ? $data['telefono'] : null,
                ':id'   => $id,
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function borrar($id) {
        $this->validarAcceso('empleado_eliminar');
        $this->conectar();
        $actual = $this->leerUno($id);
        if (!empty($actual['fotografia']))
            $this->eliminarImagen($actual['fotografia'], 'empleados');
        return $this->db->prepare("DELETE FROM Usuario WHERE id_usuario = :id")
            ->execute([':id_u' => $actual['id_usuario']]);
    }

    public function obtenerRoles() {
        $this->conectar();
        return $this->db->query("SELECT * FROM Rol ORDER BY rol")->fetchAll();
    }

    // =============================================
    // VALIDACIONES INTERNAS
    // =============================================
    private function _validarDatos($data, $esNuevo = false, $validarRFCCURP = true) {
        if (empty($data['nombre']))
            throw new Exception("El nombre es obligatorio");

        if (!$this->validarEmail($data['email'] ?? ''))
            throw new Exception("El correo electrónico no es válido");

        if ($esNuevo || !empty($data['contrasena'])) {
            if (!$this->validarContrasena($data['contrasena'] ?? ''))
                throw new Exception("La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial");
        }

        if (!empty($data['telefono']) && !$this->validarTelefono($data['telefono']))
            throw new Exception("El teléfono debe tener 10 dígitos");

        if ($validarRFCCURP) {
            if (!empty($data['rfc']) && !$this->validarRFC($data['rfc']))
                throw new Exception("El RFC no tiene el formato correcto (ej: GARC901231ABC)");
            if (!empty($data['curp']) && !$this->validarCURP($data['curp']))
                throw new Exception("El CURP no tiene el formato correcto");
        }
    }
}
