<?php

namespace App\Models;

use PDO;

class Favorite
{
    private $pdo;

    public function __construct()
    {
        // Ensure the Database class is available
        if (!class_exists('Database')) {
            require_once __DIR__ . '/../../includes/db_connect.php';
        }
        $this->pdo = \Database::getInstance();
    }

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
