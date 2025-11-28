<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Dish;

/**
 * Class OrderController - Controller xử lý đơn hàng
 *
 * Quản lý các hoạt động liên quan đến đơn hàng như xem danh sách, xem chi tiết,
 * thanh toán, tạo đơn hàng, và xác nhận đơn hàng.
 */
class OrderController extends BaseController
{
    /** @var Order Model Order để thao tác với dữ liệu đơn hàng */
    private Order $orderModel;
    /** @var Cart Model Cart để thao tác với dữ liệu giỏ hàng */
    private Cart $cartModel;
    /** @var Dish Model Dish để thao tác với dữ liệu món ăn */
    private Dish $dishModel;

    /**
     * Constructor khởi tạo các model cần thiết
     * @param \PDO $db Kết nối database
     */
    public function __construct($db)
    {
        $this->orderModel = new Order($db);
        $this->cartModel = new Cart($db);
        $this->dishModel = new Dish($db);
    }

    /**
     * Hiển thị danh sách đơn hàng của người dùng
     *
     * @return void
     */
    public function index(): void
    {
        // Kiểm tra quyền truy cập
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        // Lấy danh sách đơn hàng của người dùng hiện tại
        $userId = $_SESSION['user_id'];
        $orders = $this->orderModel->getUserOrders($userId);

        // Hiển thị view
        $this->view('orders/index', compact('orders'));
    }

    /**
     * Hiển thị chi tiết một đơn hàng
     *
     * @return void
     */
    public function show(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$orderId) {
            $_SESSION['error_message'] = 'ID đơn hàng không hợp lệ.';
            header('Location: ' . BASE_URL . 'orders');
            exit();
        }

        // Admin có thể xem mọi đơn hàng, user chỉ xem được đơn hàng của mình
        $userId = $_SESSION['user_role'] === 'Admin' ? null : $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetails($orderId, $userId);

        if (!$order) {
            $_SESSION['error_message'] = 'Đơn hàng không tồn tại hoặc bạn không có quyền xem.';
            header('Location: ' . BASE_URL . 'orders');
            exit();
        }

