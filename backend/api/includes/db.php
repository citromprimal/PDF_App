<?php

require_once 'config.php';

class Database
{
    private $connection;
    private static $instance;

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }

    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function getError()
    {
        $errorInfo = $this->connection->errorInfo();
        return $errorInfo[2];
    }

}

?>