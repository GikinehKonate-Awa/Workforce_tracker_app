# Workforce Tracker - Sistema de Control de Presencia Empresarial

Aplicación web completa para el control de fichaje y presencia de empleados, desarrollada 100% con tecnologías nativas sin frameworks externos.

## ✅ Características principales

✅ Detección automática de conexión VPN corporativa
✅ Excepciones para empleados en modalidad teletrabajo
✅ Sistema de fichaje manual nocturno con comentario obligatorio
✅ Roles diferenciados: Empleado / Jefe de Departamento / RRHH
✅ Base de datos MySQL con PDO y consultas preparadas
✅ Autenticación segura con sesiones PHP
✅ Diseño responsive con CSS puro
✅ Instalador automático
✅ Sin dependencias externas

## 🛠️ Stack Tecnológico

- **Backend**: PHP 7.4+ nativo
- **Base de Datos**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript Vanilla
- **Sin frameworks**: No React, Vue, Angular, Laravel, Bootstrap ni librerías externas

## 📋 Requisitos del sistema

- PHP 7.4 o superior
- Extensiones PHP: PDO, pdo_mysql, mysqli, gd
- Servidor web Apache o Nginx
- MySQL 5.7+ o MariaDB 10.2+
- Mod Rewrite habilitado en Apache

## 🚀 Instalación

1. **Descargar y colocar los archivos** en el directorio de tu servidor web (ej: `/var/www/html/Workforce_tracker_app/`)

2. **Configurar la base de datos** editando el fichero `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'workforce_tracker');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

3. **Ejecutar el instalador** accediendo desde el navegador a:
   ```
   http://localhost/Workforce_tracker_app/install.php
   ```

4. El instalador comprobará automáticamente todos los requisitos, creará la base de datos, las tablas y cargará los datos de prueba.

## 🔑 Credenciales de prueba

### 👤 Empleados estándar
Contraseña: `Empleat2025!`
- empleado.desarrollo@empresa.com
- empleado.contabilidad@empresa.com
- empleado.rrhh@empresa.com
- empleado.direccion@empresa.com

### 👔 Jefes de departamento
Contraseña: `Cap2025!`
- jefe.desarrollo@empresa.com
- jefe.contabilidad@empresa.com
- jefe.rrhh@empresa.com
- jefe.direccion@empresa.com

## 📂 Estructura del proyecto

```
/workforce-tracker-app
├── /assets
│   ├── /css/          # Hojas de estilo
│   ├── /js/           # JavaScript
│   └── /img/          # Imágenes y fotos de perfil
├── /includes
│   ├── config.php     # Configuración general
│   ├── db.php         # Conexión PDO a base de datos
│   ├── auth.php       # Sistema de autenticación
│   └── functions.php  # Funciones generales
├── /empleado          # Área de empleado estándar
├── /jefe              # Área de jefe de departamento
├── /auth              # Autenticación
├── /logs              # Archivos de log
├── index.php          # Página principal
├── install.php        # Instalador automático
├── database.sql       # Estructura de base de datos
└── .htaccess          # Reglas de servidor y seguridad
```

## 🔒 Características de seguridad

- Contraseñas hasheadas con `password_hash()`
- Todas las consultas SQL usan PDO preparadas
- Protección contra SQL Injection
- Sesiones PHP seguras con HttpOnly y SameSite
- Cabeceras de seguridad X-Frame-Options, X-XSS-Protection
- Bloqueo de acceso directo a archivos sensibles
- Protección CSRF implícita

## ⚙️ Funcionalidades por rol

### 🧑‍💼 Rol Empleado
- ✅ Dashboard personal
- ✅ Botón de fichaje entrada/salida
- ✅ Indicador de estado VPN
- ✅ Fichaje manual nocturno
- ✅ Historial de fichajes
- ✅ Gestión de perfil
- ✅ Visualización de horario
- ✅ Proyectos asignados
- ✅ Registro de horas extras
- ✅ Visualización de nóminas
- ✅ Notificaciones y alertas
- ✅ Directorio de contactos

### 🧑‍💼 Rol Jefe de Departamento
- ✅ Dashboard de equipo en tiempo real
- ✅ Visualización de fichajes de todo el departamento
- ✅ Aprobación de registros manuales
- ✅ Gestión de horarios de empleados
- ✅ Informes y analíticas
- ✅ Aprobación de solicitudes de horas extras
- ✅ Exportación de informes CSV
- ✅ Envío de comunicaciones al equipo

## 📝 Notas importantes

⚠️ **IMPORTANTE**: Después de la instalación correcta, elimina o protege con contraseña el fichero `install.php` por seguridad.

El fichero `installed.lock` se crea automáticamente después de la instalación para evitar ejecuciones posteriores.

## 📄 Licencia

Software desarrollado para uso empresarial interno.