<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Đăng nhập</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>

<body>
    <div class="background-overlay"></div>
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        include $headerPath;
        echo "<!-- Header loaded successfully -->";
    } else {
        echo "<p>Header not found at: " . htmlspecialchars($headerPath) . "</p>";
    }
    ?>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Chào mừng bạn trở lại CTUT Restaurant!</p>
            </div>

            <div id="error-message" class="error-message<?php echo (!empty($errors) || $authError) ? ' show' : ''; ?>">
                <?php
                if ($authError) {
                    echo '<p>' . htmlspecialchars($authError) . '</p>';
                }
                foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <div id="success-message" class="success-message<?php echo $success ? ' show' : ''; ?>">
                <?php echo htmlspecialchars($success); ?>
            </div>

            <form class="login-form" id="loginForm" method="POST" action="<?php echo BASE_URL; ?>login" novalidate>
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
                    <a href="<?php echo BASE_URL; ?>reset-password" class="forgot-password">Quên mật khẩu?</a>
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
                Chưa có tài khoản? <a href="<?php echo BASE_URL; ?>register">Đăng ký ngay</a>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/login.js"></script>
</body>

</html>