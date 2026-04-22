<?php
/**
 * Página de fichaje
 * Funcionalidad principal de entrada y salida
 * Detección VPN y fichaje manual nocturno
 */

require_once __DIR__ . '/../includes/functions.php';
require_auth();

$user = current_user();
$vpn_connected = isVPNConnected();
$estado_fichaje = get_today_checkin_status($user['id']);
$hoy = date('Y-m-d');

// Procesar fichaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fichar'])) {
    
    // Verificar VPN salvo que sea teletrabajador
    if (!$vpn_connected && $user['modalidad'] !== 'teletrabajo') {
        set_flash_message('Debes estar conectado a la VPN corporativa para poder fichar', 'danger');
        redirect('fichar.php');
    }
    
    $hora_actual = date('H:i:s');
    $ip = getUserIP();
    
    $fichaje_actual = db_fetch("SELECT * FROM fichajes WHERE empleado_id = ? AND fecha = ?", [
        $user['id'], $hoy
    ]);
    
    if (!$fichaje_actual) {
        // Registrar entrada
        db_insert('fichajes', [
            'empleado_id' => $user['id'],
            'fecha' => $hoy,
            'hora_entrada' => $hora_actual,
            'ip_entrada' => $ip,
            'vpn_entrada' => $vpn_connected ? 1 : 0,
            'es_manual' => 0,
            'estado' => 'aprobado'
        ]);
        
        registrar_log('fichaje_entrada', 'Entrada registrada correctamente');
        set_flash_message('Entrada registrada correctamente. ¡Buen día de trabajo!', 'success');
        
    } elseif (empty($fichaje_actual['hora_salida'])) {
        // Registrar salida
        db_update('fichajes', [
            'hora_salida' => $hora_actual,
            'ip_salida' => $ip,
            'vpn_salida' => $vpn_connected ? 1 : 0
        ], 'id = ?', [$fichaje_actual['id']]);
        
        registrar_log('fichaje_salida', 'Salida registrada correctamente');
        set_flash_message('Salida registrada correctamente. ¡Hasta mañana!', 'success');
    }
    
    redirect('fichar.php');
}

// Procesar fichaje manual nocturno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fichaje_manual'])) {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $tipo = $_POST['tipo'];
    $comentario = trim($_POST['comentario']);
    
    if (empty($fecha) || empty($hora) || empty($tipo) || empty($comentario)) {
        set_flash_message('Todos los campos son obligatorios para el fichaje manual', 'danger');
        redirect('fichar.php');
    }
    
    $fichaje = db_fetch("SELECT * FROM fichajes WHERE empleado_id = ? AND fecha = ?", [
        $user['id'], $fecha
    ]);
    
    if ($tipo == 'entrada') {
        if (!$fichaje) {
            db_insert('fichajes', [
                'empleado_id' => $user['id'],
                'fecha' => $fecha,
                'hora_entrada' => $hora,
                'ip_entrada' => getUserIP(),
                'vpn_entrada' => 0,
                'es_manual' => 1,
                'comentario_manual' => $comentario,
                'estado' => 'pendiente'
            ]);
        } else {
            db_update('fichajes', [
                'hora_entrada' => $hora,
                'es_manual' => 1,
                'comentario_manual' => $comentario,
                'estado' => 'pendiente'
            ], 'id = ?', [$fichaje['id']]);
        }
    } else {
        if ($fichaje) {
            db_update('fichajes', [
                'hora_salida' => $hora,
                'es_manual' => 1,
                'comentario_manual' => $comentario,
                'estado' => 'pendiente'
            ], 'id = ?', [$fichaje['id']]);
        }
    }
    
    registrar_log('fichaje_manual', "Fichaje manual registrado para $fecha $hora");
    set_flash_message('Fichaje manual registrado correctamente. Queda pendiente de aprobación por RRHH.', 'success');
    redirect('fichar.php');
}

