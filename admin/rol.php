<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/rol.php");

$app    = new Rol();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'crear':
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: rol.php?mensaje=1"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;
    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: rol.php?mensaje=2"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;
    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: rol.php?mensaje=3"); exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;
}

include_once(__DIR__."/views/header.php");

$msjs = ["1"=>"Rol creado correctamente","2"=>"Rol actualizado correctamente","3"=>"Rol eliminado"];
if (isset($_GET['mensaje'])) $app->alerta("success", $msjs[$_GET['mensaje']]);
if (isset($error))           $app->alerta("danger",  $error);

switch ($accion) {
    case 'crear':
    case 'actualizar':
        $data = ($accion === 'actualizar') ? $app->leerUno($id) : [];
        require(__DIR__."/views/rol/formulario.php");
        break;
    default:
        $registros = $app->leer();
        require(__DIR__."/views/rol/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>
