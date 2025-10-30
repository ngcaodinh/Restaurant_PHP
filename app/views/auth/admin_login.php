<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Admin Login</title>
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
    }
    ?>

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1 class="login-title">Đăng nhập Quản trị</h1>
                <p class="login-subtitle">Dành riêng cho quản trị viên hệ thống</p>
            </div>

            <div id="error-message" class="error-message<?php echo (!empty($errors)) ? ' show' : ''; ?>">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>

            <form class="login-form" id="loginForm" method="POST" action="<?php echo BASE_URL; ?>admin/login" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">Email hoặc Số điện thoại</label>
                    <input type="text" id="email" name="email" class="form-input" placeholder="Nhập email hoặc số điện thoại" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="login-button" id="loginBtn">
                    <div class="loading-spinner" id="loadingSpinner"></div>
                    <span id="loginBtnText">Đăng nhập</span>
                </button>
            </form>

        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/login.js"></script>
</body>

</html>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>