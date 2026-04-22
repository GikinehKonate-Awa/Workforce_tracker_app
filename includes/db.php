<?php
/**
 * Conexión a Base de Datos usando PDO
 * Prepared statements para prevenir SQL Injection
 */

require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch(PDOException $e) {
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar una conexión de base de datos");
    }
}

// Función helper para obtener conexión rápidamente
function db() {
    return Database::getInstance()->getConnection();
}

// Función para ejecutar consulta preparada
function db_query($sql, $params = []) {
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Error en consulta: " . $e->getMessage());
        return false;
    }
}

// Función para obtener un solo registro
function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Función para obtener todos los registros
function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

// Función para insertar registro y devolver ID
function db_insert($table, $data) {
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = ':' . implode(', :', $keys);
    
    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($data);
        return db()->lastInsertId();
    } catch(PDOException $e) {
        error_log("Error en inserción: " . $e->getMessage());
        return false;
    }
}

// Función para actualizar registros
function db_update($table, $data, $where, $where_params = []) {
    $set = [];
    foreach ($data as $key => $value) {
        $set[] = "$key = :$key";
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
    
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute(array_merge($data, $where_params));
        return $stmt->rowCount();
    } catch(PDOException $e) {
        error_log("Error en actualización: " . $e->getMessage());
        return false;
    }
}
?>