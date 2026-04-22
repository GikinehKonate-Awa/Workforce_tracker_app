<?php
/**
 * Sistema de Autenticación con Sesiones PHP
 * Sin tokens JWT ni librerías externas
 */

require_once __DIR__ . '/db.php';

// Iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) {
    // Configuración segura de cookies de sesión
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', SESSION_HTTPONLY);
    ini_set('session.cookie_secure', SESSION_SECURE);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => 'Strict'
    ]);
    
    session_start();
    
    // Regenerar ID de sesión periódicamente para prevenir fijación
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Cada 30 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Verificar si el usuario está autenticado
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Requerir autenticación para acceder a la página
function require_auth() {
    if (!is_logged_in()) {
        header("Location: " . APP_URL . "/auth/login.php");
        exit();
    }
}

// Requerir rol específico
function require_role($required_role) {
    if (!is_logged_in()) {
        header("Location: " . APP_URL . "/auth/login.php");
        exit();
    }
    
    if ($_SESSION['user_role'] !== $required_role && $_SESSION['user_role'] !== 'admin') {
        header("Location: " . APP_URL . "/empleado/");
        exit();
    }
}

// Iniciar sesión de usuario
function login_user($email, $password) {
    $user = db_fetch("SELECT id, password, nombre, apellidos, rol, departamento_id, modalidad, foto_perfil FROM empleados WHERE email = ? AND activo = 1", [$email]);
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ];
    }
    
    if (!password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ];
    }
    
    // Regenerar ID de sesión al iniciar sesión
    session_regenerate_id(true);
    
    // Almacenar datos del usuario en sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $email;
    $_SESSION['user_nombre'] = $user['nombre'];
    $_SESSION['user_apellidos'] = $user['apellidos'];
    $_SESSION['user_role'] = $user['rol'];
    $_SESSION['user_departamento'] = $user['departamento_id'];
    $_SESSION['user_modalidad'] = $user['modalidad'];
    $_SESSION['user_foto'] = $user['foto_perfil'];
    $_SESSION['last_regeneration'] = time();
    
    // Actualizar último acceso
    db_update('empleados', [
        'ultimo_acceso' => date('Y-m-d H:i:s')
    ], 'id = ?', [$user['id']]);
    
    registrar_log('login', 'Inicio de sesión correcto');
    
    return [
        'success' => true,
        'message' => 'Bienvenido ' . $user['nombre'],
        'role' => $user['rol']
    ];
}

// Cerrar sesión
function logout_user() {
    registrar_log('logout', 'Cierre de sesión');
    
    // Destruir todas las variables de sesión
    $_SESSION = [];
    
    // Eliminar cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    header("Location: " . APP_URL . "/auth/login.php");
    exit();
}

// Obtener datos del usuario actual
function current_user() {
    if (!is_logged_in()) return null;
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'nombre' => $_SESSION['user_nombre'],
        'apellidos' => $_SESSION['user_apellidos'],
        'rol' => $_SESSION['user_role'],
        'departamento_id' => $_SESSION['user_departamento'],
        'modalidad' => $_SESSION['user_modalidad'],
        'foto' => $_SESSION['user_foto']
    ];
}

// Registrar log de acción
function registrar_log($accion, $descripcion = '') {
    if (!isset($_SESSION['user_id'])) return;
    
    db_insert('logs_sistema', [
        'empleado_id' => $_SESSION['user_id'],
        'accion' => $accion,
        'descripcion' => $descripcion,
        'ip' => getUserIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

// Obtener IP real del usuario
function getUserIP() {
    $ip_keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Verificar si el usuario está conectado desde la VPN corporativa
function isVPNConnected() {
    $user_ip = getUserIP();
    
    // Si es teletrabajador permitir acceso sin VPN
    if (isset($_SESSION['user_modalidad']) && $_SESSION['user_modalidad'] === 'teletrabajo') {
        return true;
    }
    
    foreach (VPN_IP_RANGE as $range) {
        if (ip_in_range($user_ip, $range)) {
            return true;
        }
    }
    
    return false;
}

// Comprobar si una IP está dentro de un rango CIDR
function ip_in_range($ip, $range) {
    if (strpos($range, '/') === false) {
        return $ip === $range;
    }
    
    list($subnet, $bits) = explode('/', $range, 2);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask;
    
    return ($ip & $mask) == $subnet;
}
?>