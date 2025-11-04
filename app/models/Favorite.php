<?php

namespace App\Models;

use PDO;

/**
 * Class Favorite - Model quản lý món ăn yêu thích
 *
 * Xử lý các thao tác liên quan đến món ăn yêu thích của người dùng.
 */
class Favorite
{
    /** @var PDO Đối tượng kết nối CSDL */
    private $pdo;

    /**
     * Constructor.
     *
     * Khởi tạo kết nối CSDL.
     */
    public function __construct()
    {
        // Đảm bảo class Database đã được tải
        if (!class_exists('Database')) {
            require_once __DIR__ . '/../../includes/db_connect.php';
        }
        $this->pdo = \Database::getInstance();
    }

    /**
     * Lấy danh sách món ăn yêu thích của người dùng.
     *
     * @param int $userId ID của người dùng.
     * @return array Mảng các món ăn yêu thích.
     */
    public function getFavoritesByUserId($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT d.id, d.name, d.price, d.image
                FROM favorites f
                JOIN dishes d ON f.dish_id = d.id
                WHERE f.user_id = ? AND d.deleted_at IS NULL
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error fetching favorites: ' . $e->getMessage());
            return [];
        }
    }
}
