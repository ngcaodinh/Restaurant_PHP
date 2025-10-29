<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Order;
use App\Models\Cart;
use Database;

class OrderController extends BaseController
{
    private Order $orderModel;
    private Cart $cartModel;

    public function __construct()
    {
        $this->orderModel = new Order(Database::getInstance());
        $this->cartModel = new Cart(Database::getInstance());
    }

    public function index(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $orders = $this->orderModel->getUserOrders($userId);

        $this->view('orders/index', compact('orders'));
    }

    public function show(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            $_SESSION['error_message'] = 'ID đơn hàng không hợp lệ.';
            header('Location: /orders');
            exit();
        }

        $userId = $_SESSION['user_role'] === 'Admin' ? null : $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetails($orderId, $userId);

        if (!$order) {
            $_SESSION['error_message'] = 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.';
            header('Location: /orders');
            exit();
        }

        $this->view('orders/show', compact('order'));
    }

    public function checkout(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $cartItems = $this->cartModel->getCartContents();
        $cartTotal = $this->cartModel->calculateSubtotal();

        if (empty($cartItems)) {
            $_SESSION['error_message'] = 'Giỏ hàng của bạn đang trống.';
            header('Location: /cart');
            exit();
        }

        $this->view('orders/checkout', compact('cartItems', 'cartTotal'));
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /checkout');
            exit();
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $cartItems = $this->cartModel->getCartContents();
        $cartTotal = $this->cartModel->calculateSubtotal();

        if (empty($cartItems)) {
            $_SESSION['error_message'] = 'Giỏ hàng của bạn đang trống.';
            header('Location: /cart');
            exit();
        }

        $deliveryAddress = trim($_POST['delivery_address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $errors = [];

        // Validate input
        if (empty($deliveryAddress)) {
            $errors[] = 'Vui lòng nhập địa chỉ giao hàng';
        }

        if (empty($phone)) {
            $errors[] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^(\+84|84|0)[3-9][0-9]{8}$/', $phone)) {
            $errors[] = 'Số điện thoại không đúng định dạng';
        }

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            header('Location: /checkout');
            exit();
        }

        // Create order
        $orderData = [
            'total_amount' => $cartTotal,
            'delivery_address' => $deliveryAddress,
            'phone' => $phone,
            'notes' => $notes
        ];

        $orderId = $this->orderModel->createOrder($userId, $orderData);

        if ($orderId) {
            $_SESSION['success_message'] = 'Đặt hàng thành công! Mã đơn hàng: #' . $orderId;
            header('Location: /order-confirmation?id=' . $orderId);
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.';
            header('Location: /checkout');
        }
        exit();
    }

    public function confirmation(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$orderId) {
            $_SESSION['error_message'] = 'Đơn hàng không hợp lệ.';
            header('Location: /orders');
            exit();
        }

        $userId = $_SESSION['user_role'] === 'Admin' ? null : $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetails($orderId, $userId);

        if (!$order) {
            $_SESSION['error_message'] = 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.';
            header('Location: /orders');
            exit();
        }

        $this->view('orders/confirmation', compact('order'));
    }

    public function cancel(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

        if (!$orderId) {
            $this->jsonResponse(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
            return;
        }

        $userId = $_SESSION['user_id'];

        if ($this->orderModel->cancelOrder($orderId, $userId)) {
            $this->jsonResponse(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Không thể hủy đơn hàng này']);
        }
    }

    public function updateStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAuth(['Admin']);

        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');

        if (!$orderId || empty($status)) {
            $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        if ($this->orderModel->updateOrderStatus($orderId, $status)) {
            $this->jsonResponse(['success' => true, 'message' => 'Đã cập nhật trạng thái đơn hàng']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái']);
        }
    }

    private function checkAuth(array $allowedRoles): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $userRole = $_SESSION['user_role'] ?? null;
        if (!in_array($userRole, $allowedRoles)) {
            $_SESSION['error_message'] = 'Bạn không có quyền truy cập.';
            header('Location: /');
            exit();
        }
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
