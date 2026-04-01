<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/cliente.php");

$app    = new Cliente();
$app->requiereLogin();

$id     = $_GET['id']     ?? null;
$accion = $_GET['accion'] ?? null;

// Si es cliente, siempre edita su propio perfil
if ($app->esCliente()) {
    $app->conectar();
    $stmt = $app->db->prepare("SELECT id_cliente FROM Cliente WHERE id_usuario = :id");
    $stmt->execute([':id' => $app->obtenerIdUsuario()]);
    $miCliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($miCliente) {
        $id     = $miCliente['id_cliente'];
        $accion = 'actualizar';
    }
}

switch ($accion) {
    case 'crear':
        if (isset($_POST['enviar'])) {
            try {
                $app->crear($_POST);
                header("Location: cliente.php?mensaje=1"); exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;

    case 'actualizar':
        if (isset($_POST['enviar'])) {
            try {
                $app->actualizar($id, $_POST);
                $dest = $app->esCliente() ? 'index.php' : 'cliente.php?mensaje=2';
                header("Location: $dest"); exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        break;

    case 'borrar':
        try {
            $app->borrar($id);
            header("Location: cliente.php?mensaje=3"); exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        break;
}

include_once(__DIR__."/views/header.php");

if (isset($_GET['mensaje'])) {
    $msjs = ["1"=>"Cliente agregado","2"=>"Cliente actualizado","3"=>"Cliente eliminado"];
    $app->alerta("success", $msjs[$_GET['mensaje']] ?? '');
}
if (isset($error)) $app->alerta("danger", $error);

switch ($accion) {
    case 'crear':
    case 'actualizar':
        $data  = ($accion === 'actualizar') ? $app->leerUno($id) : [];
        $roles = $app->obtenerRoles();
        require(__DIR__."/views/cliente/formulario.php");
        break;
    default:
        $app->validarAcceso('cliente_leer');
        $registros = $app->leer();
        require(__DIR__."/views/cliente/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>