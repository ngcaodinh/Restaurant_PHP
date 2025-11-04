<?php

namespace App\Controllers;


use Core\BaseController;
use App\Models\User;
use Database;
use PDO;

/**
 * Class AuthController - Controller xử lý xác thực người dùng
 *
 * Controller này xử lý các chức năng liên quan đến xác thực như:
 * đăng nhập, đăng ký, đăng xuất, và đăng nhập admin.
 */
class AuthController extends BaseController
{
    /**
     * Model User để thao tác với dữ liệu người dùng
     * @var User
     */
    private User $userModel;

    /**
     * Constructor khởi tạo UserModel
     */
    public function __construct()
    {
        $this->userModel = new User(Database::getInstance());
    }

    /**
     * Hiển thị trang đăng nhập
     *
     * Nếu người dùng đã đăng nhập, chuyển hướng đến trang phù hợp với vai trò.
     *
     * @return void
     */
    public function showLogin(): void
    {
        // Lấy thông báo lỗi từ session (nếu có)
        $errors = $_SESSION['login_errors'] ?? [];
        $authError = $_SESSION['error_message'] ?? '';
        $success = $_SESSION['login_success'] ?? '';
        unset($_SESSION['login_errors'], $_SESSION['error_message'], $_SESSION['login_success']);

        // Nếu đã đăng nhập, chuyển hướng đến trang phù hợp
        if (isset($_SESSION['user_id'])) {
            $redirectUrl = match ($_SESSION['user_role']) {
                'Admin' => BASE_URL . 'admin',
                'PremiumUser' => BASE_URL . 'premium/dashboard',
                'User' => BASE_URL,
                default => BASE_URL
            };
            header("Location: $redirectUrl");
            exit();
        }

        // Hiển thị trang đăng nhập
        $this->view('auth/login', compact('errors', 'authError', 'success'));
    }

