<?php
namespace App\Models;

use PDO;

class Cart
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getOrCreateCart(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            $stmt = $this->db->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            return $this->db->lastInsertId();
        }

        return $cart['id'];
    }

    public function addItem(int $cartId, int $dishId, int $quantity = 1): bool
    {
        try {
            $this->db->beginTransaction();

            // Check if item already exists
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND dish_id = ?");
            $stmt->execute([$cartId, $dishId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                // Update existing item
                $newQuantity = $item['quantity'] + $quantity;
                $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$newQuantity, $item['id']]);
            } else {
                // Add new item
                $stmt = $this->db->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity, created_at, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
                $stmt->execute([$cartId, $dishId, $quantity]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Cart addItem error: " . $e->getMessage());
            return false;
        }
    }

    public function removeItem(int $cartId, int $dishId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ? AND dish_id = ?");
            $stmt->execute([$cartId, $dishId]);
            return true;
        } catch (\Exception $e) {
            error_log("Cart removeItem error: " . $e->getMessage());
            return false;
        }
    }

    public function updateItemQuantity(int $cartId, int $dishId, int $quantity): bool
    {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($cartId, $dishId);
            }

            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_id = ? AND dish_id = ?");
            $stmt->execute([$quantity, $cartId, $dishId]);
            return true;
        } catch (\Exception $e) {
            error_log("Cart updateItemQuantity error: " . $e->getMessage());
            return false;
        }
    }

    public function getCartItems(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                d.id, 
                d.name, 
                d.price, 
                d.image, 
                ci.quantity,
                (d.price * ci.quantity) as total_price
            FROM dishes d 
            JOIN cart_items ci ON d.id = ci.dish_id 
            JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id = ? AND d.deleted_at IS NULL
            ORDER BY ci.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCartTotal(int $userId): float
    {
        $stmt = $this->db->prepare("
            SELECT SUM(d.price * ci.quantity) as total
            FROM dishes d 
            JOIN cart_items ci ON d.id = ci.dish_id 
            JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id = ? AND d.deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    public function getCartItemCount(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT SUM(ci.quantity) as count
            FROM cart_items ci 
            JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    public function clearCart(int $userId): bool
    {
        try {
            $cartId = $this->getOrCreateCart($userId);
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $stmt->execute([$cartId]);
            return true;
        } catch (\Exception $e) {
            error_log("Cart clearCart error: " . $e->getMessage());
            return false;
        }
    }

    public function replaceCartWithSingleItem(int $userId, int $dishId): bool
    {
        try {
            $this->db->beginTransaction();
            
            $cartId = $this->getOrCreateCart($userId);
            
            // Clear existing items
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $stmt->execute([$cartId]);
            
            // Add new item
            $stmt = $this->db->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity, created_at, updated_at) VALUES (?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $stmt->execute([$cartId, $dishId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Cart replaceCartWithSingleItem error: " . $e->getMessage());
            return false;
        }
    }
}
