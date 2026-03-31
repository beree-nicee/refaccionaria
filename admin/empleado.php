<?php
require_once(__DIR__."/models/empleado.php");
$app = new Empleado();
$app->requiereLogin();
$id = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

include_once(__DIR__."/views/header.php");

switch($accion) {
    case 'crear':
        if(isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                echo "<script>window.location='empleado.php?m=1';</script>";
            } catch (Exception $e) {
                $app->alerta("danger", $e->getMessage());
            }
        }
        $roles = $app->obtenerRoles();
        $data = [];
        require(__DIR__."/views/empleado/formulario.php");
        break;

    case 'actualizar':
        if(isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                echo "<script>window.location='empleado.php?m=2';</script>";
            } catch (Exception $e) {
                $app->alerta("danger", $e->getMessage());
            }
        }
        $data = $app->leerUno($id);
        $roles = $app->obtenerRoles();
        require(__DIR__."/views/empleado/formulario.php");
        break;

    case 'borrar':
        try {
            $app->borrar($id);
            echo "<script>window.location='empleado.php?m=3';</script>";
        } catch (Exception $e) {
            $app->alerta("danger", "No se pudo eliminar: " . $e->getMessage());
        }
        break;

    default:
        if(isset($_GET['m'])) {
            $msj = ["1"=>"Agregado", "2"=>"Actualizado", "3"=>"Eliminado"];
            $app->alerta("success", "Empleado " . $msj[$_GET['m']] . " correctamente.");
        }
        $registros = $app->leer();
        require(__DIR__."/views/empleado/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");