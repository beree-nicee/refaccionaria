<?php
require_once(__DIR__."/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Sistema {
    var $db;
    
    function conectar() {
        $this->db = new PDO(
            DBDRIVER.":host=".DBHOST.";dbname=".DBNAME.";port=".DBPORT, 
            DBUSER, 
            DBPASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }


    function alerta($tipo, $mensaje) {
        if(!is_null($tipo) && !is_null($mensaje)) {
            $alerta = array();
            $alerta['tipo'] = $tipo;
            $alerta['mensaje'] = $mensaje;
            include(__DIR__."/views/alerta.php");
        }
    }

    // ========== RBAC - Sistema de Permisos ==========
    
    function cargarPermisos() {
        if (isset($_SESSION['permisos'])) return;
        if (!isset($_SESSION['id_rol'])) {
            $_SESSION['permisos'] = [];
            return;
        }
        $this->conectar();
        $sql = "SELECT p.permiso
                FROM Rol_Permiso rp
                INNER JOIN Permiso p ON rp.id_permiso = p.id_permiso
                WHERE rp.id_rol = :id_rol";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_rol' => $_SESSION['id_rol']]);
        $_SESSION['permisos'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function verificarPermiso($permiso) {
        if (!isset($_SESSION['id_usuario'])) return false;
        if ($this->esAdmin()) return true;
        $this->cargarPermisos();
        return in_array($permiso, $_SESSION['permisos'] ?? []);
    }

    /*
    function validarAcceso($permiso, $redirigir = true) {
        if (!$this->verificarPermiso($permiso)) {
            if ($redirigir) {
                $this->alerta('danger', 'No tienes permiso para realizar esta acción');
                header('Location: index.php');
                exit;
            }
            return false;
        }
        return true;
    }
        */

    function validarAcceso($permiso) {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: login.php');
            exit;
        }
        if (!$this->verificarPermiso($permiso)) {
            include_once(__DIR__."/views/header.php");
            $this->alerta('danger', 'No tienes permiso para realizar esta accion.');
            include_once(__DIR__."/views/footer.php");
            exit;
        }
    }
    
    function requiereLogin() {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: login.php');
            exit;
        }
    }
    
    function esAdmin() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';
    }
    
    function esTecnico() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Tecnico';
    }
    
    function esCliente() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Cliente';
    }
    
    function obtenerIdUsuario() {
        return $_SESSION['id_usuario'] ?? null;
    }
    
    function obtenerRolNombre() { return $_SESSION['rol'] ?? null; }
    //Validaciones importantes
    
    function sanitizar($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizar'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    function validarTelefono($telefono) {
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        return strlen($telefono) === 10;
    }
    
    //Para subir los archivos
    
    function subirImagen($archivo, $carpeta = 'productos') {
        if (empty($archivo['name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Formato no permitido.");
        }
        
        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("La imagen excede 5MB");
        }
        
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        
        // CORRECCIÓN DE RUTA: __DIR__ ya incluye "admin", así que entramos directo a uploads
        $directorioDestino = __DIR__ . "/../uploads/{$carpeta}/";
        $rutaDestino = $directorioDestino . $nombreArchivo;
        
        // Crear carpeta si no existe
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }
        
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            // Intentar dar permisos al archivo recién creado
            chmod($rutaDestino, 0666); 
            return $nombreArchivo;
        }
        
        throw new Exception("Error al mover el archivo a: " . $directorioDestino);
    }

    function subirImagenConNombre($archivo, $carpeta, $nombreBase) {
        if (empty($archivo['name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Formato no permitido.");
        }

        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("La imagen excede 5MB");
        }

        // Nombre limpio basado en el nombre de la refacción
        $nombreArchivo     = $nombreBase . '.' . $extension;
        $directorioDestino = __DIR__ . "/../uploads/{$carpeta}/";
        $rutaDestino       = $directorioDestino . $nombreArchivo;

        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            chmod($rutaDestino, 0666);
            return $nombreArchivo;
        }

        throw new Exception("Error al mover el archivo.");
    }
    
    function eliminarImagen($nombreArchivo, $carpeta = 'productos') {
        if (empty($nombreArchivo)) return false;
        
        $rutaArchivo = __DIR__ . "/../uploads/{$carpeta}/" . $nombreArchivo;
        
        if (file_exists($rutaArchivo)) {
            return unlink($rutaArchivo);
        }
        
        return false;
    }
    
    //Utilidades que use con el profe Grimaldo
    
    function formatearPrecio($precio) {
        return '$' . number_format($precio, 2, '.', ',');
    }
    
    function formatearFecha($fecha, $formato = 'd/m/Y') {
        if (empty($fecha)) return '';
        return date($formato, strtotime($fecha));
    }

    /*
    public function login($correo, $contrasena){
        $this->conectar();
        $pass_md5 = md5($contrasena);
        $sql = "SELECT * FROM Usuario WHERE email = :correo AND contrasena_hash = :pass";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo, ':pass' => $pass_md5]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario){
            // Guardamos los datos básicos en la sesión
            $_SESSION['validado'] = true;
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['email'] = $usuario['email'];
            return true;
        }
        
        return false;
    }

    */

    public function login($correo, $contrasena) {
        $this->conectar();
        $pass_md5 = md5($contrasena);

        // Consulta corregida para usar la tabla intermedia usuario_rol
        $sql = "SELECT u.*, r.id_rol, r.rol as nombre_rol
                FROM Usuario u
                INNER JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
                INNER JOIN Rol r         ON ur.id_rol = r.id_rol
                WHERE u.email = :correo
                AND u.contrasena_hash = :pass
                AND u.estado_cuenta = 'activa'";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo, ':pass' => $pass_md5]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            session_regenerate_id(true);
            $_SESSION['validado']   = true;
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['email']      = $usuario['email'];
            $_SESSION['id_rol']     = $usuario['id_rol']; // Importante para cargarPermisos()
            $_SESSION['rol']        = $usuario['nombre_rol'];

            // Carga de nombre y apellido (tu código original)
            $sqlNombre = "SELECT COALESCE(e.nombre, c.nombre, '') as nombre,
                                COALESCE(e.apellido_paterno, c.apellido_paterno, '') as apellido_paterno
                        FROM Usuario u
                        LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                        LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                        WHERE u.id_usuario = :id";
            $stmtN = $this->db->prepare($sqlNombre);
            $stmtN->execute([':id' => $usuario['id_usuario']]);
            $perfil = $stmtN->fetch(PDO::FETCH_ASSOC);

            $_SESSION['nombre']   = $perfil['nombre'] ?? '';
            $_SESSION['apellido'] = $perfil['apellido_paterno'] ?? '';

            // Ahora sí, cargamos los permisos con el ID de rol correcto
            unset($_SESSION['permisos']); // Forzamos la recarga
            $this->cargarPermisos();
            return true;
        }
        return false;
    }

    public function logout(){
        session_unset();
        session_destroy();
    }

    
}
