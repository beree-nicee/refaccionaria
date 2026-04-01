<?php
require_once(__DIR__."/../sistema.class.php");

class Servicio extends Sistema {

    function leer() {
        $this->conectar();
        $sql = "SELECT * FROM Servicio";
        if ($this->esCliente()) {
            $sql .= " WHERE estado = 'activo'";
        }
        $sql .= " ORDER BY categoria_servicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias() {
        $this->conectar();
        $sql = "SELECT DISTINCT categoria_servicio FROM Servicio WHERE categoria_servicio IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function leerUno($id) {
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Servicio WHERE id_servicio = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function crear($data) {
        $this->validarAcceso('servicio_crear');
        $data = $this->sanitizar($data);

        if (empty($data['nombre_servicio']))
            throw new Exception("El nombre del servicio es obligatorio");
        if (!isset($data['precio_mano_obra']) || $data['precio_mano_obra'] < 0)
            throw new Exception("El precio debe ser mayor o igual a 0");

        $this->conectar();

        $imagen = null;
        if (isset($_FILES['imagen_servicio']) && $_FILES['imagen_servicio']['error'] === UPLOAD_ERR_OK) {
            $imagen = $this->subirImagen($_FILES['imagen_servicio'], 'servicios');
        }

        $sql = "INSERT INTO Servicio (nombre_servicio, descripcion, precio_mano_obra,
                tiempo_estimado, imagen_servicio, categoria_servicio, estado)
                VALUES (:nombre, :desc, :precio, :tiempo, :imagen, :categoria, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'    => $data['nombre_servicio'],
            ':desc'      => $data['descripcion']       ?? null,
            ':precio'    => $data['precio_mano_obra'],
            ':tiempo'    => $data['tiempo_estimado']   ?? null,
            ':imagen'    => $imagen,
            ':categoria' => $data['categoria_servicio'] ?? null,
            ':estado'    => $data['estado']            ?? 'activo',
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('servicio_editar');
        $data = $this->sanitizar($data);
        $this->conectar();

        // Obtener datos actuales para no perder campos no enviados
        $actual = $this->leerUno($id);
        $imagen = $actual['imagen_servicio'];

        // Nueva imagen
        if (isset($_FILES['imagen_servicio']) && $_FILES['imagen_servicio']['error'] === UPLOAD_ERR_OK) {
            $nueva = $this->subirImagen($_FILES['imagen_servicio'], 'servicios');
            if ($nueva) {
                $this->eliminarImagen($imagen, 'servicios');
                $imagen = $nueva;
            }
        }

        // Eliminar imagen si se marcó
        if (!empty($data['eliminar_imagen'])) {
            $this->eliminarImagen($imagen, 'servicios');
            $imagen = null;
        }

        $sql = "UPDATE Servicio SET
                    nombre_servicio    = :nombre,
                    descripcion        = :desc,
                    precio_mano_obra   = :precio,
                    tiempo_estimado    = :tiempo,
                    imagen_servicio    = :imagen,
                    categoria_servicio = :categoria,
                    estado             = :estado
                WHERE id_servicio = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'    => $data['nombre_servicio']   ?? $actual['nombre_servicio'],
            ':desc'      => $data['descripcion']       ?? $actual['descripcion'],
            ':precio'    => $data['precio_mano_obra']  ?? $actual['precio_mano_obra'],
            ':tiempo'    => $data['tiempo_estimado']   ?? $actual['tiempo_estimado'],
            ':imagen'    => $imagen,
            ':categoria' => $data['categoria_servicio'] ?? $actual['categoria_servicio'],
            ':estado'    => $data['estado']            ?? $actual['estado'],
            ':id'        => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('servicio_eliminar');
        $this->conectar();
        $servicio = $this->leerUno($id);
        $stmt = $this->db->prepare("DELETE FROM Servicio WHERE id_servicio = :id");
        $stmt->execute([':id' => $id]);
        if (!empty($servicio['imagen_servicio'])) {
            $this->eliminarImagen($servicio['imagen_servicio'], 'servicios');
        }
        return $stmt->rowCount();
    }
}
