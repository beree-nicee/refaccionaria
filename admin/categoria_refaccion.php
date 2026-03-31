<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/categoria_refaccion.php");

$app = new CategoriaRefaccion();
$id = $_GET['id'] ?? null;
$accion = $_GET['accion'] ?? null;

switch($accion) {
    case 'crear':
        if(isset($_POST['enviar'])) {
            $app->crear($_POST);
            header("Location: categoria_refaccion.php?mensaje=1");
            exit;
        }
        break;
    case 'actualizar':
        if(isset($_POST['enviar'])) {
            $app->actualizar($id, $_POST);
            header("Location: categoria_refaccion.php?mensaje=2");
            exit;
        }
        break;
    case 'borrar':
        $app->borrar($id);
        header("Location: categoria_refaccion.php?mensaje=3");
        exit;
        break;
}

include_once(__DIR__."/views/header.php");

if(isset($_GET['mensaje'])) {
    $msjs = ["1"=>"creada", "2"=>"actualizada", "3"=>"eliminada"];
    $app->alerta("success", "Categoría " . $msjs[$_GET['mensaje']] . " correctamente.");
}

switch($accion) {
    case 'crear':
    case 'actualizar':
        $data = ($accion == 'actualizar') ? $app->leerUno($id) : [];
        require(__DIR__."/views/categoria_refaccion/formulario.php");
        break;
    default:
        $registros = $app->leer();
        require(__DIR__."/views/categoria_refaccion/index.php");
        break;
}
include_once(__DIR__."/views/footer.php");
?>

