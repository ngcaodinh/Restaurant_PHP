<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$errors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];
$auth_error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success = '';
unset($_SESSION['login_errors']);
unset($_SESSION['error_message']); // Xóa sau khi lấy

// Kiểm tra nếu user đã đăng nhập
if (isset($_SESSION['user_id'])) {
    $redirect_url = match ($_SESSION['user_role']) {
        'Admin' => 'admin_dashboard.php',
        'PremiumUser' => 'index.php',
        'User' => 'index.php',
        default => 'index.php'
    };
    header("Location: $redirect_url");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = sanitize_input(trim($_POST['email'] ?? '')); // Thêm trim() để loại bỏ khoảng trắng
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email_or_phone)) {
        $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
    } else {
        // Cải thiện regex - chấp nhận nhiều domain email hơn
        $email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        $phone_regex = '/^(\+84|84|0)[3-9][0-9]{8}$/';

        // Chuẩn hóa email thành chữ thường
        $normalized_email = strtolower($email_or_phone);
        $is_email = preg_match($email_regex, $normalized_email);
        $is_phone = preg_match($phone_regex, $email_or_phone);

        if (!$is_email && !$is_phone) {
            $errors[] = 'Email hoặc số điện thoại không đúng định dạng';
        }
    }

    if (empty($password)) {
        $errors[] = 'Vui lòng nhập mật khẩu';
    }

    // Process login if no validation errors
    if (empty($errors)) {
        try {
            // Cải thiện query để phù hợp với DB schema - bỏ điều kiện status để kiểm tra sau
            $query = 'SELECT id, name, email, phone, password, role, status, google_id, last_login
                     FROM users 
                     WHERE (LOWER(email) = ? OR phone = ?) 
                     AND deleted_at IS NULL';

            // Chuẩn hóa input cho việc tìm kiếm
            if ($is_phone) {
                $normalized_input = preg_replace('/^(\+84|84)/', '0', $email_or_phone);
            } else {
                $normalized_input = $normalized_email;
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute([$normalized_input, $normalized_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) { // Đầu tiên, đảm bảo tìm thấy người dùng
                // Kiểm tra status trước tiên
                if ($user['status'] != 'Active') {
                    $errors[] = 'Tài khoản của bạn hiện bị khóa. Vui lòng liên hệ quản trị viên.';
                }
                // Kiểm tra nếu tài khoản là Google account (có google_id nhưng không có password)
                elseif (!empty($user['google_id']) && empty($user['password'])) {
                    $errors[] = 'Tài khoản này được tạo bằng Google. Vui lòng đăng nhập bằng Google.';
                } elseif (empty($user['password'])) {
                    $errors[] = 'Tài khoản chưa thiết lập mật khẩu. Vui lòng sử dụng chức năng quên mật khẩu.';
                } elseif (password_verify($password, $user['password'])) {
                    // Tài khoản đang hoạt động và mật khẩu đúng
                    session_regenerate_id(true); // Tái tạo ID phiên để tăng cường bảo mật
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];

                    // Cập nhật thời gian đăng nhập cuối cùng
                    $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
                    $stmt->execute([$user['id']]);

                    // Xử lý "ghi nhớ đăng nhập" (remember me)
                    if ($remember) {
                        $cookie_lifetime = 7 * 24 * 3600; // 7 ngày
                        ini_set('session.cookie_lifetime', $cookie_lifetime);
                        session_set_cookie_params([
                            'lifetime' => $cookie_lifetime,
                            'path' => '/',
                            'secure' => isset($_SERVER['HTTPS']),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    // Thiết lập thông báo và chuyển hướng người dùng
                    $success = 'Đăng nhập thành công! Đang chuyển hướng...';

                    // Cải thiện logic redirect - bao gồm cả User role
                    $redirect_url = match ($user['role']) {
                        'Admin' => 'admin_dashboard.php',
                        'PremiumUser' => 'index.php',
                        'User' => 'index.php',
                        default => 'index.php'
                    };

                    // Kiểm tra redirect URL từ session nếu có
                    if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                    }

                    header("Refresh: 1.5; url=$redirect_url");
                    exit(); //dừng việc thực thi script sau khi chuyển hướng
                } else {
                    $errors[] = 'Email/số điện thoại hoặc mật khẩu không đúng.';
                }
            } else {
                // Không tìm thấy người dùng với email/số điện thoại đã nhập
                $errors[] = 'Email/số điện thoại hoặc mật khẩu không đúng.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Đăng nhập thất bại: ' . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Thiết lập biến cho layout
$page_title = 'CTUT Restaurant - Đăng nhập';
$page_css = ['assets/css/login.css'];
$show_background_overlay = true;

// Nội dung trang
ob_start();
?>

<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h1 class="login-title">Đăng nhập</h1>
            <p class="login-subtitle">Chào mừng bạn trở lại CTUT Restaurant!</p>
        </div>

        <div id="error-message" class="error-message<?php echo (!empty($errors) || $auth_error) ? ' show' : ''; ?>">
            <?php
            if ($auth_error) {
                echo '<p>' . htmlspecialchars($auth_error) . '</p>';
            }
            foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <div id="success-message" class="success-message<?php echo $success ? ' show' : ''; ?>">
            <?php echo htmlspecialchars($success); ?>
        </div>

        <form class="login-form" id="loginForm" method="POST" action="login.php" novalidate>
            <div class="form-group">
                <label for="email" class="form-label">Email hoặc Số điện thoại</label>
                <input type="text" id="email" name="email" class="form-input" placeholder="Nhập email hoặc số điện thoại" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <label for="remember">Ghi nhớ tôi</label>
                </div>
                <a href="reset_password.php" class="forgot-password">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="login-button" id="loginBtn">
                <div class="loading-spinner" id="loadingSpinner"></div>
                <span id="loginBtnText">Đăng nhập</span>
            </button>
        </form>

        <div class="divider">Hoặc đăng nhập bằng</div>

        <button type="button" class="google-login" id="googleLoginBtn">
            <div class="google-icon"></div>
            Đăng nhập với Google
        </button>

        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_js = ['assets/js/header.js', 'assets/js/login.js'];
include 'templates/layout.php';
?>