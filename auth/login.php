<?php
/**
 * Página de inicio de sesión
 */

require_once __DIR__ . '/../includes/functions.php';

// Si ya está autenticado redirigir al dashboard
if (is_logged_in()) {
    if (is_department_head()) {
        redirect(APP_URL . '/jefe/');
    } else {
        redirect(APP_URL . '/empleado/');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor introduce email y contraseña';
    } else {
        $result = login_user($email, $password);
        
        if ($result['success']) {
            set_flash_message($result['message'], 'success');
            
            if ($result['role'] === 'jefe_departamento' || $result['role'] === 'admin') {
                redirect(APP_URL . '/jefe/');
            } else {
                redirect(APP_URL . '/empleado/');
            }
        } else {
            $error = $result['message'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Workforce Tracker</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #7f8c8d;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        .btn {
            width: 100%;
            padding: 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info-box {
            margin-top: 30px;
            padding: 20px;
            background: #e8f4fd;
            border-radius: 8px;
            font-size: 14px;
            color: #31708f;
        }
        .info-box code {
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <h1>🔐 Workforce Tracker</h1>
            <p>Control de presencia empresarial</p>
        </div>
        
        <?php if (!empty($error)): ?>
        <div class="error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <?php $flash = get_flash_message(); if ($flash): ?>
        <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" name="login" class="btn">
                Iniciar sesión
            </button>
        </form>
        
        <div class="info-box">
            <strong>Credenciales de prueba:</strong><br><br>
            👤 <strong>Empleado:</strong> empleado.desarrollo@empresa.com / <code>Empleat2025!</code><br>
            👔 <strong>Jefe:</strong> jefe.desarrollo@empresa.com / <code>Cap2025!</code>
        </div>
    </div>
</body>
</html>