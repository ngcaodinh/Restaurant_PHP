<?php

namespace App\Models;

use PDO;

/**
 * Class Dish - Model quản lý món ăn
 *
 * Class này xử lý các thao tác liên quan đến món ăn trong database.
 */
class Dish
{
    /**
     * Đối tượng PDO để kết nối database
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructor khởi tạo model với kết nối database
     *
     * @param PDO $db Đối tượng PDO
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Lấy danh sách món ăn có sẵn kèm thông tin danh mục
     *
     * Lấy tất cả món ăn có trạng thái 'Available', sắp xếp theo số lượng bán.
     *
     * @return array Mảng các món ăn
     */
    public function getAvailableWithCategory(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.category_id, c.name AS category_name
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE d.status = 'Available' AND d.deleted_at IS NULL
            ORDER BY d.sales_count DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Xử lý dữ liệu món ăn
        foreach ($dishes as &$dish) {
            $dish['is_best_seller'] = false; // Sẽ được set trong controller
            $category_name = $dish['category_name'] ?? 'unknown';

            // Map tên danh mục sang slug
            $categoryMap = [
                'món chính' => 'mn-chnh',
                'tráng miệng' => 'trng-ming',
                'đồ uống' => '-ung',
            ];
            $dish['category'] = $categoryMap[$category_name] ?? strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/u', '', $category_name)));

            // Set URL hình ảnh mặc định nếu không có
            $dish['image_url'] = !empty($dish['image']) ? $dish['image'] : 'https://via.placeholder.com/300x250?text=No+Image';
        }
        unset($dish);

        return $dishes;
    }

    /**
     * Lấy tổng số món ăn có sẵn.
     *
     * @return int
     */
    public function getTotalAvailableDishes(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM dishes WHERE status = 'Available' AND deleted_at IS NULL");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }


    /**
     * Lấy tất cả món ăn với phân trang và bộ lọc
     *
     * @param int $limit Số lượng món ăn tối đa
     * @param int $offset Vị trí bắt đầu
     * @param array $filters Bộ lọc (search, category_id)
     * @return array Mảng các món ăn
     */
    public function getAllDishes(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $sql = "
            SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.status, d.category_id, c.name AS category_name, d.created_at, d.updated_at
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            WHERE d.deleted_at IS NULL
        ";

        $params = [];

        // Thêm bộ lọc tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (d.name LIKE ? OR d.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Thêm bộ lọc theo danh mục
        if (!empty($filters['category_id'])) {
            $sql .= " AND d.category_id = ?";
            $params[] = $filters['category_id'];
        }

        $sql .= " ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin món ăn theo ID
     *
     * @param int $id ID của món ăn
     * @return array|null Thông tin món ăn hoặc null
     */
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

    /**
     * Tạo món ăn mới
     *
     * @param array $data Dữ liệu món ăn
     * @return int|null ID của món ăn mới hoặc null nếu lỗi
     */
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

    /**
     * Cập nhật thông tin món ăn
     *
     * @param int $id ID món ăn
     * @param array $data Dữ liệu cần cập nhật
     * @return bool True nếu thành công, false nếu thất bại
     */
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
                return false; // Không có gì để cập nhật
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

    /**
     * Xóa mềm một món ăn
     *
     * @param int $id ID món ăn
     * @return bool True nếu thành công, false nếu thất bại
     */
    public function deleteDish(int $id): bool
    {
        try {
            // Sử dụng xóa mềm (soft delete) bằng cách cập nhật deleted_at
            $stmt = $this->db->prepare("UPDATE dishes SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Dish deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tìm kiếm món ăn theo từ khóa
     *
     * @param string $query Từ khóa tìm kiếm
     * @return array Mảng các món ăn phù hợp
     */
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

    /**
     * Lấy món ăn theo danh mục
     *
     * @param int $categoryId ID của danh mục
     * @return array Mảng các món ăn thuộc danh mục
     */
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

    /**
     * Cập nhật số lượng bán của món ăn
     *
     * @param int $dishId ID món ăn
     * @param int $quantity Số lượng đã bán
     * @return bool True nếu thành công
     */
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

    /**
     * Lấy danh sách các món ăn không có sẵn từ một danh sách ID
     *
     * @param array $dishIds Mảng các ID món ăn
     * @return array Mảng các món ăn không có sẵn
     */
    public function getUnavailableDishes(array $dishIds): array
    {
        if (empty($dishIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($dishIds), '?'));
        $sql = "
            SELECT id, name, status
            FROM dishes
            WHERE id IN ($placeholders) AND (status != 'Available' OR deleted_at IS NOT NULL)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($dishIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy các số liệu thống kê về món ăn cho dashboard admin
     *
     * @return array Mảng chứa các số liệu thống kê
     */
    public function getDishStats(): array
    {
        $stats = [];

        // Tổng số món ăn
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM dishes WHERE deleted_at IS NULL");
        $stmt->execute();
        $stats['total_dishes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Số món ăn có sẵn
        $stmt = $this->db->prepare("SELECT COUNT(*) as available FROM dishes WHERE status = 'Available' AND deleted_at IS NULL");
        $stmt->execute();
        $stats['available_dishes'] = $stmt->fetch(PDO::FETCH_ASSOC)['available'];

        // Top 5 món bán chạy nhất
        $stmt = $this->db->prepare("SELECT name, sales_count FROM dishes WHERE deleted_at IS NULL ORDER BY sales_count DESC LIMIT 5");
        $stmt->execute();
        $stats['best_sellers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Số món ăn mới trong tháng
        $stmt = $this->db->prepare("SELECT COUNT(*) as new_this_month FROM dishes WHERE deleted_at IS NULL AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stmt->execute();
        $stats['new_dishes_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['new_this_month'];

        return $stats;
    }
}
