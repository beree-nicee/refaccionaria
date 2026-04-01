<?php
require_once(__DIR__."/../sistema.class.php");

class CategoriaRefaccion extends Sistema {

    function leer() {
        $this->validarAcceso('categoria_leer');
        $this->conectar();
        $sql = "SELECT * FROM Categoria_Refaccion ORDER BY nombre_categoria";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function leerUno($id) {
        $this->validarAcceso('categoria_leer');
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Categoria_Refaccion WHERE id_categoria = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function crear($data) {
        $this->validarAcceso('categoria_crear');
        $this->conectar();
        $data = $this->sanitizar($data);

        if (empty($data['nombre_categoria'])) throw new Exception("El nombre es obligatorio");

        $imagen = null;
        if (!empty($_FILES['imagen_categoria']['name'])) {
            $imagen = $this->subirImagen($_FILES['imagen_categoria'], 'categorias');
        }

        $sql = "INSERT INTO Categoria_Refaccion (nombre_categoria, descripcion, imagen_categoria)
                VALUES (:nombre, :desc, :img)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre_categoria'],
            ':desc'   => $data['descripcion'] ?? null,
            ':img'    => $imagen,
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('categoria_editar');
        $this->conectar();
        $data = $this->sanitizar($data);

        if (empty($data['nombre_categoria'])) throw new Exception("El nombre es obligatorio");

        // Obtener imagen actual
        $cat    = $this->leerUno($id);
        $imagen = $cat['imagen_categoria'];

        // Nueva imagen
        if (!empty($_FILES['imagen_categoria']['name'])) {
            $nueva = $this->subirImagen($_FILES['imagen_categoria'], 'categorias');
            if ($nueva) {
                $this->eliminarImagen($imagen, 'categorias');
                $imagen = $nueva;
            }
        }

        // Eliminar imagen
        if (!empty($data['eliminar_imagen'])) {
            $this->eliminarImagen($imagen, 'categorias');
            $imagen = null;
        }

        $sql = "UPDATE Categoria_Refaccion
                SET nombre_categoria=:nombre, descripcion=:desc, imagen_categoria=:img
                WHERE id_categoria=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre_categoria'],
            ':desc'   => $data['descripcion'] ?? null,
            ':img'    => $imagen,
            ':id'     => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('categoria_eliminar');
        $this->conectar();

        $cat = $this->leerUno($id);
        if (!empty($cat['imagen_categoria'])) {
            $this->eliminarImagen($cat['imagen_categoria'], 'categorias');
        }

        $stmt = $this->db->prepare("DELETE FROM Categoria_Refaccion WHERE id_categoria = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}
