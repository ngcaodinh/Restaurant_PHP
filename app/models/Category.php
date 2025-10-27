<?php
namespace App\Models;

use PDO;

class Category
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllCategories(): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, created_at, updated_at 
            FROM categories 
            WHERE deleted_at IS NULL 
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, description, created_at, updated_at 
            FROM categories 
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function createCategory(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, description, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? ''
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Category creation error: " . $e->getMessage());
            return null;
        }
    }

    public function updateCategory(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE categories 
                SET name = ?, description = ?, updated_at = NOW() 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Category update error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory(int $id): bool
    {
        try {
            // Check if category has dishes
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM dishes WHERE category_id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($count > 0) {
                return false; // Cannot delete category with dishes
            }

            $stmt = $this->db->prepare("UPDATE categories SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Category deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function getCategoriesWithDishCount(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.id, 
                c.name, 
                c.description,
                COUNT(d.id) as dish_count
            FROM categories c
            LEFT JOIN dishes d ON c.id = d.category_id AND d.deleted_at IS NULL
            WHERE c.deleted_at IS NULL
            GROUP BY c.id, c.name, c.description
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
