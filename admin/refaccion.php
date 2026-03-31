<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/refaccion.php");

$app = new Refaccion();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

// --- PROCESAMIENTO DE DATOS (POST) ---
switch ($accion) {
    case 'crear':
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: refaccion.php?mensaje=1"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: refaccion.php?mensaje=2"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;

    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: refaccion.php?mensaje=3"); exit;
        } catch (Exception $e) { $error = $e->getMessage(); }
        break;
}

// --- SALIDA DE VISTAS (HTML) ---
include_once(__DIR__."/views/header.php");

// Alertas
$msjs = ["1" => "Refacción agregada correctamente", "2" => "Refacción actualizada correctamente", "3" => "Refacción eliminada"];
if (isset($_GET['mensaje'])) $app->alerta("success", $msjs[$_GET['mensaje']]);
if (isset($error)) $app->alerta("danger", $error);

// Selección de vista
switch ($accion) {
    case 'crear':
    case 'actualizar':
        $data = ($accion === 'actualizar') ? $app->leerUno($id) : [];
        $categorias = $app->obtenerCategorias();
        
        // CARGA DE COMPATIBILIDADES SOLO SI ES ACTUALIZAR
        $compatibilidades = []; 
        if ($accion === 'actualizar') {
            require_once(__DIR__."/models/compatibilidad.php");
            $objComp = new Compatibilidad();
            $compatibilidades = $objComp->leerPorRefaccion($id);
        }
        
        require(__DIR__."/views/refaccionV/formulario.php");
        break;

    default:
        $refacciones = $app->leer();
        require(__DIR__."/views/refaccionV/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");