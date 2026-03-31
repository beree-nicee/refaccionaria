<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/paquete.php");

$app = new Paquete();
$id = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

switch($accion) {
    case 'crear':
        if(isset($_POST['enviar'])) {
            $app->crear($_POST);
            header("Location: paquete.php?mensaje=1");
            exit;
        }
        break;
    case 'actualizar':
        if(isset($_POST['enviar'])) {
            $app->actualizar($id, $_POST);
            header("Location: paquete.php?mensaje=2");
            exit;
        }
        break;
    case 'borrar':
        $app->borrar($id);
        header("Location: paquete.php?mensaje=3");
        exit;
        break;
}

include_once(__DIR__."/views/header.php");

if(isset($_GET['mensaje'])) {
    $msjs = ["1"=>"creado", "2"=>"actualizado", "3"=>"eliminado"];
    $app->alerta("success", "Paquete " . $msjs[$_GET['mensaje']] . " correctamente.");
}

switch($accion) {
    case 'crear':
    case 'actualizar':
        $data = ($accion == 'actualizar') ? $app->leerUno($id) : [];
        require(__DIR__."/views/paquete/formulario.php");
        break;
    default:
        $registros = $app->leer();
        require(__DIR__."/views/paquete/index.php");
        break;
}
include_once(__DIR__."/views/footer.php");
?>