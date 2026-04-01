<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/cita.php");

$app    = new Cita();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'crear':
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: cita.php?mensaje=1"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: cita.php?mensaje=2"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: cita.php?mensaje=3"); exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;
}

include_once(__DIR__."/views/header.php");

if (isset($_GET['mensaje'])) {
    $msjs = [
        "1" => ["success", "Cita agendada correctamente"],
        "2" => ["success", "Cita actualizada correctamente"],
        "3" => ["danger",  "Cita eliminada"],
    ];
    if (isset($msjs[$_GET['mensaje']]))
        $app->alerta($msjs[$_GET['mensaje']][0], $msjs[$_GET['mensaje']][1]);
}
if (isset($error)) $app->alerta("danger", $error);

switch ($accion) {
    case 'crear':
    case 'actualizar':
        // Cliente solo ve sus propios vehículos
        // Admin/técnico ven todos los usuarios
        $usuarios  = $app->esCliente() ? [] : $app->obtenerUsuarios();
        $servicios = $app->obtenerServicios();
        $vehiculos = $app->obtenerVehiculos(); // ya filtra por cliente en el modelo
        $tecnicos  = $app->esCliente() ? [] : $app->obtenerTecnicos();
        $data      = ($accion === 'actualizar') ? $app->leerUno($id) : [];
        require(__DIR__."/views/cita/formulario.php");
        break;

    default:
        $registros = $app->leer(); // ya filtra por cliente en el modelo
        require(__DIR__."/views/cita/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");