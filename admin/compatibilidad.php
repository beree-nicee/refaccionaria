<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/compatibilidad.php");


$app = new Compatibilidad();
$app->requiereLogin();
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
    default:
        require_once(__DIR__."/models/compatibilidad.php");
        $objComp = new Compatibilidad();
        
        // Obtenemos todos los registros para mostrar la tabla completa
        $compatibilidades = $objComp->leerTodo(); 

        include_once(__DIR__."/views/header.php");
        require(__DIR__."/views/compatibilidad/index.php");
        include_once(__DIR__."/views/footer.php");
        break;
}
?>