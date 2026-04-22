<?php
/**
 * Instalador Automático Workforce Tracker
 * Ejecuta este fichero desde el navegador para instalar la aplicación
 */

require_once __DIR__ . '/config.php';

// Verificar si ya está instalado
$lock_file = __DIR__ . '/installed.lock';
if (file_exists($lock_file)) {
    die('
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Instalación Completada</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; }
            .error-box { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 30px; border-radius: 8px; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>⚠️ La aplicación ya está instalada</h2>
            <p>El fichero installed.lock existe en el directorio raíz.</p>
            <p>Si desea reinstalar, elimine este fichero primero.</p>
            <p><a href="index.php">Ir a la aplicación</a></p>
        </div>
    </body>
    </html>
    ');
}

$log_messages = [];
$errors = 0;
$success = 0;

function log_step($message, $status) {
    global $log_messages, $errors, $success;
    $log_messages[] = [
        'message' => $message,
        'status' => $status
    ];
    
    if ($status) $success++;
    else $errors++;
    
    $log_line = "[" . date('Y-m-d H:i:s') . "] " . ($status ? '✅' : '❌') . " $message\n";
    file_put_contents(__DIR__ . '/logs/install.log', $log_line, FILE_APPEND);
}

// Crear directorio de logs si no existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Inicializar log
file_put_contents(__DIR__ . '/logs/install.log', "=== INICIO INSTALACIÓN " . date('Y-m-d H:i:s') . " ===\n");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar_instalacion'])) {
    // ==============================================
    // PASO 1: VERIFICACIÓN DEL ENTORNO
    // ==============================================
    
    // Versión PHP
    $php_version = phpversion();
    log_step("Versión PHP detectada: $php_version", version_compare($php_version, '7.4.0', '>='));
    
    // Extensión PDO
    log_step("Extensión PDO activada", extension_loaded('pdo'));
    
    // Extensión pdo_mysql
    log_step("Extensión pdo_mysql activada", extension_loaded('pdo_mysql'));
    
    // Extensión mysqli
    log_step("Extensión mysqli activada", extension_loaded('mysqli'));
    
    // Extensión gd
    log_step("Extensión gd activada", extension_loaded('gd'));
    
    // Carpeta perfiles con permisos de escritura
    $perfiles_dir = __DIR__ . '/assets/img/perfiles/';
    if (!is_dir($perfiles_dir)) {
        mkdir($perfiles_dir, 0755, true);
    }
    log_step("Carpeta perfiles existente y escribible", is_writable($perfiles_dir));
    
    // ==============================================
    // PASO 2: CREACIÓN BASE DE DATOS
    // ==============================================
    
    try {
        // Conectar sin base de datos primero
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Crear base de datos si no existe
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        log_step("Base de datos creada correctamente", true);
        
        // Seleccionar base de datos
        $pdo->exec("USE " . DB_NAME);
        
        // Ejecutar database.sql
        $sql_content = file_get_contents(__DIR__ . '/database.sql');
        $queries = array_filter(array_map('trim', explode(';', $sql_content)));
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        log_step("Tablas creadas correctamente desde database.sql", true);
        
        // ==============================================
        // PASO 3: INSERTAR DATOS DE PRUEBA
        // ==============================================
        
        // Insertar departamentos
        $departamentos = [
            ['Dirección', 'Dirección general de la empresa', '#e74c3c'],
            ['Desarrollo', 'Departamento de desarrollo de software', '#3498db'],
            ['Contabilidad', 'Departamento de contabilidad y finanzas', '#2ecc71'],
            ['RRHH', 'Departamento de Recursos Humanos', '#f39c12']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO departamentos (nombre, descripcion, color) VALUES (?, ?, ?)");
        foreach ($departamentos as $dept) {
            $stmt->execute($dept);
        }
        log_step("Departamentos insertados correctamente", true);
        
        // Contraseñas hasheadas
        $password_empleado = password_hash('Empleat2025!', PASSWORD_DEFAULT);
        $password_jefe = password_hash('Cap2025!', PASSWORD_DEFAULT);
        
        // Insertar jefes de departamento
        $jefes = [
            ['jefe.direccion@empresa.com', $password_jefe, 'Juan', 'García López', 1, 'jefe_departamento', 'presencial'],
            ['jefe.desarrollo@empresa.com', $password_jefe, 'María', 'Martínez Sánchez', 2, 'jefe_departamento', 'hibrido'],
            ['jefe.contabilidad@empresa.com', $password_jefe, 'Pedro', 'Ruiz Fernández', 3, 'jefe_departamento', 'presencial'],
            ['jefe.rrhh@empresa.com', $password_jefe, 'Ana', 'Gómez Pérez', 4, 'jefe_departamento', 'teletrabajo']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO empleados (email, password, nombre, apellidos, departamento_id, rol, modalidad) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($jefes as $jefe) {
            $stmt->execute($jefe);
        }
        log_step("Jefes de departamento creados correctamente", true);
        
        // Insertar empleados estándar
        $empleados = [
            ['empleado.direccion@empresa.com', $password_empleado, 'Carlos', 'Díaz Moreno', 1, 'empleado', 'presencial'],
            ['empleado.desarrollo@empresa.com', $password_empleado, 'Laura', 'Jiménez Castro', 2, 'empleado', 'hibrido'],
            ['empleado.contabilidad@empresa.com', $password_empleado, 'David', 'Ortega Ruiz', 3, 'empleado', 'presencial'],
            ['empleado.rrhh@empresa.com', $password_empleado, 'Sofía', 'Navarro Torres', 4, 'empleado', 'teletrabajo']
        ];
        
        foreach ($empleados as $emp) {
            $stmt->execute($emp);
        }
        log_step("Empleados de prueba creados correctamente", true);
        
        // ==============================================
        // PASO 4: CREAR FICHERO .HTACCESS
        // ==============================================
        
        $htaccess_content = '
# Workforce Tracker .htaccess
RewriteEngine On

# Redirección a index.php si el fichero no existe
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

# Bloquear acceso directo a includes
RedirectMatch 403 ^/includes/.*$

# Bloquear acceso directo a config.php
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

# Bloquear acceso a logs
RedirectMatch 403 ^/logs/.*$

# Cabeceras de seguridad
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
';
        
        file_put_contents(__DIR__ . '/.htaccess', trim($htaccess_content));
        log_step("Fichero .htaccess creado correctamente", true);
        
        // ==============================================
        // PASO 5: FICHERO DE BLOQUEO POST-INSTALACIÓN
        // ==============================================
        
        file_put_contents($lock_file, date('Y-m-d H:i:s'));
        log_step("Fichero de bloqueo installed.lock creado", true);
        
    } catch (PDOException $e) {
        log_step("Error en base de datos: " . $e->getMessage(), false);
    } catch (Exception $e) {
        log_step("Error general: " . $e->getMessage(), false);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Workforce Tracker</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .step {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 8px 0;
            border-radius: 8px;
        }
        .step.success { background: #d4edda; border-left: 4px solid #28a745; }
        .step.error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }
        .step.success .status-icon { background: #28a745; }
        .step.error .status-icon { background: #dc3545; }
        .btn {
            display: inline-block;
            background: #3498db;
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            width: 100%;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials-box {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Instalador Workforce Tracker</h1>
            <p>Sistema de control de presencia empresarial</p>
        </div>
        <div class="content">
            
            <?php if (empty($log_messages)): ?>
            
            <p style="margin-bottom: 20px; font-size: 16px; line-height: 1.6;">
                Bienvenido al instalador automático. Este proceso verificará los requisitos del sistema,
                creará la base de datos, insertará datos de prueba y configurará la aplicación automáticamente.
            </p>
            
            <form method="POST">
                <button type="submit" name="iniciar_instalacion" class="btn">
                    ▶️ Iniciar instalación
                </button>
            </form>
            
            <?php else: ?>
            
            <h3 style="margin-bottom: 20px;">Resultado de la instalación</h3>
            
            <?php foreach ($log_messages as $log): ?>
            <div class="step <?= $log['status'] ? 'success' : 'error' ?>">
                <div class="status-icon">
                    <?= $log['status'] ? '✓' : '✗' ?>
                </div>
                <div><?= e($log['message']) ?></div>
            </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px; padding: 15px; border-radius: 8px; background: <?= $errors == 0 ? '#d4edda' : '#f8d7da' ?>;">
                <strong>
                    <?= $errors == 0 ? '✅ Instalación completada con éxito!' : '❌ Se encontraron ' . $errors . ' errores' ?>
                </strong>
            </div>
            
            <?php if ($errors == 0): ?>
            
            <div class="credentials-box">
                <h4>📋 Credenciales de prueba:</h4>
                <p><strong>Empleados estándar:</strong> Contraseña: <code>Empleat2025!</code></p>
                <ul style="margin: 10px 0 20px 30px;">
                    <li>empleado.desarrollo@empresa.com</li>
                    <li>empleado.contabilidad@empresa.com</li>
                    <li>empleado.rrhh@empresa.com</li>
                    <li>empleado.direccion@empresa.com</li>
                </ul>
                <p><strong>Jefes de departamento:</strong> Contraseña: <code>Cap2025!</code></p>
                <ul style="margin: 10px 0 0 30px;">
                    <li>jefe.desarrollo@empresa.com</li>
                    <li>jefe.contabilidad@empresa.com</li>
                    <li>jefe.rrhh@empresa.com</li>
                    <li>jefe.direccion@empresa.com</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ IMPORTANTE:</strong> Por seguridad, elimine o proteja con contraseña el fichero <code>install.php</code> antes de poner la aplicación en producción.
                No deje este fichero accesible públicamente.
            </div>
            
            <a href="index.php" class="btn btn-success">
                🚀 Ir a la aplicación
            </a>
            
            <?php else: ?>
            
            <p style="margin-top: 20px;">
                Corrige los errores anteriores y vuelve a ejecutar el instalador.
            </p>
            
            <?php endif; ?>
            
            <?php endif; ?>
            
        </div>
    </div>
</body>
</html>
