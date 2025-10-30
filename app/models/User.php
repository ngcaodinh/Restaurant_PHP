<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

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

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$userId]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = ? AND deleted_at IS NULL");
        $stmt->execute([strtolower($email)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = ? AND deleted_at IS NULL");
        $stmt->execute([$phone]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

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
