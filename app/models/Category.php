<?php

namespace App\Models;

use PDO;

/**
 * Class Category - Model quản lý danh mục món ăn
 *
 * Xử lý các thao tác với danh mục trong cơ sở dữ liệu.
 */
class Category
{
    /** @var PDO Đối tượng kết nối CSDL */
    private PDO $db;

    /**
     * Constructor.
     *
     * @param PDO $db Đối tượng PDO.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Lấy tất cả các danh mục.
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, created_at, updated_at
            FROM categories
            WHERE deleted_at IS NULL
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin danh mục theo ID.
     *
     * @param int $id ID danh mục.
     * @return array|null
     */
    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, created_at, updated_at
            FROM categories
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Tạo danh mục mới.
     *
     * @param array $data Dữ liệu danh mục.
     * @return int|null ID của danh mục mới hoặc null nếu lỗi.
     */
    public function createCategory(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, created_at, updated_at)
                VALUES (?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['name']
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Category creation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật thông tin danh mục.
     *
     * @param int $id ID danh mục.
     * @param array $data Dữ liệu cần cập nhật.
     * @return bool
     */
    public function updateCategory(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE categories
                SET name = ?, updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([
                $data['name'],
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Category update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa mềm một danh mục.
     *
     * @param int $id ID danh mục.
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        try {
            // Kiểm tra xem danh mục có món ăn nào không
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM dishes WHERE category_id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                return false; // Không thể xóa danh mục có món ăn
            }

            $stmt = $this->db->prepare("UPDATE categories SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Category deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách các danh mục kèm theo số lượng món ăn.
     *
     * @return array
     */
    public function getCategoriesWithDishCount(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.id,
                c.name,
                COUNT(d.id) as dish_count
            FROM categories c
            LEFT JOIN dishes d ON c.id = d.category_id AND d.deleted_at IS NULL
            WHERE c.deleted_at IS NULL
            GROUP BY c.id, c.name
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
