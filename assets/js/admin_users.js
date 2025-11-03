/**
 * Admin Users Management JavaScript
 * Handles user editing, updating, and UI interactions
 */

// Global variables
let editModal;
let successToast;
let errorToast;

/**
 * Initialize the page when DOM is loaded
 */
/**
 * Tệp JavaScript xử lý trang quản lý người dùng admin
 * Xử lý hiển thị, chỉnh sửa và xóa người dùng
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap modals
    const editModalElement = document.getElementById('editUserModal');
    if (editModalElement) {
        editModal = new bootstrap.Modal(editModalElement);
    }

    // Initialize Bootstrap toasts
    const successToastElement = document.getElementById('successToast');
    if (successToastElement) {
        successToast = new bootstrap.Toast(successToastElement);
    }

    const errorToastElement = document.getElementById('errorToast');
    if (errorToastElement) {
        errorToast = new bootstrap.Toast(errorToastElement);
    }

    // Initialize Material Dashboard form inputs
    initializeMaterialInputs();

    // Add password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function () {
            const passwordInput = document.getElementById('edit_password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility';
            }
        });
    }
});

/**
 * Initialize Material Dashboard form inputs
 */
function initializeMaterialInputs() {
    const inputs = document.querySelectorAll('.input-group-outline input');
    inputs.forEach(input => {
        // Add focus/blur event listeners
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('is-focused');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('is-focused');
            if (this.value) {
                this.parentElement.classList.add('is-filled');
            } else {
                this.parentElement.classList.remove('is-filled');
            }
        });

        // Check if input has value on load
        if (input.value) {
            input.parentElement.classList.add('is-filled');
        }
    });
}

/**
 * Open edit modal and populate with user data
 * @param {Object} user - User object containing user data
 */
function editUser(user) {
    // Set user ID
    document.getElementById('edit_user_id').value = user.id;

    // Set and mark name field as filled
    const nameInput = document.getElementById('edit_name');
    nameInput.value = user.name;
    nameInput.parentElement.classList.add('is-filled');

    // Set and mark email field as filled
    const emailInput = document.getElementById('edit_email');
    emailInput.value = user.email || '';
    if (user.email) {
        emailInput.parentElement.classList.add('is-filled');
    }

    // Set and mark phone field as filled
    const phoneInput = document.getElementById('edit_phone');
    phoneInput.value = user.phone || '';
    if (user.phone) {
        phoneInput.parentElement.classList.add('is-filled');
    }

    // Set role and status
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_status').value = user.status;

    // Clear password field
    const passwordInput = document.getElementById('edit_password');
    passwordInput.value = '';
    passwordInput.parentElement.classList.remove('is-filled');

    // Show modal
    if (editModal) {
        editModal.show();
    }
}

/**
 * Update user information via AJAX
 */
function updateUser(event) {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);

    // Validate form data
    const name = formData.get('name');
    const email = formData.get('email');
    const phone = formData.get('phone');

    if (!name || name.trim() === '') {
        showToast('error', 'Vui lòng nhập tên người dùng');
        return;
    }

    if (!email || email.trim() === '') {
        showToast('error', 'Vui lòng nhập email');
        return;
    }

    // Validate email format to end with @gmail.com
    const emailRegex = /^[^\s@]+@gmail\.com$/;
    if (!emailRegex.test(email)) {
        showToast('error', 'Email phải có định dạng @gmail.com');
        return;
    }

    // Validate phone number (must be 10 digits if provided)
    if (phone && !/^\d{10}$/.test(phone)) {
        showToast('error', 'Số điện thoại phải có đúng 10 chữ số');
        return;
    }

    // Validate password if it's being changed
    const password = formData.get('password');
    if (password) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/;
        if (!passwordRegex.test(password)) {
            showToast('error', 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.');
            return;
        }
    }

    // Disable submit button to prevent double submission
    const submitBtn = event.target;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

    // Send AJAX request
    fetch(`${BASE_URL}api/admin/user/update`, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
        .then(response => response.json())
        .then(data => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Lưu thay đổi';

            if (data.success) {
                // Hide modal
                if (editModal) {
                    editModal.hide();
                }

                // Show success toast
                showToast('success', data.message || 'Cập nhật người dùng thành công');

                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 900);
            } else {
                // Show error toast
                showToast('error', data.message || 'Có lỗi xảy ra khi cập nhật người dùng');
            }
        })
        .catch(error => {
            console.error('Error updating user:', error);

            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Lưu thay đổi';

            // Show error toast
            showToast('error', 'Có lỗi xảy ra khi kết nối đến server');
        });
}

/**
 * Show toast notification
 * @param {string} type - Type of toast ('success' or 'error')
 * @param {string} message - Message to display
 */
function showToast(type, message) {
    if (type === 'success' && successToast) {
        const toastBody = document.querySelector('#successToast .toast-body');
        if (toastBody) {
            toastBody.textContent = message;
        }
        successToast.show();
    } else if (type === 'error' && errorToast) {
        const toastBody = document.querySelector('#errorToast .toast-body');
        if (toastBody) {
            toastBody.textContent = message;
        }
        errorToast.show();
    }
}

