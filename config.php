<?php
// CONFIGURACIÓN GENERAL DE LA APLICACIÓN
define('APP_NAME', 'Workforce Tracker');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Workforce_tracker_app');

// CONFIGURACIÓN BASE DE DATOS
define('DB_HOST', 'localhost');
define('DB_NAME', 'workforce_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// CONFIGURACIÓN VPN CORPORATIVA
// Rango de IPs que pertenecen a la VPN corporativa
define('VPN_IP_RANGE', [
    '192.168.1.0/24',
    '10.0.0.0/8',
    '172.16.0.0/12',
    '127.0.0.1' // Para desarrollo local
]);

// HORARIO LABORAL ESTÁNDAR
define('WORKDAY_START', '08:00');
define('WORKDAY_END', '17:00');
define('LUNCH_BREAK_START', '13:00');
define('LUNCH_BREAK_END', '14:00');
define('WORK_HOURS_PER_DAY', 8);

// CONFIGURACIÓN DE ARCHIVOS
define('PROFILE_IMAGES_PATH', __DIR__ . '/assets/img/perfiles/');
define('MAX_PROFILE_IMAGE_SIZE', 2097152); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// CONFIGURACIÓN DE SESIONES
define('SESSION_LIFETIME', 86400); // 24 horas
define('SESSION_SECURE', false); // Cambiar a true en producción con HTTPS
define('SESSION_HTTPONLY', true);

// CONFIGURACIÓN DE ZONA HORARIA
date_default_timezone_set('Europe/Madrid');
setlocale(LC_TIME, 'es_ES.UTF-8');

// CONFIGURACIÓN DE ERRORES
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>