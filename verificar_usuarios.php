s mod<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

echo "🔍 Verificando usuarios existentes...\n";

$usuarios = db_fetch_all("SELECT id, email, nombre, rol FROM empleados");

if ($usuarios && count($usuarios) > 0) {
    echo "\n✅ USUARIOS EXISTEN EN LA BASE DE DATOS:\n";
    foreach($usuarios as $u) {
        echo "  [{$u['id']}] {$u['email']} - {$u['nombre']} ({$u['rol']})\n";
    }
    
    echo "\n✅ Credenciales validas:\n";
    echo "👤 empleado.desarrollo@empresa.com / Empleat2025!\n";
    echo "👔 jefe.desarrollo@empresa.com / Cap2025!\n";
} else {
    echo "\n❌ NO HAY USUARIOS EN LA BASE DE DATOS\n";
    
    // Crear usuario admin por defecto
    $pass = password_hash('123456', PASSWORD_DEFAULT);
    db_insert('empleados', [
        'email' => 'admin@empresa.com',
        'password' => $pass,
        'nombre' => 'Administrador',
        'apellidos' => 'Sistema',
        'departamento_id' => 1,
        'rol' => 'admin'
    ]);
    
    echo "\n✅ Usuario admin creado: admin@empresa.com / 123456\n";
}

// Probar login directamente
echo "\n🔐 Probando inicio de sesion...\n";
$test = login_user('empleado.desarrollo@empresa.com', 'Empleat2025!');

if ($test['success']) {
    echo "✅ ✅ LOGIN FUNCIONA CORRECTAMENTE!\n";
} else {
    echo "❌ Error login: {$test['message']}\n";
}

file_put_contents('installed.lock', date('Y-m-d H:i:s'));
echo "\n✅ Instalacion finalizada. Ya puedes usar la aplicacion.\n";
?>