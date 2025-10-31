<?php

namespace App\Models;

use PDO;

class Dish
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAvailableWithCategory(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.category_id, c.name AS category_name
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE d.status = 'Available' AND d.deleted_at IS NULL
            ORDER BY d.sales_count DESC
        ");
        $stmt->execute();
        $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dishes as $index => &$dish) {
            $dish['is_best_seller'] = false; // set in controller later based on position
            $category_name = $dish['category_name'] ?? 'unknown';
            $categoryMap = [
                'món chính' => 'mn-chnh',
                'tráng miệng' => 'trng-ming',
                'đồ uống' => '-ung',
            ];
            $dish['category'] = $categoryMap[$category_name] ?? strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/u', '', $category_name)));
            $dish['image_url'] = !empty($dish['image']) ? $dish['image'] : 'https://via.placeholder.com/300x250?text=No+Image';
        }
        unset($dish);

        return $dishes;
    }

    public function getAllDishes(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.status, d.category_id, c.name AS category_name, d.created_at, d.updated_at
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE d.deleted_at IS NULL
            ORDER BY d.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDishById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.status, d.category_id, c.name AS category_name, d.created_at, d.updated_at
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE d.id = ? AND d.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function createDish(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO dishes (name, price, description, image, category_id, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $data['name'],
                $data['price'],
                $data['description'] ?? '',
                $data['image'] ?? '',
                $data['category_id'] ?? null,
                $data['status'] ?? 'Available'
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Dish creation error: " . $e->getMessage());
            return null;
        }
    }

    public function updateDish(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];

            foreach (['name', 'price', 'description', 'image', 'category_id', 'status'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $values[] = $id;
            $sql = "UPDATE dishes SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND deleted_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Dish update error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDish(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE dishes SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Dish deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function searchDishes(string $query): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.status, d.category_id, c.name AS category_name
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE (d.name LIKE ? OR d.description LIKE ?) AND d.deleted_at IS NULL AND d.status = 'Available'
            ORDER BY d.sales_count DESC
        ");
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDishesByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.status, d.category_id, c.name AS category_name
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE d.category_id = ? AND d.deleted_at IS NULL AND d.status = 'Available'
            ORDER BY d.sales_count DESC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSalesCount(int $dishId, int $quantity): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE dishes SET sales_count = sales_count + ? WHERE id = ?");
            $stmt->execute([$quantity, $dishId]);
            return true;
        } catch (\Exception $e) {
            error_log("Sales count update error: " . $e->getMessage());
            return false;
        }
    }

    public function getDishStats(): array
    {
        $stats = [];

        // Total dishes
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM dishes WHERE deleted_at IS NULL");
        $stmt->execute();
        $stats['total_dishes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Available dishes
        $stmt = $this->db->prepare("SELECT COUNT(*) as available FROM dishes WHERE status = 'Available' AND deleted_at IS NULL");
        $stmt->execute();
        $stats['available_dishes'] = $stmt->fetch(PDO::FETCH_ASSOC)['available'];

        // Best selling dishes
        $stmt = $this->db->prepare("SELECT name, sales_count FROM dishes WHERE deleted_at IS NULL ORDER BY sales_count DESC LIMIT 5");
        $stmt->execute();
        $stats['best_sellers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // New dishes this month
        $stmt = $this->db->prepare("SELECT COUNT(*) as new_this_month FROM dishes WHERE deleted_at IS NULL AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stmt->execute();
        $stats['new_dishes_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['new_this_month'];

        return $stats;
    }
}
