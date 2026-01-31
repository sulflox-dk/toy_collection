<?php
namespace CollectionApp\Kernel;

use PDO;
use PDOException;

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        // Brug Config klassen i stedet for globals
        $host = Config::get('db_host');
        $db   = Config::get('db_name');
        $user = Config::get('db_user');
        $pass = Config::get('db_pass');
        $charset = Config::get('db_charset');

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        try {
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // Vi kan ikke bruge Debugger her hvis den ikke er init, så die() er ok ved fatal db fejl
            die("Database Connection Fejl: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function execute($sql, $params = []) {
        $start = microtime(true);
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $duration = microtime(true) - $start;
            
            // Log succes til debugger
            Debugger::addQuery($sql, $params, $duration);

            return $stmt;

        } catch (PDOException $e) {
            $duration = microtime(true) - $start;
            // Log fejl til debugger
            Debugger::addQuery($sql, $params, $duration, $e->getMessage());
            throw $e;
        }
    }
    
    // Helper til SELECT queries
    public function query($sql, $params = []) {
        return $this->execute($sql, $params);
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

}