<?php

namespace App\Models;

use PDO;

class Order
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createOrder(int $userId, array $orderData, array $cartItems): ?int
    {
        try {
            $this->db->beginTransaction();

            // Create order
            $stmt = $this->db->prepare("
                                INSERT INTO orders (user_id, total_price, status, delivery_address, phone, notes, created_at, updated_at)
                VALUES (?, ?, 'Pending', ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $userId,
                $orderData['total_price'],
                $orderData['delivery_address'] ?? '',
                $orderData['phone'] ?? '',
                $orderData['notes'] ?? ''
            ]);

            $orderId = $this->db->lastInsertId();

            // Add order items from the provided array

            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, dish_id, quantity, price, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([
                    $orderId,
                    $item['dish_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                // Update dish sales count
                $stmt = $this->db->prepare("UPDATE dishes SET sales_count = sales_count + ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['dish_id']]);
            }



            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Order creation error: " . $e->getMessage());
            return null;
        }
    }

    public function getUserOrders(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id,
                o.total_amount,
                o.status,
                o.delivery_address,
                o.phone,
                o.notes,
                o.created_at,
                o.updated_at,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderDetails(int $orderId, ?int $userId = null): ?array
    {
        $sql = "
            SELECT 
                o.id,
                o.user_id,
                                o.total_price,
                o.status,
                o.delivery_address,
                o.phone,
                o.notes,
                o.created_at,
                o.updated_at,
                u.name as user_name,
                u.email as user_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ";

        $params = [$orderId];
        if ($userId !== null) {
            $sql .= " AND o.user_id = ?";
            $params[] = $userId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Get order items
        $stmt = $this->db->prepare("
            SELECT 
                oi.dish_id,
                oi.quantity,
                oi.price,
                d.name as dish_name,
                d.image as dish_image
            FROM order_items oi
            JOIN dishes d ON oi.dish_id = d.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        try {
            $validStatuses = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivered', 'Cancelled'];
            if (!in_array($status, $validStatuses)) {
                return false;
            }

            $stmt = $this->db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            return true;
        } catch (\Exception $e) {
            error_log("Order status update error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllOrders(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id,
                o.user_id,
                o.total_amount,
                o.status,
                o.delivery_address,
                o.phone,
                o.created_at,
                o.updated_at,
                u.name as user_name,
                u.email as user_email,
                COUNT(oi.id) as item_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderStats(): array
    {
        $stats = [];

        // Total orders
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Orders by status
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $stmt->execute();
        $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['by_status'] = [];
        foreach ($statusCounts as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        // Total revenue
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'Cancelled'");
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

        // Today's orders
        $stmt = $this->db->prepare("SELECT COUNT(*) as today FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['today_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

        return $stats;
    }

    public function cancelOrder(int $orderId, int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = 'Cancelled', updated_at = NOW() 
                WHERE id = ? AND user_id = ? AND status IN ('Pending', 'Confirmed')
            ");
            $stmt->execute([$orderId, $userId]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Order cancellation error: " . $e->getMessage());
            return false;
        }
    }
}
