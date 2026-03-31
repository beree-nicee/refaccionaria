<?php
require_once(__DIR__."/../sistema.class.php");

class Permiso extends Sistema {

    function leer() {
        $this->validarAcceso('permiso_leer');
        $this->conectar();
        $sql = "SELECT p.id_permiso, p.permiso,
                       COUNT(rp.id_rol) as total_roles
                FROM Permiso p
                LEFT JOIN Rol_Permiso rp ON p.id_permiso = rp.id_permiso
                GROUP BY p.id_permiso
                ORDER BY p.permiso";
        $stmt = $this->db->prepare($sql);
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
        $data = $this->sanitizar($data);
        if (empty($data['permiso'])) throw new Exception("El nombre del permiso es obligatorio");

        $this->conectar();
        $stmt = $this->db->prepare("INSERT INTO Permiso (permiso) VALUES (:permiso)");
        $stmt->execute([':permiso' => $data['permiso']]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('permiso_editar');
        $data = $this->sanitizar($data);
        if (empty($data['permiso'])) throw new Exception("El nombre del permiso es obligatorio");

        $this->conectar();
        $stmt = $this->db->prepare("UPDATE Permiso SET permiso=:permiso WHERE id_permiso=:id");
        $stmt->execute([':permiso' => $data['permiso'], ':id' => $id]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('permiso_eliminar');
        $this->conectar();
        $stmt = $this->db->prepare("DELETE FROM Permiso WHERE id_permiso = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
?>
