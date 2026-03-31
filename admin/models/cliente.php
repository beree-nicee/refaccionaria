<?php
require_once(__DIR__."/../sistema.class.php");

class Cliente extends Sistema {

    function leer() {
        $this->validarAcceso('cliente_leer');
        $this->conectar();
        $sql = "SELECT c.*, u.email, r.rol
                FROM Cliente c
                INNER JOIN Usuario u ON c.id_usuario = u.id_usuario
                INNER JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
                INNER JOIN Rol r ON ur.id_rol = r.id_rol
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

        // Validaciones previas a la transacción
        if (empty($data['nombre']))           throw new Exception("El nombre es obligatorio");
        if (empty($data['apellido_paterno'])) throw new Exception("El apellido paterno es obligatorio");
        if (empty($data['apellido_materno'])) throw new Exception("El apellido materno es obligatorio");
        if (empty($data['fecha_nacimiento'])) throw new Exception("La fecha de nacimiento es obligatoria");
        if (empty($data['email']))            throw new Exception("El correo es obligatorio");
        if (empty($data['contrasena']))       throw new Exception("La contraseña es obligatoria");
        if ($data['contrasena'] !== ($data['confirmar_contrasena'] ?? '')) 
            throw new Exception("Las contraseñas no coinciden");

        // Verificar email único
        $stmt = $this->db->prepare("SELECT id_usuario FROM Usuario WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) throw new Exception("Este correo ya está registrado");

        // AHORA SÍ, INICIAMOS TRANSACCIÓN
        $this->db->beginTransaction();
        try {
            $id_rol = $this->obtenerIdRolCliente();
            $contrasena_hash = md5($data['contrasena']);

            // 1. Crear Usuario
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

            // 2. Insertar en tabla usuario_rol (CRÍTICO PARA EL LOGIN)
            // Tu esquema define PK como (id_rol, id_usuario)
            $stmtUR = $this->db->prepare("INSERT INTO usuario_rol (id_rol, id_usuario) VALUES (:id_rol, :id_usuario)");
            $stmtUR->execute([
                ':id_rol'     => $id_rol,
                ':id_usuario' => $id_usuario
            ]);

            // 3. Crear Cliente
            $stmt = $this->db->prepare(
                "INSERT INTO Cliente
                    (id_usuario, nombre, apellido_paterno, apellido_materno,
                     fecha_nacimiento, telefono, direccion, ciudad)
                 VALUES
                    (:id_usuario, :nombre, :ap, :am, :fn, :tel, :dir, :ciu)"
            );
            $stmt->execute([
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
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    
    // ... (mantén tus funciones crear, actualizar y borrar)
}