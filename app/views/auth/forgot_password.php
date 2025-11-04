<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Quên mật khẩu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
</head>

<body>
    <!-- Background Overlay -->
    <div class="background-overlay"></div>
    
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    ?>

    <div class="login-container">
        <div class="login-box">
            <!-- Header -->
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="login-title">Quên mật khẩu</h1>
                <p class="login-subtitle">Nhập email hoặc số điện thoại để đặt lại mật khẩu</p>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="error-message show">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (!empty($success)): ?>
                <div class="success-message show">
                    <p><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form class="login-form" id="forgotPasswordForm" method="POST" action="<?php echo BASE_URL; ?>forgot-password/verify" novalidate>
                <!-- Email/Phone Input -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email hoặc Số điện thoại
                    </label>
                    <input type="text" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="Nhập email hoặc số điện thoại" 
                           required 
                           autocomplete="username">
                    <small class="form-hint">
                        <i class="fas fa-info-circle me-1"></i>
                        Email phải có đuôi @gmail.com hoặc số điện thoại 10 số
                    </small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-button" id="submitBtn">
                    <div class="loading-spinner" id="loadingSpinner"></div>
                    <span id="submitBtnText">
                        <i class="fas fa-arrow-right me-2"></i>Tiếp tục
                    </span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="register-link">
                <a href="<?php echo BASE_URL; ?>login">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                </a>
            </div>

            <!-- Additional Info -->
            <div class="login-footer">
                <div class="info-box">
                    <i class="fas fa-shield-alt"></i>
                    <p>Thông tin của bạn được bảo mật tuyệt đối</p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const emailInput = document.getElementById('email');

            // Form validation
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                
                if (!email) {
                    e.preventDefault();
                    showError('Vui lòng nhập email hoặc số điện thoại');
                    return;
                }

                // Email regex
                const emailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
                // Phone regex
                const phoneRegex = /^(\+84|84|0)[3-9][0-9]{8}$/;

                if (!emailRegex.test(email) && !phoneRegex.test(email)) {
                    e.preventDefault();
                    showError('Email phải có đuôi @gmail.com hoặc số điện thoại phải đúng 10 số');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                loadingSpinner.style.display = 'block';
                submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
            });

            // Show error message
            function showError(message) {
                const errorDiv = document.querySelector('.error-message') || createErrorDiv();
                errorDiv.innerHTML = `<p><i class="fas fa-exclamation-circle me-2"></i>${message}</p>`;
                errorDiv.classList.add('show');
                
                setTimeout(() => {
                    errorDiv.classList.remove('show');
                }, 5000);
            }

            // Create error div if not exists
            function createErrorDiv() {
                const div = document.createElement('div');
                div.className = 'error-message';
                form.parentNode.insertBefore(div, form);
                return div;
            }

            // Auto-hide messages after 5 seconds
            setTimeout(() => {
                const messages = document.querySelectorAll('.error-message.show, .success-message.show');
                messages.forEach(msg => msg.classList.remove('show'));
            }, 5000);
        });
    </script>
</body>

</html>

