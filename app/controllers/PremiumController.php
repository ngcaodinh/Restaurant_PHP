<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Category;
use Database;

class PremiumController extends BaseController
{
    private User $userModel;
    private Dish $dishModel;
    private Order $orderModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->userModel = new User(Database::getInstance());
        $this->dishModel = new Dish(Database::getInstance());
        $this->orderModel = new Order(Database::getInstance());
        $this->categoryModel = new Category(Database::getInstance());
    }

    /**
     * Kiểm tra xác thực Premium User
     */
    private function checkPremiumAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'PremiumUser') {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    /**
     * Dashboard cho Premium User
     */
    public function dashboard(): void
    {
        $this->checkPremiumAuth();

        $dishStats = $this->dishModel->getDishStats();
        $orderStats = $this->orderModel->getOrderStats();

        $recentOrders = $this->orderModel->getAllOrders(10, 0);
        $monthlyRevenue = $this->orderModel->getMonthlyRevenue();

        $revenueLabels = json_encode(array_column($monthlyRevenue, 'month'));
        $revenueData = json_encode(array_column($monthlyRevenue, 'revenue'));

        // Prepare data for Order Status Chart
        $orderStatusLabels = json_encode(array_keys($orderStats['by_status'] ?? []));
        $orderStatusData = json_encode(array_values($orderStats['by_status'] ?? []));

        $this->view('premium/dashboard', compact('dishStats', 'orderStats', 'recentOrders', 'revenueLabels', 'revenueData', 'orderStatusLabels', 'orderStatusData'));
    }

    /**
     * Quản lý món ăn
     */
    public function dishes(): void
    {
        $this->checkPremiumAuth();

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [];
        $search = trim($_GET['search'] ?? '');
        $category_id = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if ($category_id) {
            $filters['category_id'] = $category_id;
        }

        $dishes = $this->dishModel->getAllDishes($limit, $offset, $filters);
        $categories = $this->categoryModel->getAllCategories();
        $dishStats = $this->dishModel->getDishStats();

        $this->view('premium/manage_dishes', compact('dishes', 'categories', 'dishStats', 'page'));
    }

    /**
     * Quản lý đơn hàng
     */
    public function orders(): void
    {
        $this->checkPremiumAuth();

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->getAllOrders($limit, $offset);
        $orderStats = $this->orderModel->getOrderStats();

        $this->view('premium/orders', compact('orders', 'orderStats', 'page'));
    }

    /**
     * API lấy chi tiết đơn hàng (JSON)
     */
    public function getOrderDetails(): void
    {
        $this->checkPremiumAuth();
        header('Content-Type: application/json');

        // Router sẽ đặt tham số {id} từ URL vào biến $_GET['id']
        $orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$orderId) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ hoặc bị thiếu.']);
            exit;
        }

        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) {
            http_response_code(404); // Not Found
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $order]);
        exit;
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus(): void
    {
        $this->checkPremiumAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');

        if (!$orderId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $validStatuses = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivered', 'Cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            exit;
        }

        $result = $this->orderModel->updateOrderStatus($orderId, $status);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái thất bại']);
        }
        exit;
    }

    /**
     * Thêm món ăn mới
     */
    public function addDish(): void
    {
        $this->checkPremiumAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Method not allowed';
            header('Location: ' . BASE_URL . 'premium/dishes');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $image_url = trim($_POST['image_url'] ?? '');

        if (empty($name) || !$price || !$category_id) {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin món ăn.';
            header('Location: ' . BASE_URL . 'premium/dishes');
            exit;
        }

        $result = $this->dishModel->createDish($name, $description, $price, $category_id, $image_url);

        if ($result) {
            $_SESSION['success'] = 'Thêm món ăn thành công!';
        } else {
            $_SESSION['error'] = 'Thêm món ăn thất bại.';
        }

        header('Location: ' . BASE_URL . 'premium/dishes');
        exit;
    }

    /**
     * Cập nhật món ăn
     */
    public function updateDish(): void
    {
        $this->checkPremiumAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Method not allowed';
            header('Location: ' . BASE_URL . 'premium/dishes');
            exit;
        }

        $dish_id = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $image_url = trim($_POST['image_url'] ?? '');

        if (!$dish_id || empty($name) || !$price || !$category_id) {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin món ăn.';
            header('Location: ' . BASE_URL . 'premium/dishes');
            exit;
        }

        $result = $this->dishModel->updateDish($dish_id, $name, $description, $price, $category_id, $image_url);

        if ($result) {
            $_SESSION['success'] = 'Cập nhật món ăn thành công!';
        } else {
            $_SESSION['error'] = 'Cập nhật món ăn thất bại.';
        }

        header('Location: ' . BASE_URL . 'premium/dishes');
        exit;
    }

    /**
     * Xóa món ăn
     */
    public function deleteDish(): void
    {
        $this->checkPremiumAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $dish_id = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);

        if (!$dish_id) {
            echo json_encode(['success' => false, 'message' => 'ID món ăn không hợp lệ']);
            exit;
        }

        $result = $this->dishModel->deleteDish($dish_id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Xóa món ăn thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa món ăn thất bại']);
        }
        exit;
    }
}
