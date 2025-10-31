<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Category;
use Database;

class AdminController extends BaseController
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

    public function dashboard(): void
    {
        $this->checkAdminAuth();

        $userStats = $this->userModel->getUserStats();
        $dishStats = $this->dishModel->getDishStats();
        $orderStats = $this->orderModel->getOrderStats();

        $recentOrders = $this->orderModel->getAllOrders(10, 0);
        $monthlyRevenue = $this->orderModel->getMonthlyRevenue();

        $revenueLabels = json_encode(array_column($monthlyRevenue, 'month'));
        $revenueData = json_encode(array_column($monthlyRevenue, 'revenue'));

        // Prepare data for Order Status Chart
        $orderStatusLabels = json_encode(array_keys($orderStats['by_status'] ?? []));
        $orderStatusData = json_encode(array_values($orderStats['by_status'] ?? []));

        $this->view('admin/dashboard', compact('userStats', 'dishStats', 'orderStats', 'recentOrders', 'revenueLabels', 'revenueData', 'orderStatusLabels', 'orderStatusData'));
    }

    public function users(): void
    {
        $this->checkAdminAuth();

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $users = $this->userModel->getAllUsers($limit, $offset);
        $userStats = $this->userModel->getUserStats();

        $this->view('admin/users', compact('users', 'userStats', 'page'));
    }

    public function dishes(): void
    {
        $this->checkAdminAuth();

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $dishes = $this->dishModel->getAllDishes($limit, $offset);
        $categories = $this->categoryModel->getAllCategories();
        $dishStats = $this->dishModel->getDishStats();

        $this->view('admin/manage_dishes', compact('dishes', 'categories', 'dishStats', 'page'));
    }

    public function orders(): void
    {
        $this->checkAdminAuth();

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->getAllOrders($limit, $offset);
        $orderStats = $this->orderModel->getOrderStats();

        $this->view('admin/orders', compact('orders', 'orderStats', 'page'));
    }

    public function createDish(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAdminAuth();

        $name = trim($_POST['name'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? 'Available');

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Tên món ăn không được để trống';
        }

        if ($price === false || $price <= 0) {
            $errors[] = 'Giá món ăn phải là số dương';
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
            return;
        }

        $dishData = [
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'image' => $image,
            'category_id' => $categoryId,
            'status' => $status
        ];

        $dishId = $this->dishModel->createDish($dishData);

        if ($dishId) {
            $this->jsonResponse(['success' => true, 'message' => 'Tạo món ăn thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi tạo món ăn']);
        }
    }

    public function updateDish(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAdminAuth();

        $dishId = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');

        if (!$dishId) {
            $this->jsonResponse(['success' => false, 'message' => 'ID món ăn không hợp lệ']);
            return;
        }

        $dishData = [];
        if (!empty($name)) $dishData['name'] = $name;
        if ($price !== false && $price > 0) $dishData['price'] = $price;
        if (!empty($description)) $dishData['description'] = $description;
        if (!empty($image)) $dishData['image'] = $image;
        if ($categoryId !== false) $dishData['category_id'] = $categoryId;
        if (!empty($status)) $dishData['status'] = $status;

        if ($this->dishModel->updateDish($dishId, $dishData)) {
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật món ăn thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi cập nhật món ăn']);
        }
    }

    public function deleteDish(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAdminAuth();

        $dishId = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);

        if (!$dishId) {
            $this->jsonResponse(['success' => false, 'message' => 'ID món ăn không hợp lệ']);
            return;
        }

        if ($this->dishModel->deleteDish($dishId)) {
            $this->jsonResponse(['success' => true, 'message' => 'Xóa món ăn thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi xóa món ăn']);
        }
    }

    public function updateUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAdminAuth();

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $status = trim($_POST['status'] ?? '');

        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'ID người dùng không hợp lệ']);
            return;
        }

        $userData = [];
        if (!empty($name)) $userData['name'] = $name;
        if (!empty($email)) $userData['email'] = $email;
        if (!empty($phone)) $userData['phone'] = $phone;
        if (!empty($role)) $userData['role'] = $role;
        if (!empty($status)) $userData['status'] = $status;

        if ($this->userModel->update($userId, $userData)) {
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật người dùng thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi cập nhật người dùng']);
        }
    }

    public function deleteUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAdminAuth();

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'ID người dùng không hợp lệ']);
            return;
        }

        // Prevent admin from deleting themselves
        if ($userId == $_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình']);
            return;
        }

        if ($this->userModel->deleteUser($userId)) {
            $this->jsonResponse(['success' => true, 'message' => 'Xóa người dùng thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi xóa người dùng']);
        }
    }

    private function checkAdminAuth(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            $_SESSION['error_message'] = 'Bạn không có quyền truy cập trang quản trị.';
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
