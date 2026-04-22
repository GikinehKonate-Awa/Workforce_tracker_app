<?php
/**
 * Dashboard Empleado
 * Página principal del área de empleado
 */

require_once __DIR__ . '/../includes/functions.php';
require_auth();

$user = current_user();
$estado_fichaje = get_today_checkin_status($user['id']);
$horas_hoy = get_today_worked_hours($user['id']);
$notificaciones_pendientes = get_unread_notifications_count();

// Obtener fichajes de la última semana
$fichajes_semana = db_fetch_all("
    SELECT fecha, hora_entrada, hora_salida, es_manual, estado
    FROM fichajes 
    WHERE empleado_id = ? 
    AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY fecha DESC
", [$user['id']]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Workforce Tracker</title>
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
                <a href="index.php" class="menu-item active">📊 Inicio</a>
                <a href="fichar.php" class="menu-item">⏱️ Fichar</a>
                <a href="perfil.php" class="menu-item">👤 Mi Perfil</a>
                <a href="horario.php" class="menu-item">📅 Horario</a>
                <a href="proyectos.php" class="menu-item">📁 Proyectos</a>
                <a href="horas-extras.php" class="menu-item">⏰ Horas Extras</a>
                <a href="nominas.php" class="menu-item">📑 Nóminas</a>
                <a href="notificaciones.php" class="menu-item">🔔 Notificaciones <?= $notificaciones_pendientes > 0 ? "($notificaciones_pendientes)" : '' ?></a>
                <a href="contacto.php" class="menu-item">📞 Directorio</a>
                <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 0;">
                <a href="../auth/logout.php" class="menu-item">🚪 Cerrar sesión</a>
            </nav>
        </aside>
        
        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">
            <div class="page-header">
                <h1>Bienvenido, <?= e($user['nombre']) ?></h1>
                <div class="vpn-status <?= isVPNConnected() ? 'connected' : 'disconnected' ?>">
                    <span class="vpn-dot"></span>
                    VPN <?= isVPNConnected() ? 'Conectada' : 'Desconectada' ?>
                </div>
            </div>
            
            <?php $flash = get_flash_message(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>
            
            <!-- ESTADO ACTUAL -->
            <div class="stats-grid">
                <div class="stat-card <?= $estado_fichaje == 'trabajando' ? 'success' : '' ?>">
                    <div class="stat-value">
                        <?php
                        switch($estado_fichaje) {
                            case 'sin_fichar': echo '🔒 Sin fichar'; break;
                            case 'trabajando': echo '✅ Trabajando'; break;
                            case 'fichado_completo': echo '✅ Completado'; break;
                        }
                        ?>
                    </div>
                    <div class="stat-label">Estado actual</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= format_hours_decimal($horas_hoy) ?></div>
                    <div class="stat-label">Horas trabajadas hoy</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= date('d/m/Y') ?></div>
                    <div class="stat-label">Fecha actual</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?= $notificaciones_pendientes ?></div>
                    <div class="stat-label">Notificaciones pendientes</div>
                </div>
            </div>
            
            <!-- ACCESO RÁPIDO -->
            <div class="card">
                <div class="card-header">
                    <h3>Acceso rápido</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                    <a href="fichar.php" class="btn btn-success btn-lg">⏱️ Fichar ahora</a>
                    <a href="horario.php" class="btn btn-primary btn-lg">📅 Ver horario</a>
                    <a href="notificaciones.php" class="btn btn-primary btn-lg">🔔 Ver notificaciones</a>
                </div>
            </div>
            
            <!-- FICHAJES SEMANALES -->
            <div class="card">
                <div class="card-header">
                    <h3>Últimos fichajes</h3>
                    <a href="fichar.php" class="btn btn-primary">Ver todos</a>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Horas</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($fichajes_semana as $f): ?>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </main>
    </div>
</body>
</html>