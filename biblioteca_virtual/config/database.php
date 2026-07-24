<?php
// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================

// Parámetros de conexión
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'biblioteca_virtual');
define('DB_USER', 'root');
define('DB_PASS', ''); // Dejar vacío en XAMPP por defecto

// Clase para manejar la conexión
class Database {
    private static $instance = null;
    private $connection;
    
    // Constructor privado (patrón Singleton)
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            // Configurar codificación UTF-8
            $this->connection->set_charset("utf8mb4");
            
            // Habilitar excepciones para errores
            $this->connection->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
            
        } catch (mysqli_sql_exception $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
    
    // Obtener instancia única
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Obtener conexión
    public function getConnection() {
        return $this->connection;
    }
    
    // Método para ejecutar consultas preparadas
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    // Método para escapar strings
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    // Obtener ID del último insert
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {}
}

// Función helper para obtener la conexión
function getDB() {
    return Database::getInstance()->getConnection();
}

// Función helper para consultas preparadas
function prepareQuery($sql) {
    return Database::getInstance()->prepare($sql);
}

// Función helper para sanitizar datos
function sanitize($input) {
    $db = getDB();
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return $db->real_escape_string(trim($input));
}
?>