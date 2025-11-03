<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;
use App\Models\Order;
use Database;

/**
 * Class UserController - Controller xử lý các chức năng liên quan đến người dùng
 *
 * Quản lý trang tổng quan, hồ sơ, cập nhật thông tin và đổi mật khẩu của người dùng.
 */
class UserController extends BaseController
{
    /** @var User Model User */
    private User $userModel;
    /** @var Order Model Order */
    private Order $orderModel;

    /**
     * Constructor khởi tạo các model cần thiết
     */
    public function __construct()
    {
        $db = Database::getInstance();
        $this->userModel = new User($db);
        $this->orderModel = new Order($db);
    }

    /**
     * Hiển thị trang tổng quan của người dùng
     *
     * Lấy thông tin người dùng và 5 đơn hàng gần nhất để hiển thị.
     * @return void
     */
    public function index(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $recentOrders = $this->orderModel->getUserOrders($userId);

        // Giới hạn 5 đơn hàng gần nhất cho trang tổng quan
        $recentOrders = array_slice($recentOrders, 0, 5);

        $this->view('user/index', compact('user', 'recentOrders'));
    }

    /**
     * Hiển thị trang hồ sơ cá nhân
     *
     * Lấy thông tin người dùng và các thông báo lỗi/thành công từ session.
     * @return void
     */
    public function profile(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $errors = $_SESSION['profile_errors'] ?? [];
        $success = $_SESSION['profile_success'] ?? '';

        // Xóa các thông báo sau khi đã lấy
        unset($_SESSION['profile_errors'], $_SESSION['profile_success']);

        $this->view('user/profile', compact('user', 'errors', 'success'));
    }

    /**
     * Xử lý cập nhật thông tin hồ sơ
     *
     * Validate dữ liệu từ form và cập nhật thông tin người dùng vào database.
     * @return void
     */
    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/profile');
            exit();
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];

        // Lấy và làm sạch dữ liệu từ form
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $errors = [];

        // Validate dữ liệu
        if (empty($name)) {
            $errors[] = 'Tên không được để trống';
        }

        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không đúng định dạng';
            } else {
                // Kiểm tra email đã tồn tại chưa
                $existingUser = $this->userModel->findByEmail($email);
                if ($existingUser && $existingUser['id'] != $userId) {
                    $errors[] = 'Email đã được sử dụng bởi tài khoản khác';
                }
            }
        }

        if (!empty($phone)) {
            if (!preg_match('/^(\+84|84|0)[3-9][0-9]{8}$/', $phone)) {
                $errors[] = 'Số điện thoại không đúng định dạng';
            } else {
                // Chuẩn hóa SĐT và kiểm tra tồn tại
                $phone = preg_replace('/^(\+84|84)/', '0', $phone);
                $existingUser = $this->userModel->findByPhone($phone);
                if ($existingUser && $existingUser['id'] != $userId) {
                    $errors[] = 'Số điện thoại đã được sử dụng bởi tài khoản khác';
                }
            }
        }

        // Chuẩn bị dữ liệu để cập nhật
        $updateData = [
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'address' => $address ?: null,
        ];

        // Nếu có lỗi, lưu vào session và quay lại trang profile
        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            header('Location: ' . BASE_URL . 'user/profile');
            exit();
        }

        // Chỉ cập nhật các trường text, avatar được xử lý riêng qua AJAX
        if ($this->userModel->update($userId, $updateData)) {
            $_SESSION['user_name'] = $name; // Cập nhật tên trong session
            $_SESSION['profile_success'] = 'Cập nhật thông tin thành công';
        } else {
            $_SESSION['profile_success'] = 'Không có thông tin nào được thay đổi.';
        }

        header('Location: ' . BASE_URL . 'user/profile');
        exit();
    }

    /**
     * Xử lý thay đổi mật khẩu (AJAX)
     *
     * Validate mật khẩu hiện tại và mật khẩu mới, sau đó cập nhật vào database.
     * @return void
     */
    public function changePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        // Validate dữ liệu
        if (empty($currentPassword)) {
            $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
        }

        if (empty($newPassword)) {
            $errors[] = 'Vui lòng nhập mật khẩu mới';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 8 ký tự, chứa chữ in hoa, chữ thường, số và ký tự đặc biệt';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
            return;
        }

        // Xác minh mật khẩu hiện tại
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
            return;
        }

        // Cập nhật mật khẩu mới
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->userModel->updatePassword($userId, $hashedPassword)) {
            $this->jsonResponse(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra khi đổi mật khẩu']);
        }
    }

    /**
     * Chuyển hướng đến trang menu chính
     *
     * @return void
     */
    public function menu(): void
    {
        // Chức năng này được xử lý bởi HomeController để đảm bảo tính nhất quán
        header('Location: /');
        exit();
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
