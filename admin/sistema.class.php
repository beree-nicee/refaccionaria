<?php
require_once(__DIR__."/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Sistema {
    var $db;

    function conectar() {
        if (!is_null($this->db)) return;
        $this->db = new PDO(
            DBDRIVER.":host=".DBHOST.";dbname=".DBNAME.";port=".DBPORT,
            DBUSER, DBPASSWORD,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    // =============================================
    // ALERTAS
    // =============================================
    function alerta($tipo, $mensaje) {
        if (!is_null($tipo) && !is_null($mensaje)) {
            $alerta = ['tipo' => $tipo, 'mensaje' => $mensaje];
            include(__DIR__."/views/alerta.php");
        }
    }

    // =============================================
    // RBAC
    // =============================================
    function cargarPermisos() {
        if (isset($_SESSION['permisos'])) return;
        if (!isset($_SESSION['id_rol'])) {
            $_SESSION['permisos'] = [];
            return;
        }
        $this->conectar();
        $stmt = $this->db->prepare(
            "SELECT p.permiso FROM Rol_Permiso rp
             INNER JOIN Permiso p ON rp.id_permiso = p.id_permiso
             WHERE rp.id_rol = :id_rol"
        );
        $stmt->execute([':id_rol' => $_SESSION['id_rol']]);
        $_SESSION['permisos'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function verificarPermiso($permiso) {
        if (!isset($_SESSION['id_usuario'])) return false;
        if ($this->esAdmin()) return true;
        $this->cargarPermisos();
        return in_array($permiso, $_SESSION['permisos'] ?? []);
    }

    function validarAcceso($permiso) {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: login.php');
            exit;
        }
        if (!$this->verificarPermiso($permiso)) {
            include_once(__DIR__."/views/header.php");
            $this->alerta('danger', 'No tienes permiso para realizar esta acción.');
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

    function esAdmin()    { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'; }
    function esTecnico()  { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Tecnico'; }
    function esCliente()  { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Cliente'; }
    function obtenerIdUsuario()  { return $_SESSION['id_usuario'] ?? null; }
    function obtenerRolNombre()  { return $_SESSION['rol'] ?? null; }

    // =============================================
    // VALIDACIONES CON REGEX
    // =============================================

    function sanitizar($data) {
        if (is_array($data)) return array_map([$this, 'sanitizar'], $data);
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    function validarTelefono($telefono) {
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        return strlen($telefono) === 10;
    }

    /**
     * Contraseña: mínimo 8 caracteres, al menos 1 mayúscula,
     * 1 minúscula, 1 número y 1 carácter especial.
     */
    function validarContrasena($contrasena) {
        return preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            $contrasena
        ) === 1;
    }

    /**
     * RFC persona física: 4 letras + 6 dígitos fecha + 3 homoclave
     * Acepta también RFC de persona moral (3 letras + 6 + 3)
     */
    function validarRFC($rfc) {
        $rfc = strtoupper(trim($rfc));
        return preg_match(
            '/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/',
            $rfc
        ) === 1;
    }

    /**
     * CURP: 18 caracteres con formato oficial SAT
     */
    function validarCURP($curp) {
        $curp = strtoupper(trim($curp));
        return preg_match(
            '/^[A-Z]{1}[AEIOU]{1}[A-Z]{2}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM]{1}(AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}\d{1}$/',
            $curp
        ) === 1;
    }

    /**
     * Número de serie VIN: exactamente 17 caracteres alfanuméricos
     * (sin I, O, Q para evitar confusión)
     */
    function validarVIN($vin) {
        return preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', strtoupper($vin)) === 1;
    }

    /**
     * Placas México: formatos AAA-000, AA-000-AAA, etc.
     * Acepta entre 5 y 8 caracteres alfanuméricos con guion opcional.
     */
    function validarPlacas($placas) {
        return preg_match('/^[A-Z0-9\-]{5,8}$/', strtoupper($placas)) === 1;
    }

    // =============================================
    // SUBIDA DE IMÁGENES SEGURA
    // =============================================

    /**
     * Valida y sube una imagen verificando extensión Y MIME type real.
     * Nombre generado con uniqid para evitar colisiones.
     */
    function subirImagen($archivo, $carpeta = 'productos') {
        if (empty($archivo['name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $this->_validarImagen($archivo);

        $extension     = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        return $this->_moverImagen($archivo['tmp_name'], $carpeta, $nombreArchivo);
    }

    /**
     * Igual que subirImagen pero con nombre personalizado (ej: RFC del empleado).
     */
    function subirImagenConNombre($archivo, $carpeta, $nombreBase) {
        if (empty($archivo['name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $this->_validarImagen($archivo);

        $extension     = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $nombreBase    = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombreBase);
        $nombreArchivo = $nombreBase . '.' . $extension;
        return $this->_moverImagen($archivo['tmp_name'], $carpeta, $nombreArchivo);
    }

    /**
     * Valida extensión y MIME type real del archivo.
     * Lanza Exception si no es válido.
     */
    private function _validarImagen($archivo) {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $mimesPermitidos       = [
            'image/jpeg', 'image/jpg', 'image/png',
            'image/gif',  'image/webp',
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Extensión no permitida. Use: " . implode(', ', $extensionesPermitidas));
        }

        // Verificar MIME type real (no el que reporta el navegador)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeReal, $mimesPermitidos)) {
            throw new Exception("El archivo no es una imagen válida (MIME: {$mimeReal})");
        }

        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("La imagen excede el límite de 5MB");
        }
    }

    /**
     * Mueve el archivo al destino y retorna el nombre del archivo.
     */
    private function _moverImagen($tmpName, $carpeta, $nombreArchivo) {
        $dir  = __DIR__ . "/../uploads/{$carpeta}/";
        $ruta = $dir . $nombreArchivo;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (move_uploaded_file($tmpName, $ruta)) {
            chmod($ruta, 0644);
            return $nombreArchivo;
        }

        throw new Exception("Error al guardar la imagen en: {$dir}");
    }

    function eliminarImagen($nombreArchivo, $carpeta = 'productos') {
        if (empty($nombreArchivo)) return false;
        $ruta = __DIR__ . "/../uploads/{$carpeta}/" . $nombreArchivo;
        if (file_exists($ruta)) return unlink($ruta);
        return false;
    }

    // =============================================
    // UTILIDADES
    // =============================================
    function formatearPrecio($precio) {
        return '$' . number_format($precio, 2, '.', ',');
    }

    function formatearFecha($fecha, $formato = 'd/m/Y') {
        if (empty($fecha)) return '';
        return date($formato, strtotime($fecha));
    }

    function obtenerUsuarios() {
        $this->conectar();
        $stmt = $this->db->prepare(
            "SELECT u.id_usuario,
                    COALESCE(e.nombre, c.nombre, u.email) as nombre,
                    u.email
             FROM Usuario u
             LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
             LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
             WHERE u.estado_cuenta = 'activa'
             ORDER BY nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // =============================================
    // LOGIN / LOGOUT
    // =============================================
    public function login($correo, $contrasena) {
        $this->conectar();
        $pass_md5 = md5($contrasena);

        $sql = "SELECT u.*, r.id_rol, r.rol as nombre_rol
                FROM Usuario u
                INNER JOIN usuario_rol ur ON u.id_usuario = ur.id_usuario
                INNER JOIN Rol r          ON ur.id_rol = r.id_rol
                WHERE u.email = :correo
                AND u.contrasena_hash = :pass
                AND u.estado_cuenta = 'activa'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo, ':pass' => $pass_md5]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            session_regenerate_id(true);
            $_SESSION['validado']   = true;
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['email']      = $usuario['email'];
            $_SESSION['id_rol']     = $usuario['id_rol'];
            $_SESSION['rol']        = $usuario['nombre_rol'];

            $stmtN = $this->db->prepare(
                "SELECT COALESCE(e.nombre, c.nombre, '') as nombre,
                        COALESCE(e.apellido_paterno, c.apellido_paterno, '') as apellido_paterno
                 FROM Usuario u
                 LEFT JOIN Empleado e ON u.id_usuario = e.id_usuario
                 LEFT JOIN Cliente  c ON u.id_usuario = c.id_usuario
                 WHERE u.id_usuario = :id"
            );
            $stmtN->execute([':id' => $usuario['id_usuario']]);
            $perfil = $stmtN->fetch();

            $_SESSION['nombre']   = $perfil['nombre']          ?? '';
            $_SESSION['apellido'] = $perfil['apellido_paterno'] ?? '';

            unset($_SESSION['permisos']);
            $this->cargarPermisos();
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
