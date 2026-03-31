<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/permiso.php");

$app    = new Permiso();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'crear':
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: permiso.php?mensaje=1"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;
    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: permiso.php?mensaje=2"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;
    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: permiso.php?mensaje=3"); exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;
}

include_once(__DIR__."/views/header.php");

$msjs = ["1"=>"Permiso creado","2"=>"Permiso actualizado","3"=>"Permiso eliminado"];
if (isset($_GET['mensaje'])) $app->alerta("success", $msjs[$_GET['mensaje']]);
if (isset($error))           $app->alerta("danger",  $error);

switch ($accion) {
    case 'crear':
    case 'actualizar':
        $data = ($accion === 'actualizar') ? $app->leerUno($id) : [];
        require(__DIR__."/views/permiso/formulario.php");
        break;
    default:
        $registros = $app->leer();
        require(__DIR__."/views/permiso/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>
