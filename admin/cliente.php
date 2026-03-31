<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/cliente.php");

$app = new Cliente();
$app->requiereLogin();
$id = (isset($_GET['id'])) ? $_GET['id'] : null;
$accion = (isset($_GET['accion'])) ? $_GET['accion'] : null;

switch($accion) {
    case 'crear':
        if(isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: cliente.php?mensaje=1");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;
    case 'actualizar':
        if(isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: cliente.php?mensaje=2");
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;
    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: cliente.php?mensaje=3");
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        break;
}

include_once(__DIR__."/views/header.php");

if(isset($_GET['mensaje'])) {
    $m = $_GET['mensaje'];
    if($m==1) $app->alerta("success", "Cliente agregado correctamente");
    if($m==2) $app->alerta("success", "Cliente actualizado correctamente");
    if($m==3) $app->alerta("danger", "Cliente eliminado");
}

if(isset($error)) {
    $app->alerta("danger", $error);
}

switch($accion) {
    case 'crear':
    case 'actualizar':
        $data = ($accion == 'actualizar') ? $app->leerUno($id) : [];
        $roles = $app->obtenerRoles();
        require(__DIR__."/views/cliente/formulario.php");
        break;
    default:
        $registros = $app->leer();
        require(__DIR__."/views/cliente/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>