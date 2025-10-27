<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;
use Database;
use PDO;

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User(Database::getInstance());
    }

    public function showLogin(): void
    {
        $errors = $_SESSION['login_errors'] ?? [];
        $authError = $_SESSION['error_message'] ?? '';
        $success = '';
        unset($_SESSION['login_errors'], $_SESSION['error_message']);

        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $redirectUrl = match ($_SESSION['user_role']) {
                'Admin' => BASE_URL . 'admin',
                'PremiumUser' => BASE_URL,
                'User' => BASE_URL,
                default => BASE_URL
            };
            header("Location: $redirectUrl");
            exit();
        }

        $this->view('auth/login', compact('errors', 'authError', 'success'));
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $emailOrPhone = $this->sanitizeInput(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $errors = [];

        // Validate input
        if (empty($emailOrPhone)) {
            $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
        } else {
            $emailRegex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
            $phoneRegex = '/^(\+84|84|0)[3-9][0-9]{8}$/';

            $normalizedEmail = strtolower($emailOrPhone);
            $isEmail = preg_match($emailRegex, $normalizedEmail);
            $isPhone = preg_match($phoneRegex, $emailOrPhone);

            if (!$isEmail && !$isPhone) {
                $errors[] = 'Email hoặc số điện thoại không đúng định dạng';
            }
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }

        // Process login if no validation errors
        if (empty($errors)) {
            try {
                $normalizedInput = $isPhone ? preg_replace('/^(\+84|84)/', '0', $emailOrPhone) : $normalizedEmail;
                $user = $this->userModel->findByEmailOrPhone($normalizedInput);

                if ($user) {
                    if ($user['status'] != 'Active') {
                        $errors[] = 'Tài khoản của bạn hiện bị khóa. Vui lòng liên hệ quản trị viên.';
                    } elseif (!empty($user['google_id']) && empty($user['password'])) {
                        $errors[] = 'Tài khoản này được tạo bằng Google. Vui lòng đăng nhập bằng Google.';
                    } elseif (empty($user['password'])) {
                        $errors[] = 'Tài khoản chưa thiết lập mật khẩu. Vui lòng sử dụng chức năng quên mật khẩu.';
                    } elseif (password_verify($password, $user['password'])) {
                        // Successful login
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];

                        // Update last login
                        $this->userModel->updateLastLogin($user['id']);

                        // Handle remember me
                        if ($remember) {
                            $cookieLifetime = 7 * 24 * 3600; // 7 days
                            ini_set('session.cookie_lifetime', $cookieLifetime);
                            session_set_cookie_params([
                                'lifetime' => $cookieLifetime,
                                'path' => '/',
                                'secure' => isset($_SERVER['HTTPS']),
                                'httponly' => true,
                                'samesite' => 'Lax'
                            ]);
                        }

                        $success = 'Đăng nhập thành công! Đang chuyển hướng...';

                        $redirectUrl = match ($user['role']) {
                            'Admin' => BASE_URL . 'admin',
                            'PremiumUser' => BASE_URL,
                            'User' => BASE_URL,
                            default => BASE_URL
                        };

                        if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                            $redirectUrl = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                        }

                        header("Refresh: 1.5; url=$redirectUrl");
                        exit();
                    } else {
                        $errors[] = 'Email/số điện thoại hoặc mật khẩu không đúng.';
                    }
                } else {
                    $errors[] = 'Email/số điện thoại hoặc mật khẩu không đúng.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Đăng nhập thất bại: ' . $e->getMessage();
                error_log("Database error: " . $e->getMessage());
            }
        }

        $_SESSION['login_errors'] = $errors;
        header('Location: /login');
        exit();
    }

    public function showRegister(): void
    {
        $errors = [];
        $success = '';
        $this->view('auth/register', compact('errors', 'success'));
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit();
        }

        $fullname = $this->sanitizeInput($_POST['fullname'] ?? '');
        $emailOrPhone = $this->sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm-password'] ?? '';
        $terms = isset($_POST['terms']);
        $errors = [];
        $success = '';

        // Validate fullname
        if (empty($fullname)) {
            $errors[] = 'Vui lòng nhập họ và tên';
        } elseif (strlen($fullname) < 2) {
            $errors[] = 'Họ và tên phải có ít nhất 2 ký tự';
        }

        // Validate email or phone
        if (empty($emailOrPhone)) {
            $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
        } else {
            $emailRegex = '/^[a-zA-Z0-9._%+-]+@gmail\.com$/';
            $phoneRegex = '/^(\+84|84|0)[3-9][0-9]{8}$/';

            if (preg_match($emailRegex, $emailOrPhone)) {
                if ($this->userModel->findByEmail($emailOrPhone)) {
                    $errors[] = 'Email đã được sử dụng';
                }
            } elseif (preg_match($phoneRegex, $emailOrPhone)) {
                $normalizedPhone = preg_replace('/^\+84|84/', '0', $emailOrPhone);
                if (strlen($normalizedPhone) === 10) {
                    if ($this->userModel->findByPhone($normalizedPhone)) {
                        $errors[] = 'Số điện thoại đã được sử dụng';
                    }
                } else {
                    $errors[] = 'Số điện thoại phải đúng 10 số';
                }
            } else {
                $errors[] = 'Email phải có đuôi @gmail.com hoặc số điện thoại phải đúng 10 số';
            }
        }

        // Validate password
        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự, chứa chữ in hoa, chữ thường, số và ký tự đặc biệt';
        }

        // Validate confirm password
        if ($password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        // Validate terms
        if (!$terms) {
            $errors[] = 'Vui lòng đồng ý với điều khoản dịch vụ';
        }

        // Process registration if no errors
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $emailRegex = '/^[a-zA-Z0-9._%+-]+@gmail\.com$/';
                $phoneRegex = '/^(\+84|84|0)[3-9][0-9]{8}$/';

                $email = preg_match($emailRegex, $emailOrPhone) ? $emailOrPhone : null;
                $phone = preg_match($phoneRegex, $emailOrPhone) ? preg_replace('/^\+84|84/', '0', $emailOrPhone) : null;

                $userId = $this->userModel->create([
                    'name' => $fullname,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashedPassword,
                    'role' => 'User'
                ]);

                if ($userId) {
                    $success = 'Đăng ký thành công! Đang chuyển hướng...';
                    header('Refresh: 1.5; url=/login');
                    exit();
                } else {
                    $errors[] = 'Đăng ký thất bại. Vui lòng thử lại sau.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Đăng ký thất bại. Vui lòng thử lại sau.';
                error_log("Registration error: " . $e->getMessage());
            }
        }

        $this->view('auth/register', compact('errors', 'success'));
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /');
        exit();
    }

    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
