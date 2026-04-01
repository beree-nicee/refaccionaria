<?php
require_once(__DIR__."/../sistema.class.php");

class Rol extends Sistema {

    function leer() {
        $this->validarAcceso('rol_leer');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Rol ORDER BY rol");
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
        $this->conectar();
        $data = $this->sanitizar($data);
        if (empty($data['rol'])) throw new Exception("El nombre del rol es obligatorio");
        if (strlen($data['rol']) < 3) throw new Exception("El nombre debe tener al menos 3 caracteres");

        $stmt = $this->db->prepare("INSERT INTO Rol (rol, descripcion) VALUES (:rol, :desc)");
        $stmt->execute([
            ':rol'  => $data['rol'],
            ':desc' => $data['descripcion'] ?? null,
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('rol_editar');
        $this->conectar();
        $data = $this->sanitizar($data);
        if (empty($data['rol'])) throw new Exception("El nombre del rol es obligatorio");

        $stmt = $this->db->prepare(
            "UPDATE Rol SET rol=:rol, descripcion=:desc WHERE id_rol=:id"
        );
        $stmt->execute([
            ':rol'  => $data['rol'],
            ':desc' => $data['descripcion'] ?? null,
            ':id'   => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('rol_eliminar');
        $this->conectar();
        // Verificar que no tenga usuarios asignados
        $chk = $this->db->prepare("SELECT COUNT(*) FROM usuario_rol WHERE id_rol = :id");
        $chk->execute([':id' => $id]);
        if ($chk->fetchColumn() > 0)
            throw new Exception("No se puede eliminar: hay usuarios con este rol");

        $stmt = $this->db->prepare("DELETE FROM Rol WHERE id_rol = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
