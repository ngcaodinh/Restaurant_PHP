<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Đăng ký</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/register.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
</head>

<body>
    <div class="background-overlay"></div>
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        include $headerPath;
    }
    ?>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1 class="register-title">Đăng ký</h1>
                <p class="register-subtitle">Khám phá ẩm thực đỉnh cao với CTUT!</p>
            </div>
            <div id="error-message" class="error-message<?php echo !empty($errors) ? ' show' : ''; ?>">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <div id="success-message" class="success-message<?php echo $success ? ' show' : ''; ?>">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <form class="register-form" id="registerForm" method="POST" action="<?php echo BASE_URL; ?>register" novalidate>
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
                        <label for="terms">Tôi đồng ý với <a href="/terms">điều khoản dịch vụ</a></label>
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
                Đã có tài khoản? <a href="<?php echo BASE_URL; ?>login">Đăng nhập ngay</a>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/register.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>
</body>

</html>