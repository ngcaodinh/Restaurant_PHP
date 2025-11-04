<?php

namespace App\Models;

use PDO;
use Exception;

/**
 * Class Cart - Model quản lý giỏ hàng
 *
 * Xử lý các thao tác với giỏ hàng trong cơ sở dữ liệu.
 */
class Cart
{
    /** @var PDO Đối tượng kết nối CSDL */
    private $db;
    /** @var int|null ID của người dùng hiện tại */
    private $user_id;

    /**
     * Constructor.
     *
     * @param PDO $db Đối tượng PDO.
     */
    public function __construct($db)
    {
        $this->db = $db;
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
        }
    }

    /**
     * Lấy hoặc tạo ID giỏ hàng cho người dùng.
     *
     * @return int ID của giỏ hàng.
     */
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

    /**
     * Thêm sản phẩm vào giỏ hàng.
     *
     * @param int $productId ID sản phẩm.
     * @param int $quantity Số lượng.
     * @return bool
     */
    public function addToCart($productId, $quantity): ?int
    {
        $cart_id = $this->getCartId();
        $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND dish_id = ?");
        $stmt->execute([$cart_id, $productId]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            $new_quantity = $cart_item['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$new_quantity, $cart_item['id']]);
            return $cart_item['id'];
        } else {
            $stmt = $this->db->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$cart_id, $productId, $quantity]);
            return $this->db->lastInsertId();
        }
    }

    /**
     * Cập nhật số lượng của một sản phẩm trong giỏ hàng.
     *
     * @param int $cartItemId ID của sản phẩm trong giỏ hàng.
     * @param int $quantity Số lượng mới.
     * @return bool
     */
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

    /**
     * Xóa một sản phẩm khỏi giỏ hàng.
     *
     * @param int $cartItemId ID của sản phẩm trong giỏ hàng.
     * @return bool
     */
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

    /**
     * Lấy tất cả sản phẩm trong giỏ hàng của người dùng.
     *
     * @return array
     */
    public function getCartContents()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT ci.id, ci.quantity, d.id AS dish_id, d.name, d.price, d.image, d.description
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                JOIN dishes d ON ci.dish_id = d.id
                WHERE c.user_id = ? AND d.deleted_at IS NULL
                ORDER BY ci.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error fetching cart contents: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy các sản phẩm trong giỏ hàng theo danh sách ID.
     *
     * @param array $itemIds Mảng các ID sản phẩm trong giỏ hàng.
     * @param int $userId ID người dùng.
     * @return array
     */
    public function getCartItemsByIds(array $itemIds, int $userId)
    {
        if (empty($itemIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));

        $sql = "
            SELECT ci.id, ci.quantity, d.id AS dish_id, d.name, d.price, d.image, d.description
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN dishes d ON ci.dish_id = d.id
            WHERE c.user_id = ? AND ci.id IN ($placeholders) AND d.deleted_at IS NULL
        ";

        $params = array_merge([$userId], $itemIds);

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error fetching cart items by IDs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Xóa các sản phẩm khỏi giỏ hàng theo danh sách ID.
     *
     * @param array $itemIds Mảng các ID sản phẩm trong giỏ hàng.
     * @return bool
     */
    public function removeItemsByIds(array $itemIds)
    {
        if (empty($itemIds)) {
            return false;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return false;
        }

        $cartIdStmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
        $cartIdStmt->execute([$userId]);
        $cart = $cartIdStmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            return true; // Không có giỏ hàng, không có gì để xóa
        }
        $cartId = $cart['id'];

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $sql = "DELETE FROM cart_items WHERE cart_id = ? AND id IN ($placeholders)";

        $params = array_merge([$cartId], $itemIds);

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Error removing items from cart: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tính tổng tiền của giỏ hàng.
     *
     * @return float
     */
    public function calculateSubtotal()
    {
        $total = 0;
        $cart_items = $this->getCartContents();
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Xóa toàn bộ giỏ hàng.
     *
     * @return bool
     */
    public function clearCart()
    {
        $cart_id = $this->getCartId();
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        return $stmt->execute([$cart_id]);
    }
}
