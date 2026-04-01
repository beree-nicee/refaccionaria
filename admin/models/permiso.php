<?php
require_once(__DIR__."/../sistema.class.php");

class Permiso extends Sistema {

    function leer() {
        $this->validarAcceso('permiso_leer');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Permiso ORDER BY permiso");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function leerUno($id) {
        $this->validarAcceso('permiso_leer');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Permiso WHERE id_permiso = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    function crear($data) {
        $this->validarAcceso('permiso_crear');
        $this->conectar();
        $data = $this->sanitizar($data);
        if (empty($data['permiso']))
            throw new Exception("El nombre del permiso es obligatorio");
        // Formato: solo letras minúsculas, números y guion bajo
        if (!preg_match('/^[a-z][a-z0-9_]+$/', $data['permiso']))
            throw new Exception("El permiso solo puede tener letras minúsculas, números y guion bajo, y debe empezar con letra");

        // Verificar que no exista
        $chk = $this->db->prepare("SELECT COUNT(*) FROM Permiso WHERE permiso = :p");
        $chk->execute([':p' => $data['permiso']]);
        if ($chk->fetchColumn() > 0)
            throw new Exception("Ya existe un permiso con ese nombre");

        $stmt = $this->db->prepare(
            "INSERT INTO Permiso (permiso, descripcion) VALUES (:permiso, :desc)"
        );
        $stmt->execute([
            ':permiso' => $data['permiso'],
            ':desc'    => $data['descripcion'] ?? null,
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('permiso_editar');
        $this->conectar();
        $data = $this->sanitizar($data);
        if (empty($data['permiso']))
            throw new Exception("El nombre del permiso es obligatorio");
        if (!preg_match('/^[a-z][a-z0-9_]+$/', $data['permiso']))
            throw new Exception("El permiso solo puede tener letras minúsculas, números y guion bajo");

        $stmt = $this->db->prepare(
            "UPDATE Permiso SET permiso=:permiso, descripcion=:desc WHERE id_permiso=:id"
        );
        $stmt->execute([
            ':permiso' => $data['permiso'],
            ':desc'    => $data['descripcion'] ?? null,
            ':id'      => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('permiso_eliminar');
        $this->conectar();
        // Verificar que no esté asignado a ningún rol
        $chk = $this->db->prepare("SELECT COUNT(*) FROM Rol_Permiso WHERE id_permiso = :id");
        $chk->execute([':id' => $id]);
        if ($chk->fetchColumn() > 0)
            throw new Exception("No se puede eliminar: el permiso está asignado a uno o más roles");

        $stmt = $this->db->prepare("DELETE FROM Permiso WHERE id_permiso = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
