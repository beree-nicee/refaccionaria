<?php
require_once(__DIR__."/../sistema.class.php");

class Cliente extends Sistema {

    function leer() {
        $this->validarAcceso('cliente_leer');
        $this->conectar();
        $sql = "SELECT c.*, u.email, r.rol
                FROM Cliente c
                INNER JOIN Usuario u ON c.id_usuario = u.id_usuario
                LEFT JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
                LEFT JOIN Rol r ON ur.id_rol = r.id_rol
                ORDER BY c.id_cliente DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function leerUno($id_cliente) {
        $this->conectar();
        $sql = "SELECT c.*, u.email, u.id_rol
                FROM Cliente c
                INNER JOIN Usuario u ON c.id_usuario = u.id_usuario
                WHERE c.id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_cliente]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenerIdRolCliente() {
        $stmt = $this->db->prepare("SELECT id_rol FROM Rol WHERE rol = 'Cliente' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new Exception("El rol 'Cliente' no existe en la base de datos");
        return $row['id_rol'];
    }

    function registrar($data) {
        $this->conectar();
        $data = $this->sanitizar($data);

        if (empty($data['nombre']))           throw new Exception("El nombre es obligatorio");
        if (empty($data['apellido_paterno'])) throw new Exception("El apellido paterno es obligatorio");
        if (empty($data['apellido_materno'])) throw new Exception("El apellido materno es obligatorio");
        if (empty($data['fecha_nacimiento'])) throw new Exception("La fecha de nacimiento es obligatoria");
        if (empty($data['email']))            throw new Exception("El correo es obligatorio");
        if (empty($data['contrasena']))       throw new Exception("La contraseña es obligatoria");
        if ($data['contrasena'] !== ($data['confirmar_contrasena'] ?? ''))
            throw new Exception("Las contraseñas no coinciden");

        $stmt = $this->db->prepare("SELECT id_usuario FROM Usuario WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) throw new Exception("Este correo ya está registrado");

        $this->db->beginTransaction();
        try {
            $id_rol          = $this->obtenerIdRolCliente();
            $contrasena_hash = md5($data['contrasena']);

            $stmt = $this->db->prepare(
                "INSERT INTO Usuario (email, contrasena_hash, id_rol, estado_cuenta)
                 VALUES (:email, :hash, :id_rol, 'activa')"
            );
            $stmt->execute([
                ':email'  => $data['email'],
                ':hash'   => $contrasena_hash,
                ':id_rol' => $id_rol,
            ]);
            $id_usuario = $this->db->lastInsertId();

            $this->db->prepare("INSERT INTO usuario_rol (id_rol, id_usuario) VALUES (:id_rol, :id_usuario)")
                ->execute([':id_rol' => $id_rol, ':id_usuario' => $id_usuario]);

            $this->db->prepare(
                "INSERT INTO Cliente
                    (id_usuario, nombre, apellido_paterno, apellido_materno,
                     fecha_nacimiento, telefono, direccion, ciudad)
                 VALUES (:id_usuario, :nombre, :ap, :am, :fn, :tel, :dir, :ciu)"
            )->execute([
                ':id_usuario' => $id_usuario,
                ':nombre'     => $data['nombre'],
                ':ap'         => $data['apellido_paterno'],
                ':am'         => $data['apellido_materno'],
                ':fn'         => $data['fecha_nacimiento'],
                ':tel'        => $data['telefono']  ?? null,
                ':dir'        => $data['direccion'] ?? null,
                ':ciu'        => $data['ciudad']    ?? null,
            ]);

            $this->db->commit();

            return [
                'id_usuario' => $id_usuario,
                'nombre'     => $data['nombre'] . ' ' . $data['apellido_paterno'],
                'email'      => $data['email'],
                'contrasena' => $data['contrasena'],
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    function crear($data) {
        $this->validarAcceso('cliente_crear');
        $data['confirmar_contrasena'] = $data['contrasena'];
        return $this->registrar($data);
    }

    function actualizar($id_cliente, $data) {
        // Cliente siempre puede editar su propio perfil
        // Admin/técnico necesitan permiso
        if (!$this->esCliente()) {
            $this->validarAcceso('cliente_editar');
        }

        $this->conectar();

        // Obtener datos ANTES de la transacción
        $actual = $this->leerUno($id_cliente);
        if (!$actual) throw new Exception("Cliente no encontrado");

        // Cliente solo puede editar su propio perfil
        if ($this->esCliente() && (int)$actual['id_usuario'] !== (int)$this->obtenerIdUsuario()) {
            throw new Exception("No tienes permiso para editar este perfil");
        }

        $id_usuario = $actual['id_usuario'];
        $this->db->beginTransaction();

        try {
            // Actualizar email si cambió
            if ($actual['email'] !== $data['email']) {
                $chk = $this->db->prepare(
                    "SELECT id_usuario FROM Usuario WHERE email = :email AND id_usuario != :id"
                );
                $chk->execute([':email' => $data['email'], ':id' => $id_usuario]);
                if ($chk->fetch()) throw new Exception("El email ya está registrado");

                $this->db->prepare("UPDATE Usuario SET email = :email WHERE id_usuario = :id")
                    ->execute([':email' => $data['email'], ':id' => $id_usuario]);
            }

            // Actualizar contraseña si se envió
            if (!empty($data['contrasena'])) {
                $this->db->prepare("UPDATE Usuario SET contrasena_hash = :h WHERE id_usuario = :id")
                    ->execute([':h' => md5($data['contrasena']), ':id' => $id_usuario]);
            }

            // Actualizar datos del cliente
            $this->db->prepare(
                "UPDATE Cliente SET
                    nombre           = :nom,
                    apellido_paterno = :ap,
                    apellido_materno = :am,
                    fecha_nacimiento = :fn,
                    telefono         = :tel,
                    direccion        = :dir,
                    ciudad           = :ciu
                 WHERE id_cliente = :id"
            )->execute([
                ':nom' => $data['nombre'],
                ':ap'  => $data['apellido_paterno'],
                ':am'  => $data['apellido_materno'],
                ':fn'  => $data['fecha_nacimiento'],
                ':tel' => $data['telefono']  ?? null,
                ':dir' => $data['direccion'] ?? null,
                ':ciu' => $data['ciudad']    ?? null,
                ':id'  => $id_cliente,
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    function borrar($id_cliente) {
        $this->validarAcceso('cliente_eliminar');
        $this->conectar();
        $stmt = $this->db->prepare("DELETE FROM Cliente WHERE id_cliente = :id");
        $stmt->execute([':id' => $id_cliente]);
        return $stmt->rowCount();
    }

    function obtenerRoles() {
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Rol ORDER BY rol");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}