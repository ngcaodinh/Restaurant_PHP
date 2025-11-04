<?php

namespace App\Models;

use PDO;

/**
 * Class Order - Model quản lý đơn hàng
 *
 * Xử lý các thao tác liên quan đến đơn hàng trong cơ sở dữ liệu.
 */
class Order
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
     * Tạo đơn hàng mới.
     *
     * @param int $userId ID người dùng.
     * @param array $orderData Dữ liệu đơn hàng.
     * @param array $cartItems Các sản phẩm trong giỏ hàng.
     * @return int|null ID của đơn hàng mới hoặc null nếu lỗi.
     */
    public function createOrder(int $userId, array $orderData, array $cartItems): ?int
    {
        try {
            $this->db->beginTransaction();

            // Tạo đơn hàng
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

            // Thêm các sản phẩm vào đơn hàng
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

                // Cập nhật số lượng bán của món ăn
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

    /**
     * Lấy danh sách đơn hàng của một người dùng.
     *
     * @param int $userId ID người dùng.
     * @return array
     */
    public function getUserOrders(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                o.id,
                o.total_price AS total_amount, -- Giữ alias total_amount để tương thích
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

    /**
     * Lấy chi tiết một đơn hàng.
     *
     * @param int $orderId ID đơn hàng.
     * @param int|null $userId ID người dùng (nếu cần kiểm tra quyền).
     * @return array|null
     */
    public function getOrderDetails(int $orderId, ?int $userId = null): ?array
    {
        $sql = "
            SELECT
                o.id, o.user_id, o.total_price, o.status, o.delivery_address,
                o.phone, o.notes, o.created_at, o.updated_at,
                u.name as user_name, u.email as user_email
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

        // Lấy các sản phẩm trong đơn hàng
        $stmt = $this->db->prepare("
            SELECT oi.dish_id, oi.quantity, oi.price, d.name as dish_name, d.image as dish_image
            FROM order_items oi
            JOIN dishes d ON oi.dish_id = d.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     *
     * @param int $orderId ID đơn hàng.
     * @param string $newStatus Trạng thái mới.
     * @return bool
     */
    public function updateOrderStatus(int $orderId, string $newStatus): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) return false; // Không tìm thấy đơn hàng

            // Logic chuyển đổi trạng thái hợp lệ
            $validTransitions = [
                'Pending' => ['Confirmed', 'Cancelled'],
                'Confirmed' => ['Processing', 'Cancelled'],
                'Processing' => ['Shipped'],
                'Shipped' => ['Delivered'],
                'Delivered' => [],
                'Cancelled' => [],
                'Refunded' => []
            ];

            if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
                return false; // Chuyển đổi trạng thái không hợp lệ
            }

            $stmt = $this->db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$newStatus, $orderId]);
        } catch (\Exception $e) {
            error_log("Order status update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tất cả đơn hàng (cho admin).
     *
     * @param int $limit Giới hạn số lượng.
     * @param int $offset Vị trí bắt đầu.
     * @return array
     */
    public function getAllOrders(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                o.id, o.user_id, o.total_price as total_amount, o.status, o.delivery_address,
                o.phone, o.created_at, o.updated_at, u.name as user_name, u.email as user_email,
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

    /**
     * Lấy thống kê đơn hàng cho dashboard admin.
     *
     * @return array
     */
    public function getOrderStats(): array
    {
        $stats = [];
        // Các câu truy vấn thống kê...
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $stmt->execute();
        $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stats['by_status'] = [];
        foreach ($statusCounts as $row) {
            $stats['by_status'][$row['status']] = $row['count'];
        }

        $stmt = $this->db->prepare("SELECT SUM(total_price) as revenue FROM orders WHERE status != 'Cancelled'");
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) as today FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['today_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

        // Tăng trưởng đơn hàng (so với tuần trước)
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)");
        $stmt->execute();
        $ordersThisWeek = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(NOW() - INTERVAL 1 WEEK, 1)");
        $stmt->execute();
        $ordersLastWeek = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $stats['order_growth_weekly'] = ($ordersLastWeek > 0) ? (($ordersThisWeek - $ordersLastWeek) / $ordersLastWeek) * 100 : ($ordersThisWeek > 0 ? 100 : 0);

        // Tăng trưởng doanh thu (so với tháng trước)
        $stmt = $this->db->prepare("SELECT SUM(total_price) as revenue FROM orders WHERE status != 'Cancelled' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stmt->execute();
        $revenueThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
        $stmt = $this->db->prepare("SELECT SUM(total_price) as revenue FROM orders WHERE status != 'Cancelled' AND created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')");
        $stmt->execute();
        $revenueLastMonth = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
        $stats['revenue_growth_monthly'] = ($revenueLastMonth > 0) ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 : ($revenueThisMonth > 0 ? 100 : 0);

        // Tăng trưởng doanh thu (so với hôm qua)
        $stmt = $this->db->prepare("SELECT SUM(total_price) as revenue FROM orders WHERE status != 'Cancelled' AND DATE(created_at) = CURDATE()");
        $stmt->execute();
        $revenueToday = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
        $stmt = $this->db->prepare("SELECT SUM(total_price) as revenue FROM orders WHERE status != 'Cancelled' AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY");
        $stmt->execute();
        $revenueYesterday = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
        $stats['revenue_growth_daily'] = ($revenueYesterday > 0) ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100 : ($revenueToday > 0 ? 100 : 0);

        return $stats;
    }

    /**
     * Lấy doanh thu hàng tháng.
     *
     * @return array
     */
    public function getMonthlyRevenue(): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as revenue
            FROM orders
            WHERE status = 'Delivered'
            GROUP BY month
            ORDER BY month ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hủy một đơn hàng.
     *
     * @param int $orderId ID đơn hàng.
     * @param int $userId ID người dùng.
     * @return bool
     */
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
