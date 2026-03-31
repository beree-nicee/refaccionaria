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
            
            // 1. Crear el Usuario primero
            $sqlU = "INSERT INTO Usuario (email, contrasena_hash, id_rol, estado_cuenta) 
                     VALUES (:email, :pass, :rol, 'activa')";
            $stmtU = $this->db->prepare($sqlU);
            $stmtU->execute([
                ':email' => $data['email'],
                ':pass'  => md5($data['contrasena']),
                ':rol'   => $data['id_rol'] ?? 2 // 2 suele ser el ID para 'tecnico/empleado'
            ]);
            $id_usuario = $this->db->lastInsertId();

            // 2. Manejar la imagen si existe
            $fotografia = null;
            if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
                $fotografia = $this->subirImagen($_FILES['fotografia'], 'empleados');
            }

            // 3. Crear el Empleado ligado al Usuario
            $sqlE = "INSERT INTO Empleado (id_usuario, nombre, apellido_paterno, apellido_materno, rfc, curp, fecha_nacimiento, fotografia, telefono) 
                     VALUES (:id_u, :nom, :ap1, :ap2, :rfc, :curp, :fnac, :foto, :tel)";
            $stmtE = $this->db->prepare($sqlE);
            $stmtE->execute([
                ':id_u' => $id_usuario,
                ':nom'  => $data['nombre'],
                ':ap1'  => $data['apellido_paterno'],
                ':ap2'  => $data['apellido_materno'],
                ':rfc'  => $data['rfc'] ?? null,
                ':curp' => $data['curp'] ?? null,
                ':fnac' => $data['fecha_nacimiento'],
                ':foto' => $fotografia,
                ':tel'  => $data['telefono'] ?? null
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizar($id, $data) {
        $this->conectar();
        $this->db->beginTransaction();
        try {
            $data = $this->sanitizar($data);
            $actual = $this->leerUno($id);
            $id_usuario = $actual['id_usuario'];

            // 1. Actualizar Usuario (Email y Password si viene)
            $sqlU = "UPDATE Usuario SET email = :email " . 
                    (!empty($data['contrasena']) ? ", contrasena_hash = :pass " : "") . 
                    "WHERE id_usuario = :id_u";
            $paramsU = [':email' => $data['email'], ':id_u' => $id_usuario];
            if (!empty($data['contrasena'])) $paramsU[':pass'] = md5($data['contrasena']);
            $this->db->prepare($sqlU)->execute($paramsU);

            // 2. Manejar imagen nueva
            $fotografia = $actual['fotografia'];
            if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
                if ($fotografia) $this->eliminarImagen($fotografia, 'empleados');
                $fotografia = $this->subirImagen($_FILES['fotografia'], 'empleados');
            }

            // 3. RFC y CURP: solo se actualizan si el usuario que edita
            //    es admin Y está editando a OTRA persona (no su propio perfil)
            $esPropio     = ((int)$actual['id_usuario'] === (int)$this->obtenerIdUsuario());
            $puedeRFCCURP = $this->esAdmin() && !$esPropio;

            $rfc  = $puedeRFCCURP ? ($data['rfc']  ?? null) : $actual['rfc'];
            $curp = $puedeRFCCURP ? ($data['curp'] ?? null) : $actual['curp'];

            // 4. Actualizar Empleado
            $sqlE = "UPDATE Empleado SET 
                        nombre           = :nom,
                        apellido_paterno = :ap1,
                        apellido_materno = :ap2,
                        rfc              = :rfc,
                        curp             = :curp,
                        fecha_nacimiento = :fnac,
                        fotografia       = :foto,
                        telefono         = :tel
                    WHERE id_empleado = :id_e";
            $this->db->prepare($sqlE)->execute([
                ':nom'  => $data['nombre'],
                ':ap1'  => $data['apellido_paterno'],
                ':ap2'  => $data['apellido_materno'],
                ':rfc'  => $rfc,
                ':curp' => $curp,
                ':fnac' => $data['fecha_nacimiento'],
                ':foto' => $fotografia,
                ':tel'  => $data['telefono'],
                ':id_e' => $id
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function borrar($id) {
        $this->conectar();
        $actual = $this->leerUno($id);
        // Al borrar el empleado, la base de datos debería borrar el usuario por CASCADE, 
        // pero aquí borramos la imagen manualmente.
        if($actual['fotografia']) $this->eliminarImagen($actual['fotografia'], 'empleados');
        $sql = "DELETE FROM Usuario WHERE id_usuario = :id_u";
        return $this->db->prepare($sql)->execute([':id_u' => $actual['id_usuario']]);
    }

    public function obtenerRoles() {
        $this->conectar();
        return $this->db->query("SELECT * FROM Rol")->fetchAll(PDO::FETCH_ASSOC);
    }
}