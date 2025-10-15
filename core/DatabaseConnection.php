<?php
// 📁 core/DatabaseConnection.php - ACTUALIZADO

class DatabaseConnection {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $config = include __DIR__ . '/../config/.env_edificio';
        
        try {
            $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // Opciones específicas para caching_sha2_password
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            
            $this->connection = new PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD'], $options);
            
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            
            // Intentar conexión sin SSL (para caching_sha2_password)
            try {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                $this->connection = new PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD'], $options);
            } catch (PDOException $e2) {
                throw new Exception("Error al conectar con la base de datos: " . $e->getMessage());
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
?>