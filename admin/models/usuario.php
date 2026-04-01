<?php
require_once(__DIR__."/../sistema.class.php");

class Usuario extends Sistema {

    function leer() {
        $this->validarAcceso('usuario_leer');
        $this->conectar();
        $sql = "SELECT u.id_usuario, u.email, u.id_rol, u.estado_cuenta, u.fecha_registro,
                    COALESCE(e.nombre, c.nombre, '') as nombre,
                    COALESCE(e.telefono, c.telefono, '') as telefono,
                    COALESCE(
                        CONCAT(e.apellido_paterno, ' ', e.apellido_materno),
                        CONCAT(c.apellido_paterno, ' ', c.apellido_materno), ''
                    ) as apellidos,
                    COALESCE(e.fotografia, c.fotografia, '') as fotografia,
                    CASE WHEN e.id_empleado IS NOT NULL THEN 'empleados' ELSE 'clientes' END as carpeta_foto,
                    e.rfc,
                    e.id_empleado,
                    c.id_cliente,
                    r.rol as nombre_rol
                FROM Usuario u
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                INNER JOIN Rol r ON u.id_rol = r.id_rol
                WHERE u.estado_cuenta != 'eliminada'
                ORDER BY u.fecha_registro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function obtenerRoles() {
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Rol ORDER BY rol");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function leerUno($id) {
        $this->conectar();
        $sql = "SELECT u.id_usuario, u.email, u.id_rol, u.estado_cuenta,
                    COALESCE(e.nombre, c.nombre, '') as nombre,
                    COALESCE(e.apellido_paterno, c.apellido_paterno, '') as apellido_paterno,
                    COALESCE(e.apellido_materno, c.apellido_materno, '') as apellido_materno,
                    COALESCE(e.telefono, c.telefono, '') as telefono,
                    COALESCE(e.fotografia, c.fotografia, '') as fotografia,
                    CASE WHEN e.id_empleado IS NOT NULL THEN 'empleados' ELSE 'clientes' END as carpeta_foto,
                    e.rfc,
                    e.id_empleado,
                    c.id_cliente,
                    r.rol as nombre_rol
                FROM Usuario u
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                INNER JOIN Rol r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function crear($data) {
        $this->validarAcceso('usuario_crear');
        $this->conectar();
        $this->db->beginTransaction();
        try {
            $data = $this->sanitizar($data);
            if (!$this->validarEmail($data['email']))
                throw new Exception("Email inválido");
            if ($this->emailExiste($data['email']))
                throw new Exception("El email ya está registrado");
            if (strlen($data['contrasena'] ?? '') < 6)
                throw new Exception("La contraseña debe tener al menos 6 caracteres");

            $stmt = $this->db->prepare(
                "INSERT INTO Usuario (email, contrasena_hash, id_rol)
                 VALUES (:email, :hash, :id_rol)"
            );
            $stmt->execute([
                ':email'  => $data['email'],
                ':hash'   => md5($data['contrasena']),
                ':id_rol' => $data['id_rol'] ?? null,
            ]);
            $this->db->commit();
            return $stmt->rowCount();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    function actualizar($id, $data) {
        $this->validarAcceso('usuario_editar');
        $this->conectar();
        $data = $this->sanitizar($data);

        // Obtener datos actuales ANTES de la transacción
        $actual = $this->leerUno($id);

        $esEmpleado = !empty($actual['id_empleado']);
        $esCliente  = !empty($actual['id_cliente']);

        // Manejar fotografía fuera de la transacción
        $fotografia = !empty($actual['fotografia']) ? $actual['fotografia'] : null;
        if (!empty($_FILES['fotografia']['name']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
            if ($fotografia) $this->eliminarImagen($fotografia, $actual['carpeta_foto']);

            if ($esEmpleado && !empty($actual['rfc'])) {
                $nombreBase = preg_replace('/[^a-zA-Z0-9]/', '_', strtoupper($actual['rfc']));
            } elseif ($esCliente) {
                $nombreBase = 'cliente_' . $actual['id_cliente'];
            } else {
                $nombreBase = 'usuario_' . $id;
            }

            $carpeta    = $esEmpleado ? 'empleados' : 'clientes';
            $fotografia = $this->subirImagenConNombre($_FILES['fotografia'], $carpeta, $nombreBase);
        }

        $this->db->beginTransaction();
        try {
            // 1. Actualizar tabla Usuario
            $camposU = [];
            $paramsU = [':id' => $id];

            if (!empty($data['email']) && $data['email'] !== $actual['email']) {
                if ($this->emailExiste($data['email'], $id))
                    throw new Exception("El email ya está registrado");
                $camposU[] = "email = :email";
                $paramsU[':email'] = $data['email'];
            }
            if (!empty($data['contrasena'])) {
                $camposU[] = "contrasena_hash = :hash";
                $paramsU[':hash'] = md5($data['contrasena']);
            }
            if ($this->esAdmin() && !empty($data['id_rol'])) {
                $camposU[] = "id_rol = :id_rol";
                $paramsU[':id_rol'] = $data['id_rol'];
            }

            if (!empty($camposU)) {
                $this->db->prepare(
                    "UPDATE Usuario SET " . implode(', ', $camposU) . " WHERE id_usuario = :id"
                )->execute($paramsU);
            }

            // 2. Conservar datos actuales si no se enviaron
            $nombre = !empty($data['nombre'])           ? $data['nombre']           : $actual['nombre'];
            $apPat  = !empty($data['apellido_paterno']) ? $data['apellido_paterno'] : $actual['apellido_paterno'];
            $apMat  = !empty($data['apellido_materno']) ? $data['apellido_materno'] : $actual['apellido_materno'];
            $tel    = isset($data['telefono'])          ? $data['telefono']         : $actual['telefono'];

            // 3. Actualizar Empleado si aplica
            if ($esEmpleado) {
                $this->db->prepare(
                    "UPDATE Empleado SET
                        nombre           = :nom,
                        apellido_paterno = :ap1,
                        apellido_materno = :ap2,
                        telefono         = :tel,
                        fotografia       = :foto
                     WHERE id_empleado = :id_e"
                )->execute([
                    ':nom'  => $nombre,
                    ':ap1'  => $apPat,
                    ':ap2'  => $apMat,
                    ':tel'  => $tel,
                    ':foto' => $fotografia,
                    ':id_e' => $actual['id_empleado'],
                ]);
            }

            // 4. Actualizar Cliente si aplica
            if ($esCliente) {
                $this->db->prepare(
                    "UPDATE Cliente SET
                        nombre           = :nom,
                        apellido_paterno = :ap1,
                        apellido_materno = :ap2,
                        telefono         = :tel,
                        fotografia       = :foto
                     WHERE id_cliente = :id_c"
                )->execute([
                    ':nom'  => $nombre,
                    ':ap1'  => $apPat,
                    ':ap2'  => $apMat,
                    ':tel'  => $tel,
                    ':foto' => $fotografia,
                    ':id_c' => $actual['id_cliente'],
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    function borrar($id) {
        $this->validarAcceso('usuario_eliminar');
        $this->conectar();
        $stmt = $this->db->prepare(
            "UPDATE Usuario SET estado_cuenta = 'eliminada' WHERE id_usuario = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    private function emailExiste($email, $excluir_id = null) {
        $sql = "SELECT COUNT(*) FROM Usuario WHERE email = :email";
        if ($excluir_id) $sql .= " AND id_usuario != :id";
        $stmt = $this->db->prepare($sql);
        $params = [':email' => $email];
        if ($excluir_id) $params[':id'] = $excluir_id;
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}