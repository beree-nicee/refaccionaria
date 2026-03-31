<?php
require_once(__DIR__."/../sistema.class.php");
class Compatibilidad extends Sistema {
    
    // Obtener todas las compatibilidades de una refacción
    public function leerPorRefaccion($id_refaccion) {
        $this->conectar();
        $sql = "SELECT * FROM Compatibilidad_Vehicular 
                WHERE id_refaccion = :id 
                ORDER BY marca_vehiculo, anio_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id_refaccion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $this->conectar();
        $data = $this->sanitizar($data);
        $sql = "INSERT INTO Compatibilidad_Vehicular (id_refaccion, marca_vehiculo, modelo_vehiculo, anio_inicio, anio_fin) 
                VALUES (:id_r, :marca, :modelo, :a_ini, :a_fin)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_r'   => $data['id_refaccion'],
            ':marca'  => $data['marca_vehiculo'],
            ':modelo' => $data['modelo_vehiculo'],
            ':a_ini'  => $data['anio_inicio'],
            ':a_fin'  => $data['anio_fin']
        ]);
    }

    public function borrar($id) {
        $this->conectar();
        $sql = "DELETE FROM Compatibilidad_Vehicular WHERE id_compatibilidad = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}