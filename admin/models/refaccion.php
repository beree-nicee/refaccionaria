<?php
require_once(__DIR__."/../sistema.class.php");

class Refaccion extends Sistema {

    function leer() {
        $this->validarAcceso('refaccion_leer');
        $this->conectar();
        $sql = "SELECT r.*, c.nombre_categoria
                FROM Refaccion r
                JOIN Categoria_Refaccion c ON r.id_categoria = c.id_categoria
                ORDER BY r.fecha_agregado DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function leerUno($id) {
        $this->validarAcceso('refaccion_leer');
        $this->conectar();
        $sql = "SELECT r.*, c.nombre_categoria
                FROM Refaccion r
                JOIN Categoria_Refaccion c ON r.id_categoria = c.id_categoria
                WHERE r.id_refaccion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    function obtenerCategorias() {
        $this->conectar();
        $stmt = $this->db->prepare("SELECT * FROM Categoria_Refaccion ORDER BY nombre_categoria");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function crear($data) {
        $this->validarAcceso('refaccion_crear');
        $this->conectar();
        $data = $this->sanitizar($data);

        // Validaciones
        if (empty($data['nombre']))           throw new Exception("El nombre es obligatorio");
        if (empty($data['codigo_producto']))  throw new Exception("El código de producto es obligatorio");
        if (empty($data['precio']) || $data['precio'] < 0) throw new Exception("El precio debe ser mayor a 0");
        if ($this->codigoExiste($data['codigo_producto'])) throw new Exception("El código de producto ya existe");

        // Subir imagen si viene
        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->subirImagen($_FILES['imagen'], 'refacciones');
        }

        $sql = "INSERT INTO Refaccion
                    (id_categoria, codigo_producto, nombre, descripcion, marca_refaccion,
                     precio, stock_actual, stock_minimo, imagen,
                     especificaciones_tecnicas, peso, estado_producto)
                VALUES
                    (:id_cat, :cod, :nom, :desc, :marca,
                     :precio, :stock, :stock_min, :imagen,
                     :specs, :peso, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_cat'    => $data['id_categoria'],
            ':cod'       => $data['codigo_producto'],
            ':nom'       => $data['nombre'],
            ':desc'      => $data['descripcion']             ?? null,
            ':marca'     => $data['marca_refaccion']         ?? null,
            ':precio'    => $data['precio'],
            ':stock'     => $data['stock_actual']            ?? 0,
            ':stock_min' => $data['stock_minimo']            ?? 5,
            ':imagen'    => $imagen,
            ':specs'     => $data['especificaciones_tecnicas'] ?? null,
            ':peso'      => $data['peso']                    ?? null,
            ':estado'    => $data['estado_producto']         ?? 'disponible',
        ]);
        return $stmt->rowCount();
    }

    function actualizar($id, $data) {
        $this->validarAcceso('refaccion_editar');
        $this->conectar();
        $data = $this->sanitizar($data);

        if (empty($data['nombre']))          throw new Exception("El nombre es obligatorio");
        if (empty($data['codigo_producto'])) throw new Exception("El código es obligatorio");
        if ($this->codigoExiste($data['codigo_producto'], $id)) throw new Exception("El código ya lo usa otra refacción");

        // Obtener imagen actual
        $actual = $this->leerUno($id);
        $imagen = $actual['imagen'];

        // Si sube nueva imagen
        if (!empty($_FILES['imagen']['name'])) {
            $nueva = $this->subirImagen($_FILES['imagen'], 'refacciones');
            if ($nueva) {
                $this->eliminarImagen($imagen, 'refacciones');
                $imagen = $nueva;
            }
        }

        // Si marcó "eliminar imagen"
        if (!empty($data['eliminar_imagen'])) {
            $this->eliminarImagen($imagen, 'refacciones');
            $imagen = null;
        }

        $sql = "UPDATE Refaccion SET
                    id_categoria = :id_cat,
                    codigo_producto = :cod,
                    nombre = :nom,
                    descripcion = :desc,
                    marca_refaccion = :marca,
                    precio = :precio,
                    stock_actual = :stock,
                    stock_minimo = :stock_min,
                    imagen = :imagen,
                    especificaciones_tecnicas = :specs,
                    peso = :peso,
                    estado_producto = :estado
                WHERE id_refaccion = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_cat'    => $data['id_categoria'],
            ':cod'       => $data['codigo_producto'],
            ':nom'       => $data['nombre'],
            ':desc'      => $data['descripcion']               ?? null,
            ':marca'     => $data['marca_refaccion']           ?? null,
            ':precio'    => $data['precio'],
            ':stock'     => $data['stock_actual']              ?? 0,
            ':stock_min' => $data['stock_minimo']              ?? 5,
            ':imagen'    => $imagen,
            ':specs'     => $data['especificaciones_tecnicas'] ?? null,
            ':peso'      => $data['peso']                      ?? null,
            ':estado'    => $data['estado_producto']           ?? 'disponible',
            ':id'        => $id,
        ]);
        return $stmt->rowCount();
    }

    function borrar($id) {
        $this->validarAcceso('refaccion_eliminar');
        $this->conectar();

        // Eliminar imagen asociada
        $actual = $this->leerUno($id);
        if (!empty($actual['imagen'])) {
            $this->eliminarImagen($actual['imagen'], 'refacciones');
        }

        $stmt = $this->db->prepare("DELETE FROM Refaccion WHERE id_refaccion = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    private function codigoExiste($codigo, $excluir_id = null) {
        $sql = "SELECT COUNT(*) FROM Refaccion WHERE codigo_producto = :cod";
        if ($excluir_id) $sql .= " AND id_refaccion != :id";
        $stmt = $this->db->prepare($sql);
        $params = [':cod' => $codigo];
        if ($excluir_id) $params[':id'] = $excluir_id;
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
?>