// Obtener fichajes de hoy
$fichajes_hoy = db_fetch_all("
    SELECT * FROM fichajes 
    WHERE empleado_id = ? 
    ORDER BY fecha DESC 
    LIMIT 10
", [$user['id']]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fichar - Workforce Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- BARRA LATERAL -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>📋 Workforce</h2>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?= get_initials($user['nombre'], $user['apellidos']) ?>
                </div>
                <div class="user-name"><?= e($user['nombre']) ?> <?= e($user['apellidos']) ?></div>
                <div class="user-role">Empleado</div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item">📊 Inicio</a>
                <a href="fichar.php" class="menu-item active">⏱️ Fichar</a>
                <a href="perfil.php" class="menu-item">👤 Mi Perfil</a>
                <a href="horario.php" class="menu-item">📅 Horario</a>
                <a href="proyectos.php" class="menu-item">📁 Proyectos</a>
                <a href="horas-extras.php" class="menu-item">⏰ Horas Extras</a>
                <a href="nominas.php" class="menu-item">📑 Nóminas</a>
                <a href="notificaciones.php" class="menu-item">🔔 Notificaciones</a>
                <a href="contacto.php" class="menu-item">📞 Directorio</a>
                <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 0;">
                <a href="../auth/logout.php" class="menu-item">🚪 Cerrar sesión</a>
            </nav>
        </aside>
        
        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <div class="page-header">
                <h1>Control de presencia</h1>
                <div class="vpn-status <?= $vpn_connected ? 'connected' : 'disconnected' ?>">
                    <span class="vpn-dot"></span>
                    VPN <?= $vpn_connected ? 'Conectada' : 'Desconectada' ?>
                </div>
            </div>
            
            <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>
            
            <!-- RELOJ TIEMPO REAL -->
            <div class="card text-center">
                <div id="clock" class="clock"><?= date('H:i:s') ?></div>
                <p style="font-size: 18px; color: var(--gray);"><?= format_date(date('Y-m-d'), 'l, d \d\e F \d\e Y') ?></p>
            </div>
            
            <!-- BOTÓN PRINCIPAL DE FICHAJE -->
            <div class="card text-center">
                <h3 style="margin-bottom: 20px;">Estado actual: 
                    <span class="badge <?= $estado_fichaje == 'trabajando' ? 'badge-success' : 'badge-info' ?>">
                        <?php
                        switch($estado_fichaje) {
                            case 'sin_fichar': echo 'Sin fichar'; break;
                            case 'trabajando': echo 'En jornada laboral'; break;
                            case 'fichado_completo': echo 'Jornada finalizada'; break;
                        }
                        ?>
                    </span>
                </h3>
                
                <?php if ($vpn_connected || $user['modalidad'] === 'teletrabajo'): ?>
                <form method="POST">
                    <button type="submit" name="fichar" class="checkin-button <?= $estado_fichaje == 'trabajando' ? 'check-out' : 'check-in' ?>">
                        <span style="font-size: 48px; margin-bottom: 8px;">
                            <?= $estado_fichaje == 'trabajando' ? '🚪' : '✅' ?>
                        </span>
                        <?= $estado_fichaje == 'trabajando' ? 'Registrar salida' : 'Registrar entrada' ?>
                    </button>
                </form>
                
                <?php if ($user['modalidad'] === 'teletrabajo'): ?>
                <p style="margin-top: 20px; color: var(--gray);">
                    ✅ Modalidad teletrabajo activada - No requiere VPN
                </p>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="alert alert-danger">
                    <strong>⚠️ No puedes fichar en este momento</strong><br>
                    Debes conectarte a la red VPN corporativa para registrar tu entrada o salida.
                </div>
                <?php endif; ?>
            </div>
            
            <!-- FICHAJE MANUAL NOCTURNO -->
            <div class="card">
                <div class="card-header">
                    <h3>📝 Registro manual (Desfichaje nocturno)</h3>
                </div>
                <p style="margin-bottom: 20px;">Si olvidaste registrar tu salida o entrada, puedes hacerlo manualmente. Este registro quedará marcado como pendiente de revisión.</p>
                
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" name="fecha" class="form-control" required max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label>Hora</label>
                            <input type="time" name="hora" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tipo de registro</label>
                            <select name="tipo" class="form-control" required>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Motivo del registro tardío (obligatorio)</label>
                        <textarea name="comentario" class="form-control" rows="3" required placeholder="Explica el motivo por el que no registraste el fichaje en su momento..."></textarea>
                    </div>
                    
                    <button type="submit" name="fichaje_manual" class="btn btn-warning">
                        Registrar fichaje manual
                    </button>
                </form>
            </div>
            
            <!-- HISTÓRICO RECIENTE -->
            <div class="card">
                <div class="card-header">
                    <h3>Historial de fichajes</h3>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Horas</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($fichajes_hoy as $f): ?>
                        <tr>
                            <td><?= format_date($f['fecha']) ?></td>
                            <td><?= format_time($f['hora_entrada']) ?></td>
                            <td><?= $f['hora_salida'] ? format_time($f['hora_salida']) : '-' ?></td>
                            <td><?= ($f['hora_entrada'] && $f['hora_salida']) ? format_hours_decimal(calculate_hours_diff($f['hora_entrada'], $f['hora_salida'])) : '-' ?></td>
                            <td>
                                <?php if($f['es_manual']): ?>
                                <span class="badge badge-warning">Manual</span>
                                <?php else: ?>
                                <span class="badge badge-success">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                switch($f['estado']) {
                                    case 'pendiente': echo '<span class="badge badge-warning">Pendiente</span>'; break;
                                    case 'aprobado': echo '<span class="badge badge-success">Aprobado</span>'; break;
                                    case 'rechazado': echo '<span class="badge badge-danger">Rechazado</span>'; break;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </main>
    </div>
    
    <script>
        // Actualizar reloj en tiempo real
        function updateClock() {
            const now = new Date();
            const clock = document.getElementById('clock');
            clock.textContent = now.toLocaleTimeString('es-ES', { hour12: false });
        }
        setInterval(updateClock, 1000);
    </script>
</body>
</html>