<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/cita.php");

$app = new Cita();
$app->requiereLogin();
$id = (isset($_GET['id'])) ? $_GET['id'] : null;
$accion = (isset($_GET['accion'])) ? $_GET['accion'] : null;

// --- PARTE 1: PROCESAMIENTO DE DATOS (POST) ---
switch($accion) {
    case 'crear':
        if(isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: cita.php?mensaje=1");
                exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'actualizar':
        if(isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: cita.php?mensaje=2");
                exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: cita.php?mensaje=3");
            exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;
}

// --- PARTE 2: CABECERA Y ALERTAS ---
include_once(__DIR__."/views/header.php");

if(isset($_GET['mensaje'])) {
    $msjs = ["1"=>["success","Cita agendada"], "2"=>["success","Cita actualizada"], "3"=>["danger","Cita eliminada"]];
    $m = $_GET['mensaje'];
    if(isset($msjs[$m])) $app->alerta($msjs[$m][0], $msjs[$m][1]);
}
if(isset($error)) $app->alerta("danger", $error);

// --- PARTE 3: MOSTRAR VISTAS (RENDER) ---
switch($accion) {
    case 'crear':
    case 'actualizar':
        // Cargamos todas las listas necesarias para los select del formulario
        $usuarios  = $app->obtenerUsuarios(); 
        $servicios = $app->obtenerServicios();
        $vehiculos = $app->obtenerVehiculos();
        $tecnicos  = $app->obtenerTecnicos(); // Asegúrate de tener esta función en el modelo
        
        $data = ($accion == 'actualizar') ? $app->leerUno($id) : [];
        require(__DIR__."/views/cita/formulario.php");
        break;

    default:
        $registros = $app->leer();
        require(__DIR__."/views/cita/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>