<?php
require_once(__DIR__."/sistema.class.php");
require_once(__DIR__."/models/rol_permiso.php");
require_once(__DIR__."/models/rol.php");
require_once(__DIR__."/models/permiso.php");

$app    = new RolPermiso();
$appRol = new Rol();
$appPermiso = new Permiso();
$app->requiereLogin();

$id_rol = $_GET['id_rol'] ?? null;
$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'editar':
        if (isset($_POST['enviar'])) {
            try {
                $ids = $_POST['permisos'] ?? [];
                $app->sincronizar($id_rol, $ids);
                header("Location: rol_permiso.php?mensaje=1"); exit;
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
        break;
}

include_once(__DIR__."/views/header.php");

if (isset($_GET['mensaje'])) $app->alerta("success", "Permisos actualizados correctamente");
if (isset($error))           $app->alerta("danger",  $error);

switch ($accion) {
    case 'editar':
        $rol= $appRol->leerUno($id_rol);
        $permisosAsignados = $app->obtenerPermisosDeRol($id_rol);
        $todosPermisos    = $app->obtenerTodosPermisos();
        require(__DIR__."/views/rol_permiso/formulario.php");
        break;
    default:
        $registros = $app->resumen();
        require(__DIR__."/views/rol_permiso/index.php");
        break;
}

include_once(__DIR__."/views/footer.php");
?>
