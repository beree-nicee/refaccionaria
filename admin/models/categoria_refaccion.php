<?php
require_once(__DIR__."/../sistema.class.php");

class CategoriaRefaccion extends Sistema {
    
    function leer() {
        $this->conectar();
        $sql = "SELECT * FROM Categoria_Refaccion ORDER BY nombre_categoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function leerUno($id) {
        $this->conectar();
        $sql = "SELECT * FROM Categoria_Refaccion WHERE id_categoria = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function crear($data) {
        $data = $this->sanitizar($data);
        $this->conectar();
        $imagen = null;
        if (isset($_FILES['imagen_categoria']) && $_FILES['imagen_categoria']['error'] === UPLOAD_ERR_OK) {
            $imagen = $this->subirImagen($_FILES['imagen_categoria'], 'categorias');
        }
        
        $sql = "INSERT INTO Categoria_Refaccion (nombre_categoria, descripcion, imagen_categoria) 
                VALUES (:nombre, :desc, :imagen)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre_categoria'],
            ':desc' => $data['descripcion'] ?? null,
            ':imagen' => $imagen
        ]);
    }

    function actualizar($id, $data) {
        $data = $this->sanitizar($data);
        $this->conectar();
        
        if (isset($_FILES['imagen_categoria']) && $_FILES['imagen_categoria']['error'] === UPLOAD_ERR_OK) {
            $cat = $this->leerUno($id);
            if ($cat['imagen_categoria']) $this->eliminarImagen($cat['imagen_categoria'], 'categorias');
            $data['imagen_categoria'] = $this->subirImagen($_FILES['imagen_categoria'], 'categorias');
        }
        
        $sql = "UPDATE Categoria_Refaccion SET nombre_categoria=:nombre, descripcion=:desc";
        if(isset($data['imagen_categoria'])) $sql .= ", imagen_categoria=:imagen";
        $sql .= " WHERE id_categoria=:id";
        
        $params = [
            ':nombre' => $data['nombre_categoria'],
            ':desc' => $data['descripcion'],
            ':id' => $id
        ];
        if(isset($data['imagen_categoria'])) $params[':imagen'] = $data['imagen_categoria'];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    function borrar($id) {
        $this->conectar();
        $cat = $this->leerUno($id);
        $sql = "DELETE FROM Categoria_Refaccion WHERE id_categoria = :id";
        $stmt = $this->db->prepare($sql);
        if($stmt->execute([':id' => $id])){
            if ($cat['imagen_categoria']) $this->eliminarImagen($cat['imagen_categoria'], 'categorias');
            return true;
        }
        return false;
    }
}
?>