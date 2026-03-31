<?php
require_once(__DIR__."/models/empleado.php");

$app    = new Empleado();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

// Técnico o admin que entra sin parámetros → edita su propio perfil
if (($app->esTecnico() || $app->esAdmin()) && !$accion) {
    $app->conectar();
    $stmt = $app->db->prepare(
        "SELECT id_empleado FROM Empleado WHERE id_usuario = :id"
    );
    $stmt->execute([':id' => $app->obtenerIdUsuario()]);
    $miEmpleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($miEmpleado) {
        $id     = $miEmpleado['id_empleado'];
        $accion = 'actualizar';
    }
    // Si no tiene registro de empleado (no debería pasar) sigue al listado
}

include_once(__DIR__."/views/header.php");

switch ($accion) {
    case 'crear':
        $app->validarAcceso('empleado_crear');
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: empleado.php?m=1"); exit;
            } catch (Exception $e) {
                $app->alerta("danger", $e->getMessage());
            }
        }
        $data = [];
        require(__DIR__."/views/empleado/formulario.php");
        break;

    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                // Si editó su propio perfil, volver al inicio con mensaje
                $esPropio = ($app->esTecnico() || $app->esAdmin()) &&
                            ($app->db->query("SELECT id_usuario FROM Empleado WHERE id_empleado=$id")
                                     ->fetchColumn() == $app->obtenerIdUsuario());
                $dest = $esPropio ? 'index.php?mensaje=perfil' : 'empleado.php?m=2';
                header("Location: $dest"); exit;
            } catch (Exception $e) {
                $app->alerta("danger", $e->getMessage());
            }
        }
        $data = $app->leerUno($id);
        require(__DIR__."/views/empleado/formulario.php");
        break;

    case 'borrar':
        $app->validarAcceso('empleado_eliminar');
        try {
            $app->borrar($id);
            header("Location: empleado.php?m=3"); exit;
        } catch (Exception $e) {
            $app->alerta("danger", "No se pudo eliminar: " . $e->getMessage());
        }
        break;

    default:
        $app->validarAcceso('empleado_leer');
        if (isset($_GET['m'])) {
            $msj = ["1" => "agregado", "2" => "actualizado", "3" => "eliminado"];
            $app->alerta("success", "Empleado " . ($msj[$_GET['m']] ?? '') . " correctamente.");
        }
        $registros = $app->leer();
        require(__DIR__."/views/empleado/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>
