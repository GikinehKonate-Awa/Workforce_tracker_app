<?php
/**
 * Funciones generales de la aplicación
 * Helper functions para uso en toda la aplicación
 */

require_once __DIR__ . '/auth.php';

// Formatear fecha en español
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Formatear hora
function format_time($time, $format = 'H:i') {
    if (empty($time)) return '';
    return date($format, strtotime($time));
}

// Calcular diferencia de horas entre dos horas
function calculate_hours_diff($start_time, $end_time) {
    if (empty($start_time) || empty($end_time)) return 0;
    
    $start = strtotime($start_time);
    $end = strtotime($end_time);
    
    if ($end < $start) {
        $end += 86400; // Añadir un día si es medianoche
    }
    
    $diff = $end - $start;
    return round($diff / 3600, 2);
}

// Formatear horas con formato HH:MM
function format_hours_decimal($hours) {
    $h = floor($hours);
    $m = round(($hours - $h) * 60);
    return sprintf("%02d:%02d", $h, $m);
}

// Obtener nombre del día de la semana
function get_day_name($day_number) {
    $days = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    return $days[$day_number] ?? 'Desconocido';
}

// Obtener nombre del mes
function get_month_name($month_number) {
    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    return $months[$month_number] ?? 'Desconocido';
}

// Escapear salida HTML para prevenir XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Redireccionar a otra página
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Mostrar mensaje flash
function set_flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Obtener y eliminar mensaje flash
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

// Validar email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Obtener estado de fichaje del día
function get_today_checkin_status($empleado_id) {
    $hoy = date('Y-m-d');
    
    $fichaje = db_fetch("SELECT * FROM fichajes WHERE empleado_id = ? AND fecha = ?", [
        $empleado_id, $hoy
    ]);
    
    if (!$fichaje) {
        return 'sin_fichar';
    }
    
    if (!empty($fichaje['hora_entrada']) && empty($fichaje['hora_salida'])) {
        return 'trabajando';
    }
    
    if (!empty($fichaje['hora_entrada']) && !empty($fichaje['hora_salida'])) {
        return 'fichado_completo';
    }
    
    return 'sin_fichar';
}

// Obtener horas trabajadas hoy
function get_today_worked_hours($empleado_id) {
    $hoy = date('Y-m-d');
    
    $fichaje = db_fetch("SELECT hora_entrada, hora_salida FROM fichajes WHERE empleado_id = ? AND fecha = ?", [
        $empleado_id, $hoy
    ]);
    
    if (!$fichaje) return 0;
    
    $entrada = $fichaje['hora_entrada'];
    $salida = $fichaje['hora_salida'] ?? date('H:i:s');
    
    return calculate_hours_diff($entrada, $salida);
}

// Obtener conteo de notificaciones no leídas
function get_unread_notifications_count() {
    if (!is_logged_in()) return 0;
    
    return db_fetch("SELECT COUNT(*) as total FROM notificaciones WHERE empleado_id = ? AND leida = 0", [
        $_SESSION['user_id']
    ])['total'] ?? 0;
}

// Verificar si el usuario es jefe de departamento
function is_department_head() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'jefe_departamento';
}

// Obtener empleados de un departamento
function get_department_employees($departamento_id) {
    return db_fetch_all("SELECT id, nombre, apellidos, email, telefono, modalidad, foto_perfil FROM empleados WHERE departamento_id = ? AND activo = 1 ORDER BY apellidos, nombre", [
        $departamento_id
    ]);
}

// Generar color aleatorio para avatar
function generate_avatar_color($name) {
    $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];
    $index = crc32($name) % count($colors);
    return $colors[$index];
}

// Obtener iniciales del nombre
function get_initials($nombre, $apellidos) {
    return strtoupper(substr($nombre, 0, 1) . substr($apellidos, 0, 1));
}

// Convertir tamaño de bytes a formato legible
function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
?>