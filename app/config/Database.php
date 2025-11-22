<?php
namespace App\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        $host = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
        $db   = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? 'seminar_db';
        $user = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? 'root';
        $pass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("❌ ارتباط با دیتابیس برقرار نشد: " . $e->getMessage());
        }
    }

    public static function getConnection() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}