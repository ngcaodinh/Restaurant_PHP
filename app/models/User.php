<?php

namespace App\Models;

use PDO;

/**
 * Class User - Model quản lý người dùng
 *
 * Class này xử lý tất cả các thao tác liên quan đến người dùng trong database,
 * bao gồm tìm kiếm, tạo mới, cập nhật và xóa người dùng.
 */
class User
{
    /**
     * Đối tượng PDO để kết nối database
     * @var PDO
     */
    private PDO $db;

    /**
     * Constructor khởi tạo model với kết nối database
     *
     * @param PDO $db Đối tượng PDO để thao tác với database
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Tìm người dùng theo email hoặc số điện thoại
     *
     * Hàm này tìm kiếm người dùng bằng email hoặc số điện thoại.
     * Chỉ trả về người dùng chưa bị xóa (deleted_at IS NULL).
     *
     * @param string $identifier Email hoặc số điện thoại cần tìm
     * @return array|null Thông tin người dùng nếu tìm thấy, null nếu không
     */
    public function findByEmailOrPhone(string $identifier): ?array
    {
        $query = 'SELECT id, name, email, phone, password, role, status, google_id, last_login
                  FROM users
                  WHERE (LOWER(email) = ? OR phone = ?) AND deleted_at IS NULL';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Cập nhật thời gian đăng nhập cuối cùng
     *
     * @param int $userId ID của người dùng
     * @return void
     */
    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Tìm người dùng theo ID
     *
     * @param int $id ID của người dùng cần tìm
     * @return array|null Thông tin người dùng nếu tìm thấy, null nếu không
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Tìm người dùng theo email
     *
     * @param string $email Email cần tìm (không phân biệt hoa thường)
     * @return array|null Thông tin người dùng nếu tìm thấy, null nếu không
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = ? AND deleted_at IS NULL");
        $stmt->execute([strtolower($email)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Tìm người dùng theo số điện thoại
     *
     * @param string $phone Số điện thoại cần tìm
     * @return array|null Thông tin người dùng nếu tìm thấy, null nếu không
     */
    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = ? AND deleted_at IS NULL");
        $stmt->execute([$phone]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Tạo người dùng mới
     *
     * Hàm này thêm một người dùng mới vào database.
     * Mật khẩu nên được hash trước khi truyền vào.
     *
     * @param array $data Mảng chứa thông tin người dùng (name, email, phone, password, role)
     * @return int|null ID của người dùng mới tạo, hoặc null nếu thất bại
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'Active', NOW(), NOW())
            ");
            $stmt->execute([
                $data['name'],
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['password'],
                $data['role'] ?? 'User'
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];

            // Handle password separately for hashing
            if (!empty($data['password'])) {
                $fields[] = "password = ?";
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
                unset($data['password']); // Unset to avoid processing it again
            }

            foreach (['name', 'email', 'phone', 'address', 'avatar_url', 'role', 'status'] as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND deleted_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$hashedPassword, $id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, phone, role, status, last_login, created_at, updated_at
            FROM users
            WHERE deleted_at IS NULL
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserStats(): array
    {
        $stats = [];

        // Total users
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL");
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Users by role
        $stmt = $this->db->prepare("SELECT role, COUNT(*) as count FROM users WHERE deleted_at IS NULL GROUP BY role");
        $stmt->execute();
        $roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['by_role'] = [];
        foreach ($roleCounts as $row) {
            $stats['by_role'][$row['role']] = $row['count'];
        }

        // Active users (logged in within last 30 days)
        $stmt = $this->db->prepare("SELECT COUNT(*) as active FROM users WHERE deleted_at IS NULL AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // New users this month
        $stmt = $this->db->prepare("SELECT COUNT(*) as new_this_month FROM users WHERE deleted_at IS NULL AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stmt->execute();
        $newThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['new_this_month'];

        // New users last month
        $stmt = $this->db->prepare("SELECT COUNT(*) as new_last_month FROM users WHERE deleted_at IS NULL AND created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stmt->execute();
        $newLastMonth = $stmt->fetch(PDO::FETCH_ASSOC)['new_last_month'];

        // Growth percentage
        if ($newLastMonth > 0) {
            $stats['user_growth'] = (($newThisMonth - $newLastMonth) / $newLastMonth) * 100;
        } else {
            $stats['user_growth'] = $newThisMonth > 0 ? 100 : 0;
        }

        return $stats;
    }

    public function createGoogleUser(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, google_id, role, status, created_at, updated_at)
                VALUES (?, ?, ?, 'User', 'Active', NOW(), NOW())
            ");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['google_id']
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Google user creation error: " . $e->getMessage());
            return null;
        }
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE google_id = ? AND deleted_at IS NULL");
        $stmt->execute([$googleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
