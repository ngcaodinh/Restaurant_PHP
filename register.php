<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $fullname = sanitize_input($_POST['fullname'] ?? '');
    $email_or_phone = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    $terms = isset($_POST['terms']);

    // Validate fullname
    if (empty($fullname)) {
        $errors[] = 'Vui lòng nhập họ và tên';
    } elseif (strlen($fullname) < 2) {
        $errors[] = 'Họ và tên phải có ít nhất 2 ký tự';
    }

    // Validate email or phone
    if (empty($email_or_phone)) {
        $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
    } else {
        $email_regex = '/^[a-zA-Z0-9._%+-]+@gmail\.com$/';
        $phone_regex = '/^(\+84|84|0)[3-9][0-9]{8}$/';
        if (preg_match($email_regex, $email_or_phone)) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND deleted_at IS NULL');
            $stmt->execute([$email_or_phone]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email đã được sử dụng';
            }
        } elseif (preg_match($phone_regex, $email_or_phone)) {
            $normalized_phone = preg_replace('/^\+84|84/', '0', $email_or_phone);
            if (strlen($normalized_phone) === 10) {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE phone = ? AND deleted_at IS NULL');
                $stmt->execute([$normalized_phone]);
                if ($stmt->fetchColumn() > 0) {
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
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }

    // Validate terms
    if (!$terms) {
        $errors[] = 'Vui lòng đồng ý với điều khoản dịch vụ';
    }

    // Process registration if no errors
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('
                INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, "User", "Active", NOW(), NOW())
            ');
            $email = preg_match($email_regex, $email_or_phone) ? $email_or_phone : null;
            $phone = preg_match($phone_regex, $email_or_phone) ? preg_replace('/^\+84|84/', '0', $email_or_phone) : null;
            $stmt->execute([$fullname, $email, $phone, $hashed_password]);
            $success = 'Đăng ký thành công! Đang chuyển hướng...';
            header('Refresh: 1.5; url=login.php');
        } catch (PDOException $e) {
            $errors[] = 'Đăng ký thất bại. Vui lòng thử lại sau.';
        }
    }
}

// Thiết lập biến cho layout
$page_title = 'CTUT Restaurant - Đăng ký';
$page_css = ['assets/css/register.css'];
$show_background_overlay = true;

// Nội dung trang
ob_start();
?>
<div class="register-container">
    <div class="register-box">
        <div class="register-header">
            <h1 class="register-title">Đăng ký</h1>
            <p class="register-subtitle">Khám phá ẩm thực đỉnh cao với CTUT!</p>
        </div>
        <div id="error-message" class="error-message<?php echo !empty($errors) ? ' show' : ''; ?>">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
        <div id="success-message" class="success-message<?php echo $success ? ' show' : ''; ?>">
            <?php echo $success; ?>
        </div>
        <form class="register-form" id="registerForm" method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label for="fullname" class="form-label">Họ và tên</label>
                <input type="text" id="fullname" name="fullname" class="form-input" placeholder="Nhập họ và tên" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required autocomplete="name">
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email hoặc Số điện thoại</label>
                <input type="text" id="email" name="email" class="form-input" placeholder="Nhập email hoặc số điện thoại" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Nhập mật khẩu" required autocomplete="new-password">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-strength-text" id="passwordStrengthText"></div>
                <ul class="password-requirements" id="passwordRequirements">
                    <li id="req-length">Ít nhất 8 ký tự</li>
                    <li id="req-uppercase">Chứa ít nhất 1 chữ in hoa</li>
                    <li id="req-lowercase">Chứa ít nhất 1 chữ thường</li>
                    <li id="req-number">Chứa ít nhất 1 số</li>
                    <li id="req-special">Chứa ít nhất 1 ký tự đặc biệt</li>
                </ul>
            </div>
            <div class="form-group">
                <label for="confirm-password" class="form-label">Xác nhận mật khẩu</label>
                <div style="position: relative;">
                    <input type="password" id="confirm-password" name="confirm-password" class="form-input" placeholder="Xác nhận mật khẩu" required autocomplete="new-password">
                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                </div>
            </div>
            <div class="form-options">
                <div class="terms-agree">
                    <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                    <label for="terms">Tôi đồng ý với <a href="terms.php">điều khoản dịch vụ</a></label>
                </div>
            </div>
            <button type="submit" class="register-button" id="btn">
                <div class="loading-spinner" id="loadingSpinner"></div>
                <span id="submitBtnText">Đăng ký</span>
            </button>
        </form>
        <div class="divider">Hoặc đăng ký bằng</div>
        <button type="button" class="google-register-btn" id="googleBtn">
            <div class="google-icon"></div>
            Đăng ký với Google
        </button>
        <div class="login-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_js = ['assets/js/header.js', 'assets/js/register.js'];
include 'templates/layout.php';
?>