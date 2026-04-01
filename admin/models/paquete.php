<?php
require_once(__DIR__."/../sistema.class.php");

class Paquete extends Sistema {

    function leer() {
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Paquete_Reparacion ORDER BY id_paquete DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function leerUno($id) {
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Paquete_Reparacion WHERE id_paquete = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function crear($data) {
        $this->validarAcceso('paquete_crear');
        $data = $this->sanitizar($data);
        $this->conectar();

        $imagen = null;
        if (isset($_FILES['imagen_paquete']) && $_FILES['imagen_paquete']['error'] === UPLOAD_ERR_OK) {
            $imagen = $this->subirImagen($_FILES['imagen_paquete'], 'paquetes');
        }

        $sql = "INSERT INTO Paquete_Reparacion (nombre_paquete, descripcion, descuento_porcentaje, imagen_paquete, estado)
                VALUES (:nombre, :desc, :descuento, :imagen, :estado)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $data['nombre_paquete'],
            ':desc'      => $data['descripcion']          ?? null,
            ':descuento' => $data['descuento_porcentaje'] ?? 0,
            ':imagen'    => $imagen,
            ':estado'    => $data['estado']               ?? 'activo',
        ]);
    }

    function actualizar($id, $data) {
        $this->validarAcceso('paquete_editar');
        $data = $this->sanitizar($data);
        $this->conectar();

        $actual = $this->leerUno($id);
        $imagen = $actual['imagen_paquete'];

        if (isset($_FILES['imagen_paquete']) && $_FILES['imagen_paquete']['error'] === UPLOAD_ERR_OK) {
            $nueva = $this->subirImagen($_FILES['imagen_paquete'], 'paquetes');
            if ($nueva) {
                $this->eliminarImagen($imagen, 'paquetes');
                $imagen = $nueva;
            }
        }

        if (!empty($data['eliminar_imagen'])) {
            $this->eliminarImagen($imagen, 'paquetes');
            $imagen = null;
        }

        $sql = "UPDATE Paquete_Reparacion SET
                    nombre_paquete       = :nombre,
                    descripcion          = :desc,
                    descuento_porcentaje = :descuento,
                    imagen_paquete       = :imagen,
                    estado               = :estado
                WHERE id_paquete = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $data['nombre_paquete']       ?? $actual['nombre_paquete'],
            ':desc'      => $data['descripcion']          ?? $actual['descripcion'],
            ':descuento' => $data['descuento_porcentaje'] ?? $actual['descuento_porcentaje'],
            ':imagen'    => $imagen,
            ':estado'    => $data['estado']               ?? $actual['estado'],
            ':id'        => $id,
        ]);
    }

    function borrar($id) {
        $this->validarAcceso('paquete_eliminar');
        $this->conectar();
        $paquete = $this->leerUno($id);
        $stmt = $this->db->prepare("DELETE FROM Paquete_Reparacion WHERE id_paquete = :id");
        if ($stmt->execute([':id' => $id])) {
            if (!empty($paquete['imagen_paquete'])) {
                $this->eliminarImagen($paquete['imagen_paquete'], 'paquetes');
            }
            return true;
        }
        return false;
    }
}
?>