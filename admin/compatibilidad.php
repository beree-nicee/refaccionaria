<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/compatibilidad.php");


$app = new Compatibilidad();
$accion = $_GET['accion'] ?? null;
$id_refaccion = $_GET['id_refaccion'] ?? null;

switch($accion) {
    case 'guardar':
        if(isset($_POST['enviar'])) {
            $app->crear($_POST);
            header("Location: refaccion.php?accion=actualizar&id=" . $_POST['id_refaccion']);
            exit;
        }
        break;

    case 'borrar':
        $id_c = $_GET['id'];
        $app->borrar($id_c);
        header("Location: refaccion.php?accion=actualizar&id=" . $id_refaccion);
        exit;
        break;
}
?>