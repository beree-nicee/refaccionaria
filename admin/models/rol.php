<?php
require_once(__DIR__."/../sistema.class.php");

class Rol extends Sistema {

    function leer() {
        $this->validarAcceso('rol_leer');
        $this->conectar();
        $sql = "SELECT r.*, COUNT(rp.id_permiso) as total_permisos
                FROM Rol r
                LEFT JOIN Rol_Permiso rp ON r.id_rol = rp.id_rol
                GROUP BY r.id_rol
                ORDER BY r.rol";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function leerUno($id) {
        $this->validarAcceso('rol_leer');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Rol WHERE id_rol = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    function crear($data) {
        $this->validarAcceso('rol_crear');
        $data = $this->sanitizar($data);
        if (empty($data['rol'])) throw new Exception("El nombre del rol es obligatorio");

        $this->conectar();
        $stmt = $this->db->prepare("INSERT INTO Rol (rol) VALUES (:rol)");
        $stmt->execute([':rol' => strtolower(trim($data['rol']))]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('rol_editar');
        $data = $this->sanitizar($data);
        if (empty($data['rol'])) throw new Exception("El nombre del rol es obligatorio");

        $this->conectar();
        $stmt = $this->db->prepare("UPDATE Rol SET rol = :rol WHERE id_rol = :id");
        $stmt->execute([':rol' => strtolower(trim($data['rol'])), ':id' => $id]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('rol_eliminar');
        $this->conectar();
        // Verificar que no haya usuarios con ese rol
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Usuario WHERE id_rol = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->fetch()['total'] > 0)
            throw new Exception("No se puede eliminar: hay usuarios asignados a este rol");

        $stmt = $this->db->prepare("DELETE FROM Rol WHERE id_rol = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
?>
