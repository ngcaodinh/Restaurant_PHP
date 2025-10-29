<?php

namespace App\Models;

use PDO;
use Exception;

class Cart
{
    private $db;
    private $user_id;

    public function __construct($db)
    {
        $this->db = $db;
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
        }
    }

    private function getCartId()
    {
        $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            $stmt = $this->db->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$this->user_id]);
            return $this->db->lastInsertId();
        } else {
            return $cart['id'];
        }
    }

    public function addToCart($productId, $quantity)
    {
        $cart_id = $this->getCartId();
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND dish_id = ?");
        $stmt->execute([$cart_id, $productId]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            $new_quantity = $cart_item['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            return $stmt->execute([$new_quantity, $cart_item['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, ?)");
            return $stmt->execute([$cart_id, $productId, $quantity]);
        }
    }

    public function updateQuantity($cartItemId, $quantity)
    {
        $stmt = $this->db->prepare("SELECT ci.id FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE ci.id = ? AND c.user_id = ?");
        $stmt->execute([$cartItemId, $this->user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            return $stmt->execute([$quantity, $cartItemId]);
        }
        return false;
    }

    public function removeFromCart($cartItemId)
    {
        $stmt = $this->db->prepare("SELECT ci.id FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE ci.id = ? AND c.user_id = ?");
        $stmt->execute([$cartItemId, $this->user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = ?");
            return $stmt->execute([$cartItemId]);
        }
        return false;
    }

    public function getCartContents()
    {
        if (!$this->user_id) return [];
        try {
            $stmt = $this->db->prepare("
                SELECT ci.id, ci.quantity, d.id AS dish_id, d.name, d.price, d.image, d.description
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                JOIN dishes d ON ci.dish_id = d.id
                WHERE c.user_id = ? AND d.deleted_at IS NULL
                ORDER BY ci.created_at DESC
            ");
            $stmt->execute([$this->user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function calculateSubtotal()
    {
        $total = 0;
        $cart_items = $this->getCartContents();
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function clearCart()
    {
        $cart_id = $this->getCartId();
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        return $stmt->execute([$cart_id]);
    }
}
