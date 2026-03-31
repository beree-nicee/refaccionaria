<?php
require_once(__DIR__."/../sistema.class.php");

class RolPermiso extends Sistema {
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

    function obtenerPermisosDeRol($id_rol) {
        $this->validarAcceso('rol_permiso_gestionar');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT id_permiso FROM Rol_Permiso WHERE id_rol = :id_rol");
        $stmt->execute([':id_rol' => $id_rol]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function obtenerTodosPermisos() {
        $this->validarAcceso('rol_permiso_gestionar');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Permiso ORDER BY permiso");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function sincronizar($id_rol, $ids_permisos = []) {
        $this->validarAcceso('rol_permiso_gestionar');
        $this->conectar();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM Rol_Permiso WHERE id_rol = :id_rol");
            $stmt->execute([':id_rol' => $id_rol]);

            if (!empty($ids_permisos)) {
                $stmt = $this->db->prepare(
                    "INSERT IGNORE INTO Rol_Permiso (id_rol, id_permiso) VALUES (:id_rol, :id_permiso)"
                );
                foreach ($ids_permisos as $id_permiso) {
                    $stmt->execute([':id_rol' => $id_rol, ':id_permiso' => (int)$id_permiso]);
                }
            }
            $this->db->commit();
            unset($_SESSION['permisos']);
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
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

    function resumen() {
        $this->validarAcceso('rol_permiso_gestionar');
        $this->conectar();
        $sql = "SELECT r.id_rol, r.rol,
                       GROUP_CONCAT(p.permiso ORDER BY p.permiso SEPARATOR ', ') as permisos,
                       COUNT(rp.id_permiso) as total
                FROM Rol r
                LEFT JOIN Rol_Permiso rp ON r.id_rol = rp.id_rol
                LEFT JOIN Permiso p ON rp.id_permiso = p.id_permiso
                GROUP BY r.id_rol
                ORDER BY r.rol";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
