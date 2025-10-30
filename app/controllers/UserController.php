<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;
use App\Models\Order;
use Database;

class UserController extends BaseController
{
    private User $userModel;
    private Order $orderModel;

    public function __construct()
    {
        $this->userModel = new User(Database::getInstance());
        $this->orderModel = new Order(Database::getInstance());
    }

    public function index(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $recentOrders = $this->orderModel->getUserOrders($userId);

        // Limit to 5 most recent orders for dashboard
        $recentOrders = array_slice($recentOrders, 0, 5);

        $this->view('user/index', compact('user', 'recentOrders'));
    }

    public function profile(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $errors = $_SESSION['profile_errors'] ?? [];
        $success = $_SESSION['profile_success'] ?? '';

        unset($_SESSION['profile_errors'], $_SESSION['profile_success']);

        $this->view('user/profile', compact('user', 'errors', 'success'));
    }

    public function updateProfile(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/profile');
            exit();
        }

        $this->checkAuth(['Admin', 'User', 'PremiumUser']);

        $userId = $_SESSION['user_id'];

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $errors = [];

        // Validate input
        if (empty($name)) {
            $errors[] = 'Tên không được để trống';
        }

        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không đúng định dạng';
            } else {
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
                $phone = preg_replace('/^(\+84|84)/', '0', $phone);
                $existingUser = $this->userModel->findByPhone($phone);
                if ($existingUser && $existingUser['id'] != $userId) {
                    $errors[] = 'Số điện thoại đã được sử dụng bởi tài khoản khác';
                }
            }
        }

        $updateData = [
            'name' => $name,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'address' => $address ?: null,
        ];

        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            header('Location: ' . BASE_URL . 'user/profile');
            exit();
        }

        // We only update text fields here. Avatar is handled by a separate AJAX endpoint.
        if ($this->userModel->update($userId, $updateData)) {
            $_SESSION['user_name'] = $name;
            $_SESSION['profile_success'] = 'Cập nhật thông tin thành công';
        } else {
            $_SESSION['profile_success'] = 'Không có thông tin nào được thay đổi.';
        }

        header('Location: ' . BASE_URL . 'user/profile');
        exit();
    }

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

        // Verify current password
        $user = $this->userModel->findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->userModel->updatePassword($userId, $hashedPassword)) {
            $this->jsonResponse(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra khi đổi mật khẩu']);
        }
    }

    public function menu(): void
    {
        // This will be handled by HomeController for consistency
        header('Location: /');
        exit();
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
