<?php
require_once(__DIR__."/models/empleado.php");

$app    = new Empleado();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'crear':
        $app->validarAcceso('empleado_crear');
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: empleado.php?m=1"); exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;

    case 'perfil':
        // Buscar el registro del empleado en sesión
        $app->conectar();
        $stmt = $app->db->prepare("SELECT id_empleado FROM Empleado WHERE id_usuario = :id");
        $stmt->execute([':id' => $app->obtenerIdUsuario()]);
        $miEmpleado = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($miEmpleado) {
            $id     = $miEmpleado['id_empleado'];
            $accion = 'actualizar';
        }
        // Sin break — cae al case actualizar

    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                header("Location: empleado.php?m=2"); exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;

    case 'borrar':
        $app->validarAcceso('empleado_eliminar');
        try {
            $app->borrar($id);
            header("Location: empleado.php?m=3"); exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        break;
}

include_once(__DIR__."/views/header.php");

if (isset($_GET['m'])) {
    $msj = ["1" => "agregado", "2" => "actualizado", "3" => "eliminado"];
    $app->alerta("success", "Empleado " . ($msj[$_GET['m']] ?? '') . " correctamente.");
}
if (isset($error)) $app->alerta("danger", $error);

switch ($accion) {
    case 'crear':
        $data = [];
        require(__DIR__."/views/empleado/formulario.php");
        break;

    case 'actualizar':
        $data = $app->leerUno($id);
        require(__DIR__."/views/empleado/formulario.php");
        break;

    default:
        $app->validarAcceso('empleado_leer');
        $registros = $app->leer();
        require(__DIR__."/views/empleado/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>