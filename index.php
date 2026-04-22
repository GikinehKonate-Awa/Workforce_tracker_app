<?php
/**
 * Página principal - Redirecciona al login o al dashboard correspondiente
 */

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    if (is_department_head()) {
        redirect(APP_URL . '/jefe/');
    } else {
        redirect(APP_URL . '/empleado/');
    }
} else {
    redirect(APP_URL . '/auth/login.php');
}
?>