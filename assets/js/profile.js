document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    if (!newPasswordInput) return; 

    const passwordStrengthBar = document.getElementById('password-strength-bar');
    const passwordStrengthText = document.getElementById('password-strength-text');
    const passwordRequirements = document.getElementById('password-requirements');
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    function calculatePasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };

        if (checks.length) score += 20;
        if (checks.uppercase) score += 20;
        if (checks.lowercase) score += 20;
        if (checks.number) score += 20;
        if (checks.special) score += 20;

        return { score, checks };
    }

    function updatePasswordStrengthUI(password) {
        if (!passwordStrengthBar || !passwordStrengthText || !passwordRequirements) return;

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
        passwordStrengthBar.style.width = score + '%';

        if (score <= 40) {
            passwordStrengthBar.classList.add('weak');
            passwordStrengthText.textContent = 'Mật khẩu yếu';
            passwordStrengthText.className += ' text-danger';
        } else if (score <= 80) {
            passwordStrengthBar.classList.add('medium');
            passwordStrengthText.textContent = 'Mật khẩu trung bình';
            passwordStrengthText.className += ' text-warning';
        } else {
            passwordStrengthBar.classList.add('strong');
            passwordStrengthText.textContent = 'Mật khẩu mạnh';
            passwordStrengthText.className += ' text-success';
        }

        // Update requirements list
        reqLength.classList.toggle('valid', checks.length);
        reqUppercase.classList.toggle('valid', checks.uppercase);
        reqLowercase.classList.toggle('valid', checks.lowercase);
        reqNumber.classList.toggle('valid', checks.number);
        reqSpecial.classList.toggle('valid', checks.special);
    }

    newPasswordInput.addEventListener('input', function() {
        updatePasswordStrengthUI(this.value);
    });

    // Toggle password visibility
    document.querySelectorAll('.password-toggle-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                const type = targetInput.getAttribute('type') === 'password' ? 'text' : 'password';
                targetInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            }
        });
    });
});
