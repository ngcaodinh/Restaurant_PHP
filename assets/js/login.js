/**
 * Tệp JavaScript xử lý trang đăng nhập
 * Xử lý validation form, hiển thị/ẩn mật khẩu, và đăng nhập Google
 */

document.addEventListener('DOMContentLoaded', function () {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const loginBtnText = document.getElementById('loginBtnText');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');
    const emailInput = document.getElementById('email');
    const googleLoginBtn = document.getElementById('googleLoginBtn');

    // Kiểm tra các phần tử cần thiết
    if (!loginForm || !emailInput || !passwordInput || !loginBtn || !loadingSpinner || !loginBtnText || !errorMessage || !successMessage) {
        console.error('Một hoặc nhiều phần tử form không được tìm thấy trong DOM.');
        return;
    }

    // Xử lý hiển thị/ẩn mật khẩu
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    /** Hàm debounce để giảm số lần gọi hàm */
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

    /** Validate email hoặc số điện thoại */
    function validateEmailOrPhoneInput() {
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

    const debouncedValidateEmailOrPhone = debounce(validateEmailOrPhoneInput, 300);

    emailInput.addEventListener('input', debouncedValidateEmailOrPhone);

    /** Ẩn tất cả thông báo */
    function hideMessages() {
        errorMessage.classList.remove('show');
        successMessage.classList.remove('show');
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
    }

    /** Hiển thị thông báo lỗi */
    function showError(message) {
        hideMessages();
        errorMessage.innerHTML = message.split('\n').map(line => `<p>${line}</p>`).join('');
        errorMessage.style.display = 'block';
        setTimeout(() => errorMessage.classList.add('show'), 10);
        autoHideMessage(errorMessage);
    }

    /** Hiển thị thông báo thành công */
    function showSuccess(message) {
        hideMessages();
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        setTimeout(() => successMessage.classList.add('show'), 10);
        autoHideMessage(successMessage);
    }

    /** Tự động ẩn thông báo sau 5 giây */
    function autoHideMessage(element) {
        if (element.style.display !== 'none') {
            setTimeout(() => {
                element.classList.remove('show');
                element.style.display = 'none';
            }, 5000);
        }
    }

    // Xử lý đăng nhập Google
    if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', function () {
            window.location.href = '/Restaurant_PHP/google_auth.php';
        });
    }

    // Xử lý submit form đăng nhập
    loginForm.addEventListener('submit', function (e) {
        const email = emailInput.value.trim().toLowerCase();
        const password = passwordInput.value;

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

        if (!password) {
            e.preventDefault();
            showError('Vui lòng nhập mật khẩu');
            return;
        }

        setLoadingState(true);
    });

    // Ẩn thông báo khi focus vào input
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', hideMessages);
    });

    /** Thiết lập trạng thái loading cho nút đăng nhập */
    function setLoadingState(isLoading) {
        loginBtn.disabled = isLoading;
        loadingSpinner.style.display = isLoading ? 'inline-block' : 'none';
        loginBtnText.textContent = isLoading ? 'Đang đăng nhập...' : 'Đăng nhập';
    }
});