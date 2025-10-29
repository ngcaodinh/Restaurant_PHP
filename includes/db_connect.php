<?php
require_once 'config.php';

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout = 28800, interactive_timeout = 28800"
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Kết nối cơ sở dữ liệu thất bại: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        // Kiểm tra kết nối còn sống
        try {
            self::$instance->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            error_log("Connection lost: " . $e->getMessage());
            $max_attempts = 3;
            $attempt = 0;
            while ($attempt < $max_attempts) {
                try {
                    self::$instance->connect();
                    break;
                } catch (PDOException $e) {
                    $attempt++;
                    error_log("Reconnection attempt $attempt failed: " . $e->getMessage());
                    if ($attempt >= $max_attempts) {
                        die("Không thể kết nối lại cơ sở dữ liệu sau $max_attempts lần thử: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                    }
                    sleep(1);
                }
            }
        }
        return self::$instance->pdo;
    }
}

$pdo = Database::getInstance();
