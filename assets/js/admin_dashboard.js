document.addEventListener('DOMContentLoaded', function () {
    // Initialize Charts
    initializeCharts();

    // Animate stat cards
    animateStatCards();

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

    window.editCustomer = function (id) {
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

// Initialize Charts
function initializeCharts() {
    // Revenue Chart
    const revenueChartCanvas = document.getElementById('revenueChart');
    if (revenueChartCanvas) {
        const revenueCtx = revenueChartCanvas.getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (triệu đồng)',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 38, 42, 45],
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(102, 126, 234)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                family: "'Inter', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        borderColor: 'rgba(102, 126, 234, 0.5)',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return context.parsed.y + ' triệu đồng';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            callback: function (value) {
                                return value + 'M';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // Order Status Chart
    const orderStatusChartCanvas = document.getElementById('orderStatusChart');
    if (orderStatusChartCanvas) {
        const orderStatusCtx = orderStatusChartCanvas.getContext('2d');
        const orderStatusChart = new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đã giao', 'Đang chuẩn bị', 'Chờ xác nhận', 'Đã hủy'],
                datasets: [{
                    data: [45, 25, 20, 10],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)',
                        'rgb(168, 85, 247)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11,
                                family: "'Inter', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function (context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' đơn (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Animate Stat Cards
function animateStatCards() {
    const statCards = document.querySelectorAll('.stat-card');

    statCards.forEach((card, index) => {
        const valueElement = card.querySelector('.stat-value');
        if (valueElement) {
            const finalValue = valueElement.textContent.replace(/[^\d]/g, '');
            if (finalValue) {
                animateValue(valueElement, 0, parseInt(finalValue), 1500, index * 100);
            }
        }
    });
}

// Animate number counting
function animateValue(element, start, end, duration, delay) {
    setTimeout(() => {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        const originalText = element.textContent;
        const suffix = originalText.replace(/[\d,\.]/g, '');

        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                current = end;
                clearInterval(timer);
            }

            if (suffix.includes('đ')) {
                element.textContent = Math.floor(current).toLocaleString('vi-VN') + 'đ';
            } else {
                element.textContent = Math.floor(current).toLocaleString('vi-VN');
            }
        }, 16);
    }, delay);
}