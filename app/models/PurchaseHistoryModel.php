<?php

namespace App\Models;

use PDO;

class PurchaseHistoryModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getPurchaseHistoryByUserId(int $userId): array
    {
        // Step 1: Get all orders for the user
        $stmt = $this->db->prepare("
            SELECT id, total_price, status, created_at
            FROM orders
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

        if (empty($orders)) {
            return [];
        }

        // Step 2: Get all items for those orders in a single query
        $orderIds = array_keys($orders);
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        $itemStmt = $this->db->prepare("
            SELECT
                oi.order_id,
                oi.quantity,
                oi.price,
                d.name as dish_name,
                d.image as dish_image
            FROM order_items oi
            JOIN dishes d ON oi.dish_id = d.id
            WHERE oi.order_id IN ($placeholders)
        ");
        $itemStmt->execute($orderIds);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 3: Map items back to their respective orders
        foreach ($items as $item) {
            $orderId = $item['order_id'];
            if (isset($orders[$orderId])) {
                // Initialize 'items' array if it doesn't exist
                if (!isset($orders[$orderId]['items'])) {
                    $orders[$orderId]['items'] = [];
                }
                $orders[$orderId]['items'][] = $item;
            }
        }

        // Step 4: Add the order ID back into the order data and ensure 'items' key exists
        foreach ($orders as $orderId => &$order) {
            $order['id'] = $orderId;
            if (!isset($order['items'])) {
                $order['items'] = [];
            }
        }

        return array_values($orders); // Return as a simple array
    }
}
