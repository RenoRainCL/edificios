<?php
// 📁 database/DatabaseConnection.php

class DatabaseConnection
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        // CORREGIDO: Usar .env_edificio en lugar de .env_proyecto
        $config = include __DIR__.'/../config/.env_edificio';

        try {
            $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=utf8mb4";
            $this->connection = new PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            error_log("✅ Conexión a BD exitosa: {$config['DB_NAME']}");
        } catch (PDOException $e) {
            error_log('❌ Error de conexión a BD: '.$e->getMessage());
            throw new Exception('Error al conectar con la base de datos: ' . $e->getMessage());
        }
    }

    // ... el resto del código permanece igual
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (PDOException $e) {
            error_log('❌ Error en query: '.$e->getMessage());
            throw $e;
        }
    }

    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }
}
?>