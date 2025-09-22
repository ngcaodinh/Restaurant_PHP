<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$errors = [];
$success = '';
$step = 'input_email'; // Mặc định là bước nhập email/số điện thoại
$email_or_phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] === 'input_email') {
        $email_or_phone = sanitize_input(strtolower($_POST['email'] ?? '')); 
        if (empty($email_or_phone)) {
            $errors[] = 'Vui lòng nhập email hoặc số điện thoại';
        } else {
            $email_regex = '/^[a-z0-9._%+-]+@gmail\.com$/';
            $phone_regex = '/^(\+84|84|0)[3-9][0-9]{8}$/';
            $is_email = preg_match($email_regex, $email_or_phone);
            $is_phone = preg_match($phone_regex, $email_or_phone);

            if (!$is_email && !$is_phone) {
                $errors[] = 'Email phải có đuôi @gmail.com hoặc số điện thoại phải đúng 10 số';
            }
        }

        if (empty($errors)) {
            try {
                $query = 'SELECT id, email, phone FROM users 
                         WHERE (LOWER(email) = ? OR phone = ?) 
                         AND status = "Active" 
                         AND deleted_at IS NULL';
                $normalized_input = $is_phone ? preg_replace('/^\+84|84/', '0', $email_or_phone) : $email_or_phone;
                $stmt = $pdo->prepare($query);
                $stmt->execute([$normalized_input, $normalized_input]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Reset password input: $email_or_phone, Normalized: $normalized_input, User: " . print_r($user, true));

                if ($user) {
                    $step = 'reset_password'; // Chuyển sang bước nhập mật khẩu mới
                    $_SESSION['reset_email_or_phone'] = $normalized_input; // Lưu tạm để sử dụng ở bước sau
                } else {
                    $errors[] = 'Email hoặc số điện thoại không tồn tại hoặc tài khoản không hoạt động';
                }
            } catch (PDOException $e) {
                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
                error_log("Database error: " . $e->getMessage());
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] === 'reset_password' && isset($_SESSION['reset_email_or_phone'])) {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $email_or_phone = $_SESSION['reset_email_or_phone'];

        // Kiểm tra độ mạnh mật khẩu
        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu mới';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        } elseif (!preg_match('/[A-Z]/', $password) || 
                 !preg_match('/[a-z]/', $password) || 
                 !preg_match('/[0-9]/', $password) || 
                 !preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (!@#$%^&*)';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        if (empty($errors)) {
            try {
                $is_phone = preg_match('/^0[3-9][0-9]{8}$/', $email_or_phone);
                $query = 'SELECT id FROM users 
                         WHERE (' . ($is_phone ? 'phone' : 'LOWER(email)') . ' = ?) 
                         AND status = "Active" 
                         AND deleted_at IS NULL';
                $stmt = $pdo->prepare($query);
                $stmt->execute([$email_or_phone]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Cập nhật mật khẩu
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$hashed_password, $user['id']]);

                    // Xóa session
                    unset($_SESSION['reset_email_or_phone']);

                    $success = 'Mật khẩu đã được đặt lại thành công! Đang chuyển hướng đến trang đăng nhập...';
                    header('Refresh: 2; url=login.php');
                } else {
                    $errors[] = 'Tài khoản không tồn tại hoặc không hoạt động';
                    $step = 'input_email';
                    unset($_SESSION['reset_email_or_phone']);
                }
            } catch (PDOException $e) {
                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
                error_log("Database error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Đặt lại mật khẩu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/style.css">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/login.css">
</head>
<body>
    <div class="background-overlay"></div>
    <?php 
    try {
        include 'templates/header.php'; 
        echo "<!-- Header loaded successfully -->";
    } catch (Exception $e) {
        echo "<p>Lỗi tải header: " . htmlspecialchars($e->getMessage()) . "</p>";
        error_log("Header error: " . $e->getMessage());
    }
    ?>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1 class="login-title">Đặt lại mật khẩu</h1>
                <p class="login-subtitle"><?php echo $step === 'input_email' ? 'Nhập email hoặc số điện thoại để đặt lại mật khẩu' : 'Nhập mật khẩu mới cho tài khoản của bạn'; ?></p>
            </div>

            <div id="error-message" class="error-message<?php echo !empty($errors) ? ' show' : ''; ?>">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <div id="success-message" class="success-message<?php echo $success ? ' show' : ''; ?>">
                <?php echo htmlspecialchars($success); ?>
            </div>

            <?php if ($step === 'input_email'): ?>
            <form class="login-form" id="resetPasswordForm" method="POST" action="reset_password.php" novalidate>
                <input type="hidden" name="step" value="input_email">
                <div class="form-group">
                    <label for="email" class="form-label">Email hoặc Số điện thoại</label>
                    <input type="text" id="email" name="email" class="form-input" placeholder="Nhập email hoặc số điện thoại" value="<?php echo htmlspecialchars($email_or_phone); ?>" required autocomplete="username">
                </div>

                <button type="submit" class="login-button" id="resetBtn">
                    <div class="loading-spinner" id="loadingSpinner"></div>
                    <span id="resetBtnText">Tiếp tục</span>
                </button>
            </form>
            <?php else: ?>
            <form class="login-form" id="resetPasswordConfirmForm" method="POST" action="reset_password.php" novalidate>
                <input type="hidden" name="step" value="reset_password">
                <div class="form-group">
                    <label for="password" class="form-label">Mật khẩu mới</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Nhập mật khẩu mới" required autocomplete="new-password">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Xác nhận mật khẩu" required autocomplete="new-password">
                        <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <button type="submit" class="login-button" id="confirmBtn">
                    <div class="loading-spinner" id="loadingSpinner"></div>
                    <span id="confirmBtnText">Cập nhật mật khẩu</span>
                </button>
            </form>
            <?php endif; ?>

            <div class="register-link">
                <a href="login.php">Quay lại đăng nhập</a>
            </div>
        </div>
    </div>

    <script src="/Restaurant_PHP/assets/js/header.js"></script>
    <script src="/Restaurant_PHP/assets/js/reset_password.js"></script>
</body>
</html>