<?php

namespace App\Controllers;

use App\Models\Cart;
use Core\BaseController;

/**
 * Class CartController - Controller xử lý giỏ hàng
 *
 * Quản lý các hoạt động liên quan đến giỏ hàng như hiển thị, thêm, xóa, cập nhật.
 */
class CartController extends BaseController
{
    /**
     * Model Cart để thao tác với dữ liệu giỏ hàng
     * @var Cart
     */
    private $cartModel;

    /**
     * Constructor khởi tạo CartModel
     * @param PDO $db Kết nối database
     */
    public function __construct($db)
    {
        $this->cartModel = new Cart($db);
    }

    /**
     * Hiển thị trang giỏ hàng
     *
     * Lấy thông tin các món trong giỏ hàng và tổng tiền, sau đó hiển thị view.
     * @return void
     */
    public function index()
    {
        // Yêu cầu đăng nhập để xem giỏ hàng
        if (!is_logged_in()) {
            $_SESSION['flash_message'] = 'Vui lòng đăng nhập để xem giỏ hàng của bạn.';
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Lấy dữ liệu giỏ hàng và hiển thị view
        $cart_items = $this->cartModel->getCartContents();
        $total = $this->cartModel->calculateSubtotal();
        $this->view('cart/index', compact('cart_items', 'total'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     *
     * Xử lý yêu cầu thêm sản phẩm và số lượng vào giỏ hàng.
     * @return void
     */
    public function add()
    {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($productId && $quantity) {
            $this->cartModel->addToCart($productId, $quantity);
        }

        // Chuyển hướng lại trang giỏ hàng
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }




    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng (AJAX)
     *
     * @return void
     */
    public function updateQuantity()
    {
        header('Content-Type: application/json');
        $cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($cartItemId && $quantity > 0) {
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

    /**
     * Xóa một sản phẩm khỏi giỏ hàng (AJAX)
     *
     * @return void
     */
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

    /**
     * Xử lý các sản phẩm được chọn để thanh toán
     *
     * Lưu ID các sản phẩm được chọn vào session và chuyển hướng đến trang thanh toán.
     * @return void
     */
    public function processSelection(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        $selectedItemsJson = $_POST['selected_items'] ?? '[]';
        $selectedItemIds = json_decode($selectedItemsJson, true);

        // Kiểm tra nếu không có sản phẩm nào được chọn
        if (empty($selectedItemIds) || !is_array($selectedItemIds)) {
            $_SESSION['error_message'] = 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.';
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        // Lưu vào session và chuyển hướng
        $_SESSION['checkout_items'] = $selectedItemIds;
        header('Location: ' . BASE_URL . 'checkout');
        exit();
    }
}