        $this->view('orders/show', compact('order'));
    }

    /**
     * Xử lý chức năng "Mua ngay" (AJAX)
     *
     * Thêm một sản phẩm vào giỏ hàng, lưu vào session và chuẩn bị chuyển hướng đến trang thanh toán.
     * @return void
     */
    public function buyNow(): void
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục.']);
            exit();
        }

        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ.']);
            exit();
        }

        // Thêm sản phẩm vào giỏ hàng và lấy cart_item_id
        $cartItemId = $this->cartModel->addToCart($productId, 1);

        if ($cartItemId) {
            // Lưu chỉ sản phẩm này vào session để thanh toán
            $_SESSION['checkout_items'] = [$cartItemId];
            echo json_encode(['success' => true, 'redirect_url' => BASE_URL . 'checkout']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể thêm sản phẩm vào giỏ hàng.']);
        }
        exit();
    }


    /**
     * Hiển thị trang thanh toán
     *
     * Lấy các sản phẩm đã được chọn từ giỏ hàng để hiển thị trên trang thanh toán.
     * @return void
     */
    public function checkout(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);
        $userId = $_SESSION['user_id'];

        // Xử lý khi người dùng nhấn "Thanh toán" từ trang giỏ hàng hoặc "Mua ngay"
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedItemsJson = $_POST['selected_items'] ?? '[]';
            $selectedItemIds = json_decode($selectedItemsJson, true);

            if (empty($selectedItemIds) || !is_array($selectedItemIds)) {
                $_SESSION['error_message'] = 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.';
                header('Location: ' . BASE_URL . 'cart');
                exit();
            }

            // Lưu các sản phẩm được chọn vào session để trang checkout (GET) có thể hiển thị chúng
            $_SESSION['checkout_items'] = $selectedItemIds;
            // Chuyển hướng đến trang checkout bằng phương thức GET để hiển thị form thanh toán
            header('Location: ' . BASE_URL . 'checkout');
            exit();
        }

        // Lấy các sản phẩm đã chọn từ session để hiển thị
        $selectedItemIds = $_SESSION['checkout_items'] ?? [];
        if (empty($selectedItemIds)) {
            $_SESSION['error_message'] = 'Không có sản phẩm nào được chọn để thanh toán.';
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        // Lấy thông tin chi tiết của các sản phẩm được chọn
        $cartItems = $this->cartModel->getCartItemsByIds($selectedItemIds, $userId);

        // Tính tổng tiền
        $cartTotal = 0;
        foreach ($cartItems as $item) {
            $cartTotal += $item['price'] * $item['quantity'];
        }

        $this->view('orders/checkout', compact('cartItems', 'cartTotal'));
    }

    /**
     * Xử lý tạo đơn hàng mới
     *
     * Nhận thông tin từ form thanh toán, kiểm tra dữ liệu, tạo đơn hàng trong database,
     * và xóa các sản phẩm đã đặt khỏi giỏ hàng.
     * @return void
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'checkout');
            exit();
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);
        $userId = $_SESSION['user_id'];

        // Lấy danh sách sản phẩm cần thanh toán từ SESSION thay vì POST để bảo mật và ổn định hơn
        $selectedItemIds = $_SESSION['checkout_items'] ?? [];

        $cartTotal = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;

        if (empty($selectedItemIds) || $cartTotal <= 0) {
            $_SESSION['error_message'] = 'Không có sản phẩm nào được chọn hoặc tổng tiền không hợp lệ.';
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        $cartItems = $this->cartModel->getCartItemsByIds($selectedItemIds, $userId);

        // Kiểm tra xem tất cả các món ăn có còn hàng không
        $dishIds = array_column($cartItems, 'dish_id');
        $unavailableDishes = $this->dishModel->getUnavailableDishes($dishIds);

        if (!empty($unavailableDishes)) {
            $unavailableDishNames = array_column($unavailableDishes, 'name');
            $errorMessage = 'Các món sau không còn tồn tại hoặc đã hết: ' . implode(', ', $unavailableDishNames) . '. Vui lòng xóa chúng khỏi giỏ hàng và thử lại.';
            $_SESSION['error_message'] = $errorMessage;
            header('Location: ' . BASE_URL . 'cart');
            exit();
        }

        // Validate thông tin giao hàng
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

        // Chuẩn bị dữ liệu để tạo đơn hàng
        $orderData = [
            'total_price' => $cartTotal,
            'delivery_address' => $deliveryAddress,
            'phone' => $phone,
            'notes' => $notes
        ];

        // Tạo đơn hàng
        $orderId = $this->orderModel->createOrder($userId, $orderData, $cartItems);

        if ($orderId) {
            $_SESSION['success_message'] = 'Đã đặt hàng thành công!';
            // Xóa các sản phẩm đã đặt khỏi giỏ hàng
            $this->cartModel->removeItemsByIds($selectedItemIds);
            // Xóa các sản phẩm khỏi session checkout
            unset($_SESSION['checkout_items']);
            header('Location: ' . BASE_URL . 'order-confirmation?id=' . $orderId);
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.';
            header('Location: ' . BASE_URL . 'checkout');
        }
        exit();
    }

    /**
     * Hiển thị trang xác nhận đơn hàng
     *
     * @return void
     */
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

    /**
     * Hủy đơn hàng (AJAX)
     *
     * @return void
     */
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

    /**
     * Cập nhật trạng thái đơn hàng (AJAX, chỉ dành cho Admin)
     *
     * @return void
     */
    public function updateStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAuth(['Admin', 'PremiumUser']);


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

    /**
     * Kiểm tra quyền truy cập của người dùng
     *
     * @param array $allowedRoles Các vai trò được phép
     * @return void
     */
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

    /**
     * Trả về phản hồi dạng JSON
     *
     * @param array $data Dữ liệu cần trả về
     * @return void
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
