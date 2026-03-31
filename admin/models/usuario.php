<?php
require_once(__DIR__."/../sistema.class.php");

class Usuario extends Sistema {
    
    function leer() {
        //$this->validarAcceso('usuario.leer');
        $this->conectar();
        $sql = "SELECT u.id_usuario, u.email, u.id_rol, u.estado_cuenta, u.fecha_registro,
                COALESCE(e.nombre, c.nombre) as nombre, 
                COALESCE(e.telefono, c.telefono) as telefono,
                COALESCE(CONCAT(e.apellido_paterno, ' ', e.apellido_materno), 
                         CONCAT(c.apellido_paterno, ' ', c.apellido_materno)) as apellidos,
                r.rol as nombre_rol
                FROM Usuario u
                LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                LEFT JOIN Cliente c ON u.id_usuario = c.id_usuario
                INNER JOIN Rol r ON u.id_rol = r.id_rol
                WHERE u.estado_cuenta != 'eliminada' 
                ORDER BY u.fecha_registro DESC";
        //$sql = "SELECT * FROM Usuario WHERE estado_cuenta != 'eliminada' ORDER BY fecha_registro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    function obtenerRoles() {
        $this->conectar();
        $sql = "SELECT * FROM Rol ORDER BY rol";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function leerUno($id) {
        // Puede ver su propio perfil o ser admin
        //if ($_SESSION['id_usuario'] != $id && !$this->esAdmin()) {
          //  throw new Exception("No tienes permiso");
        //}
        
        $this->conectar();
        //$sql = "SELECT * FROM Usuario WHERE id_usuario = :id";
        $sql = "SELECT 
                u.id_usuario, 
                u.email,          -- Antes decía u.correo, cámbialo a u.email
                u.contrasena_hash, -- Verifica si es 'contrasena' o 'contrasena_hash' según tu DB
                u.id_rol,
                COALESCE(e.nombre, c.nombre) as nombre, 
                COALESCE(e.apellido_paterno, c.apellido_paterno) as apellido_paterno,
                COALESCE(e.apellido_materno, c.apellido_materno) as apellido_materno,
                COALESCE(e.telefono, c.telefono) as telefono
            FROM Usuario u
            LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
            LEFT JOIN Cliente c ON u.id_usuario = c.id_usuario
            WHERE u.id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function crear($data) {
        $this->conectar();
        $this->db->beginTransaction(); 
        
        try{
            $data = $this->sanitizar($data);
            // Validaciones
            if (!$this->validarEmail($data['email'])) {
                throw new Exception("Email inválido");
            }
            if ($this->emailExiste($data['email'])) {
                throw new Exception("El email ya está registrado");
            }
            if (strlen($data['contrasena']) < 2) {
                throw new Exception("La contraseña debe tener al menos 2 caracteres");
            }

            $hash = md5($data['contrasena']);
            $sql = "INSERT INTO Usuario (email, contrasena_hash, id_rol) 
                    VALUES (:email, :hash, :id_rol)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $data['email'],
                ':hash' => $hash,
                ':id_rol' => $data['id_rol'] ?? null
            ]);

            $id_usuario = $this->db->lastInsertId();

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    
        
      
        return $stmt->rowCount();
    }
    /*
    function actualizar($id, $data) {
        if ($_SESSION['id_usuario'] != $id && !$this->esAdmin()) {
            throw new Exception("No tienes permiso");
        }
        
        $data = $this->sanitizar($data);
        $this->conectar();
        
        $campos = [];
        $params = [':id' => $id];
        
        if (!empty($data['nombre'])) {
            $campos[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        
        if (!empty($data['apellidos'])) {
            $campos[] = "apellidos = :apellidos";
            $params[':apellidos'] = $data['apellidos'];
        }
        
        if (!empty($data['telefono'])) {
            $campos[] = "telefono = :telefono";
            $params[':telefono'] = $data['telefono'];
        }
        
        if (isset($data['direccion'])) {
            $campos[] = "direccion = :direccion";
            $params[':direccion'] = $data['direccion'];
        }
        
        if (isset($data['ciudad'])) {
            $campos[] = "ciudad = :ciudad";
            $params[':ciudad'] = $data['ciudad'];
        }
        
        if (isset($data['estado'])) {
            $campos[] = "estado = :estado";
            $params[':estado'] = $data['estado'];
        }
        
        if (isset($data['codigo_postal'])) {
            $campos[] = "codigo_postal = :codigo_postal";
            $params[':codigo_postal'] = $data['codigo_postal'];
        }
        
        if (!empty($data['contrasena'])) {
            $campos[] = "contrasena_hash = :hash";
            $params[':hash'] = md5($data['contrasena']);
        }
        
        // Solo admin puede cambiar rol
        if ($this->esAdmin() && isset($data['rol'])) {
            $campos[] = "rol = :rol";
            $params[':rol'] = $data['rol'];
        }
        
        if (empty($campos)) return 0;
        
        $sql = "UPDATE Usuario SET " . implode(', ', $campos) . " WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    */
    function actualizar($id, $data) {
        $this->conectar();
        $this->db->beginTransaction(); // Iniciamos transacción por seguridad

        try {
            $data = $this->sanitizar($data);

            // 1. Actualizar datos en la tabla 'usuario' (Email y Password)
            $camposU = [];
            $paramsU = [':id' => $id];

            if (!empty($data['email'])) {
                $camposU[] = "correo = :correo"; // Según tu imagen la columna es 'correo'
                $paramsU[':correo'] = $data['email'];
            }
            if (!empty($data['contrasena'])) {
                $camposU[] = "contrasena = :hash";
                $paramsU[':hash'] = md5($data['contrasena']);
            }
            if ($this->esAdmin() && isset($data['id_rol'])) {
                $camposU[] = "id_rol = :id_rol";
                $paramsU[':id_rol'] = $data['id_rol'];
            }

            if (!empty($camposU)) {
                $sqlU = "UPDATE usuario SET " . implode(', ', $camposU) . " WHERE id_usuario = :id";
                $this->db->prepare($sqlU)->execute($paramsU);
            }

            // 2. Separar apellidos (el formulario manda uno, la DB tiene dos)
            $apellidos = explode(' ', $data['apellidos'] ?? '', 2);
            $apPaterno = $apellidos[0] ?? '';
            $apMaterno = $apellidos[1] ?? '';

            // 3. Actualizar datos en Cliente o Empleado
            // Intentamos actualizar en Cliente
            $sqlC = "UPDATE cliente SET 
                        nombre = :nom, 
                        apellido_paterno = :ap1, 
                        apellido_materno = :ap2, 
                        telefono = :tel,
                        direccion = :dir,
                        ciudad = :ciu,
                        estado = :est,
                        codigo_postal = :cp
                    WHERE id_usuario = :id";
            
            $stmtC = $this->db->prepare($sqlC);
            $stmtC->execute([
                ':nom' => $data['nombre'],
                ':ap1' => $apPaterno,
                ':ap2' => $apMaterno,
                ':tel' => $data['telefono'] ?? null,
                ':dir' => $data['direccion'] ?? null,
                ':ciu' => $data['ciudad'] ?? null,
                ':est' => $data['estado'] ?? null,
                ':cp'  => $data['codigo_postal'] ?? null,
                ':id'  => $id
            ]);

            // Si no se actualizó nada en cliente, intentamos en empleado
            if ($stmtC->rowCount() == 0) {
                $sqlE = "UPDATE empleado SET nombre = :nom, apellido_paterno = :ap1, apellido_materno = :ap2, telefono = :tel WHERE id_usuario = :id";
                $this->db->prepare($sqlE)->execute([
                    ':nom' => $data['nombre'],
                    ':ap1' => $apPaterno,
                    ':ap2' => $apMaterno,
                    ':tel' => $data['telefono'] ?? null,
                    ':id'  => $id
                ]);
            }

            $this->db->commit(); // Si todo salió bien, guardamos cambios
            return true;

        } catch (Exception $e) {
            $this->db->rollBack(); // Si algo falló, deshacemos todo
            throw $e;
        }
    }
    function borrar($id) {
        $this->validarAcceso('usuario.eliminar');
        $this->conectar();
        
        $sql = "UPDATE Usuario SET estado_cuenta = 'eliminada' WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->rowCount();
    }
    
    function login($email, $contrasena) {
        $this->conectar();
        $contrasena_md5 = md5($contrasena);
        
        $sql = "SELECT * FROM Usuario WHERE email = :email 
            AND contrasena_hash = :pass 
            AND estado_cuenta = 'activa'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':pass' => $contrasena_md5]);
        
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellidos'] = $usuario['apellidos'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            return true;
        }
        
        return false;
    }
    
    function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    private function emailExiste($email, $excluir_id = null) {
        $this->conectar();
        $sql = "SELECT COUNT(*) as existe FROM Usuario WHERE email = :email";
        
        if ($excluir_id) {
            $sql .= " AND id_usuario != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [':email' => $email];
        
        if ($excluir_id) {
            $params[':id'] = $excluir_id;
        }
        
        $stmt->execute($params);
        return $stmt->fetch()['existe'] > 0;
    }
}
?>