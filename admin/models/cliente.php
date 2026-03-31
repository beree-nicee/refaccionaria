<?php
require_once(__DIR__."/../sistema.class.php");

class Cliente extends Sistema {
    
    function leer() {
        $this->conectar();
        
        $sql = "SELECT c.*, u.email, r.rol
                FROM Cliente c
                INNER JOIN Usuario u ON c.id_usuario = u.id_usuario
                INNER JOIN Rol r ON u.id_rol = r.id_rol
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
        $stmt->bindParam(":id", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    function crear($data) {
        $this->conectar();
        $this->db->beginTransaction();
        
        try {
            // Validar que el email no exista
            $sql = "SELECT email FROM Usuario WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $this->db->rollBack();
                throw new Exception("El email ya está registrado");
            }
            
            // Validar fecha de nacimiento
            if (empty($data['fecha_nacimiento'])) {
                throw new Exception("La fecha de nacimiento es obligatoria");
            }
            
            // 1. Crear Usuario
            $sql = "INSERT INTO Usuario (email, contrasena_hash, id_rol, estado_cuenta) 
                    VALUES (:email, :contrasena, :id_rol, 'activa')";
            
            $stmt = $this->db->prepare($sql);
            $contrasena_hash = md5($data['contrasena']); // Usar md5 como en tu config
            $id_rol = 3; // Rol cliente por defecto (ajustar según tu tabla Rol)
            
            $stmt->execute([
                ':email' => $data['email'],
                ':contrasena' => $contrasena_hash,
                ':id_rol' => $id_rol
            ]);
            
            $id_usuario = $this->db->lastInsertId();
            
            // 2. Crear Cliente
            $sql = "INSERT INTO Cliente (id_usuario, nombre, apellido_materno, apellido_paterno, 
                    fecha_nacimiento, telefono, direccion, ciudad) 
                    VALUES (:id_usuario, :nombre, :apellido_materno, :apellido_paterno, 
                    :fecha_nacimiento, :telefono, :direccion, :ciudad)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':nombre' => $data['nombre'],
                ':apellido_materno' => $data['apellido_materno'],
                ':apellido_paterno' => $data['apellido_paterno'] ?? null,
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':telefono' => $data['telefono'] ?? null,
                ':direccion' => $data['direccion'] ?? null,
                ':ciudad' => $data['ciudad'] ?? null
            ]);
            
            $this->db->commit();
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    function actualizar($id_cliente, $data) {
        $this->conectar();
        $this->db->beginTransaction();
        
        try {
            // Obtener datos actuales
            $actual = $this->leerUno($id_cliente);
            
            if (!$actual) {
                throw new Exception("Cliente no encontrado");
            }
            
            $id_usuario = $actual['id_usuario'];
            
            // Actualizar email si cambió
            if ($actual['email'] != $data['email']) {
                // Verificar que el nuevo email no exista
                $sql = "SELECT email FROM Usuario WHERE email = :email AND id_usuario != :id_usuario";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':email' => $data['email'],
                    ':id_usuario' => $id_usuario
                ]);
                
                if ($stmt->rowCount() > 0) {
                    $this->db->rollBack();
                    throw new Exception("El email ya está registrado");
                }
                
                // Actualizar email
                $sql = "UPDATE Usuario SET email = :email WHERE id_usuario = :id_usuario";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':email' => $data['email'],
                    ':id_usuario' => $id_usuario
                ]);
            }
            
            // Actualizar contraseña si se proporcionó
            if (!empty($data['contrasena'])) {
                $sql = "UPDATE Usuario SET contrasena_hash = :contrasena WHERE id_usuario = :id_usuario";
                $stmt = $this->db->prepare($sql);
                $contrasena_hash = md5($data['contrasena']);
                $stmt->execute([
                    ':contrasena' => $contrasena_hash,
                    ':id_usuario' => $id_usuario
                ]);
            }
            
            // Actualizar datos del cliente
            $sql = "UPDATE Cliente SET 
                    nombre = :nombre,
                    apellido_materno = :apellido_materno,
                    apellido_paterno = :apellido_paterno,
                    fecha_nacimiento = :fecha_nacimiento,
                    telefono = :telefono,
                    direccion = :direccion,
                    ciudad = :ciudad
                    WHERE id_cliente = :id_cliente";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':apellido_materno' => $data['apellido_materno'],
                ':apellido_paterno' => $data['apellido_paterno'] ?? null,
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':telefono' => $data['telefono'] ?? null,
                ':direccion' => $data['direccion'] ?? null,
                ':ciudad' => $data['ciudad'] ?? null,
                ':id_cliente' => $id_cliente
            ]);
            
            $this->db->commit();
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    function borrar($id_cliente) {
        $this->conectar();
        
        // Al borrar Cliente, se eliminará Usuario por CASCADE
        $sql = "DELETE FROM Cliente WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_cliente]);
        
        return $stmt->rowCount();
    }
    
    function obtenerRoles() {
        $this->conectar();
        $sql = "SELECT * FROM Rol ORDER BY rol";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>