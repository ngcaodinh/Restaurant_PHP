document.addEventListener('DOMContentLoaded', function() {
    const resetEmailForm = document.getElementById('resetPasswordForm');
    const resetPasswordConfirmForm = document.getElementById('resetPasswordConfirmForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const resetBtn = document.getElementById('reset-email');
    const confirmBtn = document.getElementById('confirm-password');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const resetBtnText = document.getElementById('resetBtnText');
    const confirmBtnText = document.getElementById('confirmBtnText');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    // Kiểm tra phần tử cần thiết
    if (!errorMessage || !successMessage || !loadingSpinner) {
        console.error('Một hoặc nhiều phần tử cần thiết không được tìm thấy trong DOM.');
        return;
    }

    // Password toggle
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function validateEmailOrPhoneInput() {
        if (!emailInput) return false;
        const input = emailInput.value.trim().toLowerCase();
        const emailRegex = /^[a-z0-9._%+-]+@gmail\.com$/;
        const phoneRegex = /^(\+84|84|0)[3-9][0-9]{8}$/;

        if (emailRegex.test(input)) {
            emailInput.classList.remove('is-invalid');
            emailInput.classList.add('is-valid');
            return true;
        } else if (phoneRegex.test(input)) {
            const normalizedPhone = input.replace(/^\+84|84/, '0');
            if (normalizedPhone.length === 10) {
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
                return true;
            }
        }
        emailInput.classList.remove('is-valid');
        emailInput.classList.add('is-invalid');
        return false;
    }

    function validatePasswordInput() {
        if (!passwordInput) return false;
        const password = passwordInput.value;
        const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/;

        if (passwordRegex.test(password)) {
            passwordInput.classList.remove('is-invalid');
            passwordInput.classList.add('is-valid');
            return true;
        } else {
            passwordInput.classList.remove('is-valid');
            passwordInput.classList.add('is-invalid');
            return false;
        }
    }

    function validateConfirmPassword() {
        if (!confirmPasswordInput || !passwordInput) return false;
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password === confirmPassword && confirmPassword !== '') {
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
            return true;
        } else {
            confirmPasswordInput.classList.remove('is-valid');
            confirmPasswordInput.classList.add('is-invalid');
            return false;
        }
    }

    const debouncedValidateEmailOrPhone = debounce(validateEmailOrPhoneInput, 300);
    const debouncedValidatePassword = debounce(validatePasswordInput, 300);
    const debouncedValidateConfirmPassword = debounce(validateConfirmPassword, 300);

    if (emailInput) {
        emailInput.addEventListener('input', debouncedValidateEmailOrPhone);
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', debouncedValidatePassword);
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', debouncedValidateConfirmPassword);
    }

    function hideMessages() {
        errorMessage.classList.remove('show');
        successMessage.classList.remove('show');
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
    }

    function showError(message) {
        hideMessages();
        errorMessage.innerHTML = message.split('\n').map(line => `<p>${line}</p>`).join('');
        errorMessage.style.display = 'block';
        setTimeout(() => errorMessage.classList.add('show'), 10);
        autoHideMessage(errorMessage);
    }

    function showSuccess(message) {
        hideMessages();
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        setTimeout(() => successMessage.classList.add('show'), 10);
        autoHideMessage(successMessage);
    }

    function autoHideMessage(element) {
        if (element.style.display !== 'none') {
            setTimeout(() => {
                element.classList.remove('show');
                element.style.display = 'none';
            }, 5000);
        }
    }

    if (resetEmailForm) {
        resetEmailForm.addEventListener('submit', function(e) {
            const email = emailInput.value.trim().toLowerCase();

            if (!email) {
                e.preventDefault();
                showError('Vui lòng nhập email hoặc số điện thoại');
                return;
            }

            if (!validateEmailOrPhoneInput()) {
                e.preventDefault();
                showError('Email phải có đuôi @gmail.com hoặc số điện thoại phải đúng 10 số');
                return;
            }

            setLoadingState(true, resetBtn, resetBtnText, 'Đang kiểm tra...');
        });
    }

    if (resetPasswordConfirmForm) {
        resetPasswordConfirmForm.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (!password) {
                e.preventDefault();
                showError('Vui lòng nhập mật khẩu mới');
                return;
            }

            if (!validatePasswordInput()) {
                e.preventDefault();
                showError('Mật khẩu phải có ít nhất 8 ký tự, chứa chữ hoa, chữ thường, số và ký tự đặc biệt (!@#$%^&*)');
                return;
            }

            if (!validateConfirmPassword()) {
                e.preventDefault();
                showError('Mật khẩu xác nhận không khớp');
                return;
            }

            setLoadingState(true, confirmBtn, confirmBtnText, 'Đang cập nhật...');
        });
    }

    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', hideMessages);
    });

    function setLoadingState(isLoading, button, buttonText, loadingText) {
        button.disabled = isLoading;
        loadingSpinner.style.display = isLoading ? 'inline-block' : 'none';
        buttonText.textContent = isLoading ? loadingText : (button.id === 'reset-email' ? 'Tiếp tục' : 'Cập nhật mật khẩu');
    }
});