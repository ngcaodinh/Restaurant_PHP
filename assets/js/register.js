document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirm-password');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('btn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const submitBtnText = document.getElementById('submitBtnText');
    const errorMessage = document.getElementById('error-message');
    const successMessage = document.getElementById('success-message');
    const passwordRequirements = document.getElementById('passwordRequirements');
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    const emailInput = document.getElementById('email');
    const googleBtn = document.getElementById('googleBtn');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    toggleConfirmPassword.addEventListener('click', function () {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

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

    function calculatePasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        if (checks.length) score += password.length >= 12 ? 40 : 20;
        if (checks.uppercase) score += 20;
        if (checks.lowercase) score += 20;
        if (checks.number) score += 20;
        if (checks.special) score += 20;
        if (password.length > 16) score += 10;
        if (/(.)\1{2,}/.test(password)) score -= 20;
        if (/[a-zA-Z]{4,}/.test(password)) score += 10;
        score = Math.min(Math.max(score, 0), 100);
        return { score, checks };
    }

    function updatePasswordStrength(password) {
        const { score, checks } = calculatePasswordStrength(password);
        passwordStrengthBar.className = 'password-strength-bar';
        passwordStrengthText.className = 'password-strength-text';
        if (password.length === 0) {
            passwordStrengthBar.style.width = '0%';
            passwordStrengthText.textContent = '';
            passwordRequirements.classList.remove('show');
            return;
        }
        passwordRequirements.classList.add('show');
        if (score <= 40) {
            passwordStrengthBar.classList.add('password-strength-weak');
            passwordStrengthText.classList.add('weak');
            passwordStrengthText.textContent = 'Mật khẩu yếu';
        } else if (score <= 80) {
            passwordStrengthBar.classList.add('password-strength-medium');
            passwordStrengthText.classList.add('medium');
            passwordStrengthText.textContent = 'Mật khẩu trung bình';
        } else {
            passwordStrengthBar.classList.add('password-strength-strong');
            passwordStrengthText.classList.add('strong');
            passwordStrengthText.textContent = 'Mật khẩu mạnh';
        }
        reqLength.className = checks.length ? 'valid' : 'invalid';
        reqUppercase.className = checks.uppercase ? 'valid' : 'invalid';
        reqLowercase.className = checks.lowercase ? 'valid' : 'invalid';
        reqNumber.className = checks.number ? 'valid' : 'invalid';
        reqSpecial.className = checks.special ? 'valid' : 'invalid';
    }

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        if (password && confirmPassword) {
            if (password === confirmPassword) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
            }
        } else {
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
        }
    }

    function validateEmailOrPhoneInput() {
        const input = emailInput.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
        const phoneRegex = /^(\+84|84|0)[3-9][0-9]{8}$/;
        if (emailRegex.test(input)) {
            emailInput.classList.remove('is-invalid');
            emailInput.classList.add('is-valid');
        } else if (phoneRegex.test(input)) {
            const normalizedPhone = input.replace(/\+84|84/, '0');
            if (normalizedPhone.length === 10) {
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
            } else {
                emailInput.classList.remove('is-valid');
                emailInput.classList.add('is-invalid');
            }
        } else {
            emailInput.classList.remove('is-valid');
            emailInput.classList.add('is-invalid');
        }
    }

    const debouncedUpdatePasswordStrength = debounce(updatePasswordStrength, 300);
    const debouncedValidateEmailOrPhone = debounce(validateEmailOrPhoneInput, 300);

    passwordInput.addEventListener('input', function () {
        debouncedUpdatePasswordStrength(this.value);
        checkPasswordMatch();
    });

    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    emailInput.addEventListener('input', debouncedValidateEmailOrPhone);

    function setLoadingState(isLoading) {
        registerBtn.disabled = isLoading;
        loadingSpinner.style.display = isLoading ? 'inline-block' : 'none';
        submitBtnText.textContent = isLoading ? 'Đang đăng ký...' : 'Đăng ký';
    }

    if (googleBtn) {
        googleBtn.addEventListener('click', function () {
            window.location.href = '/Restaurant_PHP/google_auth.php';
        });
    }

    registerForm.addEventListener('submit', function () {
        setLoadingState(true);
    });

    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            errorMessage.classList.remove('show');
            successMessage.classList.remove('show');
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
        });
    });
});