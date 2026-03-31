<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/orden.php");

$app    = new Orden();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: orden.php?mensaje=2"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'cancelar':
        try {
            $app->cancelar($id);
            header("Location: orden.php?mensaje=4"); exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;

    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: orden.php?mensaje=3"); exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;

    case 'pdf':
        require_once(__DIR__."/pdf_orden.php");
        generarPDFOrden($id, $app);
        exit;
}

include_once(__DIR__."/views/header.php");

$msjs = [
    "2" => "Orden actualizada correctamente",
    "3" => "Orden eliminada",
    "4" => "Orden cancelada",
];
if (isset($_GET['mensaje'])) $app->alerta("success", $msjs[$_GET['mensaje']] ?? '');
if (isset($error))           $app->alerta("danger",  $error);

switch ($accion) {
    case 'ver':
        $orden    = $app->leerUno($id);
        $detalles = $app->obtenerDetalles($id);
        require(__DIR__."/views/orden/detalle.php");
        break;

    case 'actualizar':
        $orden = $app->leerUno($id);
        require(__DIR__."/views/orden/formulario.php");
        break;

    default:
        $ordenes = $app->leer();
        require(__DIR__."/views/orden/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>