    /**
     * Xử lý đăng nhập người dùng
     *
     * Phương thức này xác thực thông tin đăng nhập (email/SĐT và mật khẩu),
     * kiểm tra trạng thái tài khoản, và tạo session nếu đăng nhập thành công.
     *
     * @return void
     */
    public function login(): void
    {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        // Lấy dữ liệu từ form
        $emailOrPhone = $this->sanitizeInput(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $errors = [];

        // Validate dữ liệu đầu vào
        if (empty($emailOrPhone)) {
            $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
        } else {
            // Regex kiểm tra định dạng email và số điện thoại
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

        // Xử lý đăng nhập nếu không có lỗi validation
        if (empty($errors)) {
            try {
                // Chuẩn hóa input (chuyển số điện thoại về dạng 0xxx)
                $normalizedInput = $isPhone ? preg_replace('/^(\+84|84)/', '0', $emailOrPhone) : $normalizedEmail;
                $user = $this->userModel->findByEmailOrPhone($normalizedInput);

                if ($user) {
                    // Kiểm tra trạng thái tài khoản
                    if ($user['status'] != 'Active') {
                        $errors[] = 'Tài khoản của bạn hiện bị khóa. Vui lòng liên hệ quản trị viên.';
                    } elseif (!empty($user['google_id']) && empty($user['password'])) {
                        $errors[] = 'Tài khoản này được tạo bằng Google. Vui lòng đăng nhập bằng Google.';
                    } elseif (empty($user['password'])) {
                        $errors[] = 'Tài khoản chưa thiết lập mật khẩu. Vui lòng sử dụng chức năng quên mật khẩu.';
                    } elseif (password_verify($password, $user['password'])) {
                        // Đăng nhập thành công
                        // Tạo session ID mới để bảo mật
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];

                        // Cập nhật thời gian đăng nhập cuối
                        $this->userModel->updateLastLogin($user['id']);

                        // Xử lý chức năng "Ghi nhớ đăng nhập"
                        if ($remember) {
                            $cookieLifetime = 7 * 24 * 3600; // 7 ngày
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

                        // Xác định URL chuyển hướng dựa trên vai trò
                        $redirectUrl = match ($user['role']) {
                            'Admin' => BASE_URL . 'admin',
                            'PremiumUser' => BASE_URL . 'premium/dashboard',
                            'User' => BASE_URL,
                            default => BASE_URL
                        };

                        // Nếu có URL chuyển hướng được lưu trước đó, sử dụng nó
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


    public function showAdminLogin(): void
    {
        // Redirect if already logged in as admin
        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'Admin') {
            header("Location: " . BASE_URL . 'admin/dashboard');
            exit();
        }

        $errors = $_SESSION['admin_login_errors'] ?? [];
        unset($_SESSION['admin_login_errors']);

        $this->view('auth/admin_login', compact('errors'));
    }

    public function adminLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/login');
            exit();
        }

        $emailOrPhone = $this->sanitizeInput(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (empty($emailOrPhone)) {
            $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }

        if (empty($errors)) {
            try {
                $user = $this->userModel->findByEmailOrPhone($emailOrPhone);

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['role'] !== 'Admin') {
                        $errors[] = 'Truy cập bị từ chối. Tài khoản không có quyền quản trị.';
                    } elseif ($user['status'] != 'Active') {
                        $errors[] = 'Tài khoản của bạn đã bị khóa.';
                    } else {
                        // Successful admin login
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_role'] = $user['role'];

                        $this->userModel->updateLastLogin($user['id']);

                        header('Location: ' . BASE_URL . 'admin/dashboard');
                        exit();
                    }
                } else {
                    $errors[] = 'Email/số điện thoại hoặc mật khẩu không đúng.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
                error_log("Admin login error: " . $e->getMessage());
            }
        }

        $_SESSION['admin_login_errors'] = $errors;
        header('Location: /admin/login');
        exit();
    }

    /**
     * Hiển thị trang quên mật khẩu (Bước 1: Nhập email/SĐT)
     *
     * @return void
     */
    public function showForgotPassword(): void
    {
        $errors = $_SESSION['forgot_password_errors'] ?? [];
        $success = $_SESSION['forgot_password_success'] ?? '';
        unset($_SESSION['forgot_password_errors'], $_SESSION['forgot_password_success']);

        $this->view('auth/forgot_password', compact('errors', 'success'));
    }

    /**
     * Xử lý xác minh email/SĐT (Bước 1)
     *
     * @return void
     */
    public function verifyAccount(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit();
        }

        $emailOrPhone = $this->sanitizeInput(strtolower($_POST['email'] ?? ''));
        $errors = [];

        // Validate input
        if (empty($emailOrPhone)) {
            $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
        } else {
            $emailRegex = '/^[a-z0-9._%+-]+@gmail\.com$/';
            $phoneRegex = '/^(\+84|84|0)[3-9][0-9]{8}$/';
            $isEmail = preg_match($emailRegex, $emailOrPhone);
            $isPhone = preg_match($phoneRegex, $emailOrPhone);

            if (!$isEmail && !$isPhone) {
                $errors[] = 'Email phải có đuôi @gmail.com hoặc số điện thoại phải đúng 10 số';
            }
        }

        // Nếu không có lỗi validation, kiểm tra tài khoản trong DB
        if (empty($errors)) {
            try {
                // Chuẩn hóa input
                $normalizedInput = $isPhone ? preg_replace('/^\+84|84/', '0', $emailOrPhone) : $emailOrPhone;
                $user = $this->userModel->findByEmailOrPhone($normalizedInput);

                if ($user && $user['status'] === 'Active' && empty($user['deleted_at'])) {
                    // Lưu thông tin vào session để dùng ở bước 2
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_email_or_phone'] = $normalizedInput;

                    // Chuyển sang bước 2
                    header('Location: ' . BASE_URL . 'reset-password');
                    exit();
                } else {
                    $errors[] = 'Email hoặc số điện thoại không tồn tại hoặc tài khoản không hoạt động';
                }
            } catch (\Exception $e) {
                $errors[] = 'Lỗi hệ thống. Vui lòng thử lại sau.';
                error_log("Verify account error: " . $e->getMessage());
            }
        }

        $_SESSION['forgot_password_errors'] = $errors;
        header('Location: ' . BASE_URL . 'forgot-password');
        exit();
    }

    /**
     * Hiển thị trang đặt lại mật khẩu (Bước 2: Nhập mật khẩu mới)
     *
     * @return void
     */
    public function showResetPassword(): void
    {
        // Kiểm tra xem đã xác minh tài khoản chưa
        if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_email_or_phone'])) {
            header('Location: ' . BASE_URL . 'forgot-password');
            exit();
        }

        $errors = $_SESSION['reset_password_errors'] ?? [];
        $success = $_SESSION['reset_password_success'] ?? '';
        unset($_SESSION['reset_password_errors'], $_SESSION['reset_password_success']);

        $this->view('auth/reset_password', compact('errors', 'success'));
    }

    /**
     * Xử lý đặt lại mật khẩu (Bước 2)
     *
     * @return void
     */
    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'reset-password');
            exit();
        }

        // Kiểm tra session
        if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_email_or_phone'])) {
            header('Location: ' . BASE_URL . 'forgot-password');
            exit();
        }

        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['reset_user_id'];
        $errors = [];

        // Validate mật khẩu mới
        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu mới';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        } elseif (
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[!@#$%^&*]/', $password)
        ) {
            $errors[] = 'Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (!@#$%^&*)';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        // Nếu không có lỗi, cập nhật mật khẩu
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updated = $this->userModel->updatePassword($userId, $hashedPassword);

                if ($updated) {
                    // Xóa session sau khi hoàn tất
                    unset($_SESSION['reset_user_id'], $_SESSION['reset_email_or_phone']);

                    $_SESSION['login_success'] = 'Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập.';
                    header('Location: ' . BASE_URL . 'login');
                    exit();
                } else {
                    $errors[] = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Lỗi hệ thống. Vui lòng thử lại sau.';
                error_log("Reset password error: " . $e->getMessage());
            }
        }

        $_SESSION['reset_password_errors'] = $errors;
        header('Location: ' . BASE_URL . 'reset-password');
        exit();
    }

    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
