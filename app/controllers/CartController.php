<?php

namespace App\Controllers;

use App\Models\Cart;

class CartController
{
    private $cartModel;

    public function __construct($db)
    {
        $this->cartModel = new Cart($db);
    }

    public function index()
    {
        if (!is_logged_in()) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để xem giỏ hàng của bạn.';
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        $cart_items = $this->cartModel->getCartContents();
        $total = $this->cartModel->calculateSubtotal();
        require 'app/views/cart/index.php';
    }

    public function add()
    {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($productId && $quantity) {
            $this->cartModel->addToCart($productId, $quantity);
        }

        header('Location: cart.php');
        exit();
    }

    public function updateQuantity()
    {
        header('Content-Type: application/json');
        $cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($cartItemId && $quantity) {
            if ($this->cartModel->updateQuantity($cartItemId, $quantity)) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật số lượng thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật số lượng']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }
        exit();
    }

    public function remove()
    {
        header('Content-Type: application/json');
        $cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);

        if ($cartItemId) {
            if ($this->cartModel->removeFromCart($cartItemId)) {
                echo json_encode(['success' => true, 'message' => 'Xóa món thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa món']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID món không hợp lệ']);
        }
        exit();
    }

    public function processSelection(): void
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        $selectedItemsJson = $_POST['selected_items'] ?? '[]';
        $selectedItemIds = json_decode($selectedItemsJson, true);

        if (empty($selectedItemIds) || !is_array($selectedItemIds)) {
            $_SESSION['error_message'] = 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.';
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        $_SESSION['checkout_items'] = $selectedItemIds;
        header('Location: ' . BASE_URL . 'checkout');
        exit();
    }
}
