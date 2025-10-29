<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Order;
use App\Models\Cart;


class OrderController extends BaseController
{
    private Order $orderModel;
    private Cart $cartModel;

    public function __construct($db)
    {
        $this->orderModel = new Order($db);
        $this->cartModel = new Cart($db);
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
            header('Location: ' . BASE_URL . 'orders');
            exit();
        }

        $userId = $_SESSION['user_role'] === 'Admin' ? null : $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetails($orderId, $userId);

        if (!$order) {
            $_SESSION['error_message'] = 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.';
            header('Location: ' . BASE_URL . 'orders');
            exit();
        }

        $this->view('orders/show', compact('order'));
    }

    public function checkout(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        $selectedItemIds = $_SESSION['checkout_items'] ?? [];
        if (empty($selectedItemIds)) {
            $_SESSION['error_message'] = 'Không có sản phẩm nào được chọn để thanh toán.';
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        $cartItems = $this->cartModel->getCartItemsByIds($selectedItemIds, $userId);

        $cartTotal = 0;
        foreach ($cartItems as $item) {
            $cartTotal += $item['price'] * $item['quantity'];
        }


        $this->view('orders/checkout', compact('cartItems', 'cartTotal'));
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'checkout');
            exit();
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);
        $userId = $_SESSION['user_id'];

        $checkoutItemsJson = $_POST['checkout_items'] ?? '[]';
        $selectedItemIds = json_decode($checkoutItemsJson, true);

        // Use the total price sent directly from the form
        $cartTotal = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;

        if (empty($selectedItemIds) || $cartTotal <= 0) {
            $_SESSION['error_message'] = 'Không có sản phẩm nào được chọn hoặc tổng tiền không hợp lệ.';
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        $cartItems = $this->cartModel->getCartItemsByIds($selectedItemIds, $userId);

        $deliveryAddress = trim($_POST['delivery_address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $errors = [];

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
            header('Location: ' . BASE_URL . 'checkout');
            exit();
        }


        $orderData = [
            'total_price' => $cartTotal,
            'delivery_address' => $deliveryAddress,
            'phone' => $phone,
            'notes' => $notes
        ];

        $orderId = $this->orderModel->createOrder($userId, $orderData, $cartItems);

        if ($orderId) {

            $_SESSION['success_message'] = 'Đã đặt hàng thành công!';
            $this->cartModel->removeItemsByIds($selectedItemIds);
            header('Location: ' . BASE_URL . 'order-confirmation?id=' . $orderId);
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.';
            header('Location: ' . BASE_URL . 'checkout');
        }
        exit();
    }

    public function confirmation(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$orderId) {
            $_SESSION['error_message'] = 'Đơn hàng không hợp lệ.';
            header('Location: ' . BASE_URL . 'orders');
            exit();
        }

        $userId = $_SESSION['user_role'] === 'Admin' ? null : $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetails($orderId, $userId);

        if (!$order) {
            $_SESSION['error_message'] = 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.';
            header('Location: ' . BASE_URL . 'orders');
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
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        $userRole = $_SESSION['user_role'] ?? null;
        if (!in_array($userRole, $allowedRoles)) {
            $_SESSION['error_message'] = 'Bạn không có quyền truy cập.';
            header('Location: ' . BASE_URL);
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
