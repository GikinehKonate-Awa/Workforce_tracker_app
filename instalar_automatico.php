<?php
/**
 * Instalador Automatico por CLI
 * Ejecuta toda la instalacion sin necesidad de navegador
 */

require_once __DIR__ . '/config.php';

echo "🔧 INICIANDO INSTALACION AUTOMATICA WORFORCE TRACKER\n";
echo "==================================================\n\n";

// Crear directorio logs si no existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
    echo "✅ Directorio logs creado\n";
}

try {
    // Conectar a MySQL
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Conexion MySQL establecida\n";

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de datos " . DB_NAME . " creada\n";

    $pdo->exec("USE " . DB_NAME);
    echo "✅ Base de datos seleccionada\n";

    // Ejecutar database.sql
    $sql_content = file_get_contents(__DIR__ . '/database.sql');
    $queries = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    echo "✅ Todas las tablas creadas correctamente\n";

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
    echo "✅ Departamentos insertados\n";

    // Contraseñas hasheadas
    $password_empleado = password_hash('Empleat2025!', PASSWORD_DEFAULT);
    $password_jefe = password_hash('Cap2025!', PASSWORD_DEFAULT);

    // Insertar jefes
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
    echo "✅ Jefes de departamento creados\n";

    // Insertar empleados
    $empleados = [
        ['empleado.direccion@empresa.com', $password_empleado, 'Carlos', 'Díaz Moreno', 1, 'empleado', 'presencial'],
        ['empleado.desarrollo@empresa.com', $password_empleado, 'Laura', 'Jiménez Castro', 2, 'empleado', 'hibrido'],
        ['empleado.contabilidad@empresa.com', $password_empleado, 'David', 'Ortega Ruiz', 3, 'empleado', 'presencial'],
        ['empleado.rrhh@empresa.com', $password_empleado, 'Sofía', 'Navarro Torres', 4, 'empleado', 'teletrabajo']
    ];
    
    foreach ($empleados as $emp) {
        $stmt->execute($emp);
    }
    echo "✅ Empleados de prueba creados\n";

    // Crear archivo de bloqueo
    file_put_contents(__DIR__ . '/installed.lock', date('Y-m-d H:i:s'));
    echo "✅ Archivo installed.lock creado\n";

    echo "\n🎉 INSTALACION FINALIZADA CORRECTAMENTE!\n";
    echo "\n✅ CREDENCIALES LISTAS:\n";
    echo "👤 Empleado: empleado.desarrollo@empresa.com / Empleat2025!\n";
    echo "👔 Jefe: jefe.desarrollo@empresa.com / Cap2025!\n";
    echo "\n🚀 Ya puedes iniciar sesion en la aplicacion\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}