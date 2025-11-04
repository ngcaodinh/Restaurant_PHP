<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Đặt lại mật khẩu</title>
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
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="login-title">Đặt lại mật khẩu</h1>
                <p class="login-subtitle">Nhập mật khẩu mới cho tài khoản của bạn</p>
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
            <form class="login-form" id="resetPasswordForm" method="POST" action="<?php echo BASE_URL; ?>reset-password/update" novalidate>
                <!-- New Password Input -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-key me-2"></i>Mật khẩu mới
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Nhập mật khẩu mới" 
                               required 
                               autocomplete="new-password">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                    <small class="form-hint">
                        <i class="fas fa-info-circle me-1"></i>
                        Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt
                    </small>
                </div>

                <!-- Confirm Password Input -->
                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-check-double me-2"></i>Xác nhận mật khẩu
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-input" 
                               placeholder="Nhập lại mật khẩu mới" 
                               required 
                               autocomplete="new-password">
                        <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <!-- Password Strength Indicator -->
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBarFill"></div>
                    </div>
                    <p class="strength-text" id="strengthText">Độ mạnh mật khẩu</p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="login-button" id="submitBtn">
                    <div class="loading-spinner" id="loadingSpinner"></div>
                    <span id="submitBtnText">
                        <i class="fas fa-check-circle me-2"></i>Cập nhật mật khẩu
                    </span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="register-link">
                <a href="<?php echo BASE_URL; ?>login">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                </a>
            </div>

            <!-- Security Tips -->
            <div class="login-footer">
                <div class="security-tips">
                    <h4><i class="fas fa-lightbulb me-2"></i>Mẹo bảo mật</h4>
                    <ul>
                        <li><i class="fas fa-check me-2"></i>Sử dụng mật khẩu dài và phức tạp</li>
                        <li><i class="fas fa-check me-2"></i>Không chia sẻ mật khẩu với người khác</li>
                        <li><i class="fas fa-check me-2"></i>Thay đổi mật khẩu định kỳ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const strengthBarFill = document.getElementById('strengthBarFill');
            const strengthText = document.getElementById('strengthText');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                togglePasswordVisibility(passwordInput, togglePassword);
            });

            toggleConfirmPassword.addEventListener('click', function() {
                togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword);
            });

            function togglePasswordVisibility(input, icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const strength = checkPasswordStrength(password);
                updateStrengthIndicator(strength);
            });

            function checkPasswordStrength(password) {
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[!@#$%^&*]/.test(password)) strength++;

                return strength;
            }

            function updateStrengthIndicator(strength) {
                const colors = ['#dc2626', '#f59e0b', '#fbbf24', '#84cc16', '#22c55e', '#10b981'];
                const texts = ['Rất yếu', 'Yếu', 'Trung bình', 'Khá', 'Mạnh', 'Rất mạnh'];
                const widths = ['16%', '33%', '50%', '66%', '83%', '100%'];

                strengthBarFill.style.width = widths[strength] || '0%';
                strengthBarFill.style.backgroundColor = colors[strength] || '#e5e7eb';
                strengthText.textContent = texts[strength] || 'Độ mạnh mật khẩu';
                strengthText.style.color = colors[strength] || '#6b7280';
            }

            // Form validation
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                // Validate password
                if (!password) {
                    e.preventDefault();
                    showError('Vui lòng nhập mật khẩu mới');
                    return;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    showError('Mật khẩu phải có ít nhất 8 ký tự');
                    return;
                }

                if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || 
                    !/[0-9]/.test(password) || !/[!@#$%^&*]/.test(password)) {
                    e.preventDefault();
                    showError('Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (!@#$%^&*)');
                    return;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    showError('Mật khẩu xác nhận không khớp');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                loadingSpinner.style.display = 'block';
                submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang cập nhật...';
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

