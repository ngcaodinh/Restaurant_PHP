document.addEventListener('DOMContentLoaded', function () {
    // Toggle hamburger menu
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            const menuNav = document.querySelector('.menu-nav');
            if (menuNav) {
                menuNav.classList.toggle('active');
            }
        });
    }

    // Toggle user menu
    function toggleUserMenu() {
        const userMenu = document.getElementById('userMenu');
        if (userMenu) {
            userMenu.classList.toggle('show');
        }
    }

    const userIcon = document.querySelector('.nav-icon.fa-user');
    if (userIcon) {
        userIcon.addEventListener('click', toggleUserMenu);
    }

    document.addEventListener('click', (e) => {
        const userIcon = document.querySelector('.nav-icon.fa-user');
        const userMenu = document.getElementById('userMenu');
        if (userIcon && userMenu && !userIcon.contains(e.target) && !userMenu.contains(e.target)) {
            userMenu.classList.remove('show');
        }
    });

    // Modal functions
    function openAddModal() {
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const customerForm = document.getElementById('customerForm');
        const customerId = document.getElementById('customerId');
        const customerModal = document.getElementById('customerModal');
        if (modalTitle && formAction && customerForm && customerId && customerModal) {
            modalTitle.textContent = 'Thêm người dùng mới';
            formAction.value = 'add';
            customerForm.reset();
            customerId.value = '';
            customerModal.classList.add('show');
        }
    }

window.editCustomer = function(id) {
    fetch(`admin_dashboard.php?action=get_user&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Chỉnh sửa thông tin người dùng';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('customerId').value = data.user.id;
                document.getElementById('customerName').value = data.user.name || '';
                document.getElementById('customerEmail').value = data.user.email || '';
                document.getElementById('customerPhone').value = data.user.phone || '';
                document.getElementById('customerAddress').value = data.user.address || '';
                document.getElementById('customerRole').value = data.user.role;
                document.getElementById('customerStatus').value = data.user.status;
                document.getElementById('customerPassword').value = ''; // Không điền mật khẩu hiện tại
                document.getElementById('customerPassword').removeAttribute('required'); // Xóa thuộc tính required
                document.getElementById('customerModal').classList.add('show');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(() => showNotification('Lỗi khi lấy thông tin người dùng', 'error'));
};

    window.deleteCustomer = function (id) {
        if (confirm('Bạn có chắc chắn muốn xóa người dùng này?\nHành động này không thể hoàn tác.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    window.viewCustomer = function (id) {
        fetch(`admin_dashboard.php?action=get_user&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    const info = `
                        Thông tin chi tiết người dùng:
                        Tên: ${user.name}
                        Email: ${user.email}
                        SĐT: ${user.phone || 'Chưa cập nhật'}
                        Địa chỉ: ${user.address || 'Chưa cập nhật'}
                        Vai trò: ${user.role === 'Admin' ? 'Quản trị' : user.role === 'PremiumUser' ? 'Premium' : 'Thường'}
                        Trạng thái: ${user.status === 'Active' ? 'Hoạt động' : 'Không hoạt động'}
                        Ngày đăng ký: ${new Date(user.created_at).toLocaleDateString('vi-VN')}
                        Lần đăng nhập cuối: ${user.last_login ? new Date(user.last_login).toLocaleDateString('vi-VN') : 'Chưa đăng nhập'}
                    `;
                    alert(info);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(() => showNotification('Lỗi khi lấy thông tin người dùng', 'error'));
    }

    // Notification function
    function showNotification(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        if (toast && toastMessage) {
            toastMessage.textContent = message;
            toast.className = `toast ${type} show`;
            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }
    }

    // Placeholder for toggleCart and toggleWishlist
    window.toggleCart = function () {
        console.log('Toggle cart function called');
        // Add actual cart toggle logic here
    };

    window.toggleWishlist = function () {
        console.log('Toggle wishlist function called');
        // Add actual wishlist toggle logic here
    };

    // Event listeners
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', openAddModal);
    }

    const closeModalBtn = document.getElementById('closeModal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            const customerModal = document.getElementById('customerModal');
            if (customerModal) {
                customerModal.classList.remove('show');
            }
        });
    }

    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            const customerModal = document.getElementById('customerModal');
            if (customerModal) {
                customerModal.classList.remove('show');
            }
        });
    }

    const customerModal = document.getElementById('customerModal');
    if (customerModal) {
        customerModal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const customerModal = document.getElementById('customerModal');
            if (customerModal) {
                customerModal.classList.remove('show');
            }
        }
    });

    // Use window.appConfig for URL parameters
    const searchInput = document.getElementById('userSearchInput');
    if (searchInput && searchInput.closest('.content-controls')) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const searchContainer = searchInput.closest('.search-container');
                searchContainer.classList.add('loading');
                setTimeout(() => {
                    window.location.href = `admin_dashboard.php?search=${encodeURIComponent(e.target.value)}&status=${window.appConfig.statusFilter}&role=${window.appConfig.roleFilter}`;
                }, 500);
            }
        });
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            window.location.href = `admin_dashboard.php?search=${window.appConfig.searchTerm}&status=${encodeURIComponent(this.value)}&role=${window.appConfig.roleFilter}`;
        });
    }

    const roleFilter = document.getElementById('roleFilter');
    if (roleFilter) {
        roleFilter.addEventListener('change', function () {
            window.location.href = `admin_dashboard.php?search=${window.appConfig.searchTerm}&status=${window.appConfig.statusFilter}&role=${encodeURIComponent(this.value)}`;
        });
    }

    window.changePage = function (page) {
        window.location.href = `admin_dashboard.php?page=${page}&sort=${window.appConfig.sortField}&direction=${window.appConfig.sortDirection}&search=${window.appConfig.searchTerm}&status=${window.appConfig.statusFilter}&role=${window.appConfig.roleFilter}`;
    };

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});

window.togglePasswordVisibility = function (inputId) {
    const input = document.getElementById(inputId);
    const toggleIcon = input.nextElementSibling.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
};

window.editCustomer = function (id) {
    fetch(`admin_dashboard.php?action=get_user&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Chỉnh sửa thông tin người dùng';
                document.getElementById('formAction').value = 'edit';
                document.getElementById('customerId').value = data.user.id;
                document.getElementById('customerName').value = data.user.name;
                document.getElementById('customerEmail').value = data.user.email;
                document.getElementById('customerPhone').value = data.user.phone || '';
                document.getElementById('customerAddress').value = data.user.address || '';
                document.getElementById('customerRole').value = data.user.role;
                document.getElementById('customerStatus').value = data.user.status;
                document.getElementById('customerPassword').value = ''; // Không điền mật khẩu hiện tại
                document.getElementById('customerModal').classList.add('show');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(() => showNotification('Lỗi khi lấy thông tin người dùng', 'error'));
};