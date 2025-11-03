<?php

/**
 * Tệp quản lý kết nối cơ sở dữ liệu
 *
 * Tệp này cung cấp class Database để quản lý kết nối đến cơ sở dữ liệu MySQL.
 * Sử dụng Singleton pattern để đảm bảo chỉ có một kết nối duy nhất trong suốt vòng đời ứng dụng.
 * Hỗ trợ tự động kết nối lại khi mất kết nối.
 */

require_once 'config.php';

/**
 * Class Database - Quản lý kết nối cơ sở dữ liệu
 *
 * Sử dụng Singleton pattern để tạo và quản lý một kết nối PDO duy nhất.
 * Tự động kiểm tra và kết nối lại nếu kết nối bị mất.
 */
class Database
{
    /**
     * Instance duy nhất của class Database
     * @var Database|null
     */
    private static $instance = null;

    /**
     * Đối tượng PDO để kết nối database
     * @var PDO
     */
    private $pdo;

    /**
     * Constructor private để ngăn khởi tạo trực tiếp
     * Chỉ có thể tạo instance thông qua getInstance()
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Thiết lập kết nối đến cơ sở dữ liệu
     *
     * Phương thức này tạo kết nối PDO đến MySQL với các tùy chọn:
     * - Chế độ báo lỗi: Exception
     * - Chế độ fetch mặc định: Associative array
     * - Tắt emulate prepares để bảo mật tốt hơn
     * - Thiết lập timeout cho session
     *
     * @return void
     * @throws PDOException Nếu kết nối thất bại
     */
    private function connect()
    {
        try {
            // Tạo DSN (Data Source Name) cho MySQL
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

            // Tạo kết nối PDO với các tùy chọn
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // Bật chế độ exception khi có lỗi
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Fetch dữ liệu dạng mảng kết hợp
                PDO::ATTR_EMULATE_PREPARES => false,                   // Tắt emulate prepares để bảo mật
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout = 28800, interactive_timeout = 28800" // Thiết lập timeout 8 giờ
            ]);
        } catch (PDOException $e) {
            // Ghi log lỗi
            error_log("Database connection failed: " . $e->getMessage());
            // Hiển thị thông báo lỗi và dừng ứng dụng
            die("Kết nối cơ sở dữ liệu thất bại: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    /**
     * Lấy instance duy nhất của Database (Singleton pattern)
     *
     * Phương thức này trả về đối tượng PDO để thao tác với database.
     * Nếu instance chưa tồn tại, nó sẽ tạo mới.
     * Tự động kiểm tra kết nối còn hoạt động không, nếu mất kết nối sẽ thử kết nối lại.
     *
     * @return PDO Đối tượng PDO để thao tác với database
     * @throws PDOException Nếu không thể kết nối sau nhiều lần thử
     */
    public static function getInstance()
    {
        // Tạo instance mới nếu chưa tồn tại
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        // Kiểm tra kết nối còn hoạt động không
        try {
            self::$instance->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            // Kết nối bị mất, ghi log và thử kết nối lại
            error_log("Connection lost: " . $e->getMessage());

            $max_attempts = 3;  // Số lần thử kết nối lại tối đa
            $attempt = 0;

            // Thử kết nối lại nhiều lần
            while ($attempt < $max_attempts) {
                try {
                    self::$instance->connect();
                    break;  // Kết nối thành công, thoát vòng lặp
                } catch (PDOException $e) {
                    $attempt++;
                    error_log("Reconnection attempt $attempt failed: " . $e->getMessage());

                    // Nếu đã thử đủ số lần, dừng ứng dụng
                    if ($attempt >= $max_attempts) {
                        die("Không thể kết nối lại cơ sở dữ liệu sau $max_attempts lần thử: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                    }

                    // Chờ 1 giây trước khi thử lại
                    sleep(1);
                }
            }
        }

        // Trả về đối tượng PDO
        return self::$instance->pdo;
    }
}

// Khởi tạo kết nối database toàn cục
$pdo = Database::getInstance();
