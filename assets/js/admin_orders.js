/**
 * Admin Orders Management JavaScript
 * Handles order details display, status updates, and modal interactions
 */

// Global variables
let orderDetailsModal, confirmationModal, successToast;
let activeSelect = null;

// Status configuration
const statusColors = {
    'Pending': 'warning',
    'Confirmed': 'info',
    'Processing': 'primary',
    'Shipped': 'secondary',
    'Delivered': 'success',
    'Cancelled': 'danger',
    'Refunded': 'dark'
};

const statusLabels = {
    'Pending': 'Chờ xác nhận',
    'Confirmed': 'Đã xác nhận',
    'Processing': 'Đang xử lý',
    'Shipped': 'Đang giao',
    'Delivered': 'Đã giao',
    'Cancelled': 'Đã hủy',
    'Refunded': 'Đã hoàn tiền'
};

const paymentMethodLabels = {
    'COD': 'Thanh toán khi nhận hàng (COD)',
    'Online': 'Thanh toán trực tuyến'
};

const validTransitions = {
    'Pending': ['Confirmed', 'Cancelled'],
    'Confirmed': ['Processing', 'Cancelled'],
    'Processing': ['Shipped'],
    'Shipped': ['Delivered'],
    'Delivered': [],
    'Cancelled': [],
    'Refunded': []
};

/**
 * Initialize the page when DOM is loaded
 */
/**
 * Tệp JavaScript xử lý trang quản lý đơn hàng admin
 * Xử lý hiển thị, cập nhật trạng thái và xem chi tiết đơn hàng
 */

document.addEventListener('DOMContentLoaded', function () {
    initializeModalsAndToasts();
    initializeTooltips();
    initializeViewOrderButtons();
    initializeStatusSelects();
});

/**
 * Initialize Bootstrap modals and toasts
 */
function initializeModalsAndToasts() {
    orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    successToast = new bootstrap.Toast(document.getElementById('successToast'), {
        delay: 5000
    });
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize view order button click handlers
 */
function initializeViewOrderButtons() {
    document.querySelectorAll('.view-order-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const orderId = this.dataset.orderId;
            loadOrderDetails(orderId);
        });
    });
}

/**
 * Load order details via AJAX
 * @param {number} orderId - The ID of the order to load
 */
function loadOrderDetails(orderId) {
    const orderDetailsContent = document.getElementById('orderDetailsContent');
    const orderIdDisplay = document.getElementById('orderIdDisplay');

    // Reset modal content and show loading spinner
    orderDetailsContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-3 text-muted">Đang tải thông tin đơn hàng #${orderId}...</p>
        </div>
    `;

    // Update order ID display
    if (orderIdDisplay) {
        orderIdDisplay.textContent = '#' + orderId;
    }

    // Show modal
    orderDetailsModal.show();

    // Fetch order details
    fetch(`${BASE_URL}admin/get-order-details?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.data);
            } else {
                showError(orderDetailsContent, data.message || 'Không thể tải thông tin đơn hàng');
            }
        })
        .catch(error => {
            console.error('Error loading order details:', error);
            showError(orderDetailsContent, 'Có lỗi xảy ra khi tải thông tin đơn hàng');
        });
}

/**
 * Display order details in modal
 * @param {Object} order - Order data object
 */
function displayOrderDetails(order) {
    let itemsHtml = '';
    let totalAmount = 0;

    // Build items HTML
    order.items.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        totalAmount += itemTotal;
        itemsHtml += buildOrderItemRow(item, itemTotal);
    });

    // Build complete HTML
    const html = `
        <div class="p-4">
            ${buildCustomerInfoCard(order)}
            ${buildOrderItemsCard(order, itemsHtml, totalAmount)}
            ${buildOrderSummaryCard(order)}
        </div>
    `;

    document.getElementById('orderDetailsContent').innerHTML = html;

    // Add animation to order items
    animateOrderItems();
}

/**
 * Build HTML for a single order item row
 * @param {Object} item - Order item data
 * @param {number} itemTotal - Total price for this item
 * @returns {string} HTML string
 */
function buildOrderItemRow(item, itemTotal) {
    return `
        <tr class="order-item-row">
            <td class="ps-4">
                <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm bg-gradient-info shadow text-center border-radius-md me-3">
                        <i class="material-symbols-rounded opacity-10 text-white">restaurant</i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-sm font-weight-bold">${item.dish_name}</h6>
                        <p class="text-xs text-muted mb-0">Đơn giá: ${formatCurrency(item.price)}</p>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge badge-sm bg-gradient-secondary px-3 py-2">${item.quantity}</span>
            </td>
            <td class="text-end pe-4">
                <span class="text-sm font-weight-bold text-dark">${formatCurrency(itemTotal)}</span>
            </td>
        </tr>
    `;
}

/**
 * Build customer information card HTML
 * @param {Object} order - Order data
 * @returns {string} HTML string
 */
function buildCustomerInfoCard(order) {
    return `
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-gradient-info">
                <h6 class="text-white mb-0">
                    <i class="material-symbols-rounded align-middle">person</i>
                    Thông tin khách hàng
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">badge</i>
                            Tên khách hàng
                        </label>
                        <p class="text-sm mb-0 font-weight-bold">${order.user_name}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">email</i>
                            Email
                        </label>
                        <p class="text-sm mb-0">${order.user_email}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">phone</i>
                            Số điện thoại
                        </label>
                        <p class="text-sm mb-0">${order.phone || 'Chưa cung cấp'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">info</i>
                            Trạng thái
                        </label>
                        <p class="mb-0">
                            <span class="badge bg-gradient-${statusColors[order.status]} px-3 py-2">${statusLabels[order.status]}</span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">payments</i>
                            Phương thức thanh toán
                        </label>
                        <p class="text-sm mb-0">${paymentMethodLabels[order.payment_method] || order.payment_method}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">location_on</i>
                            Địa chỉ giao hàng
                        </label>
                        <p class="text-sm mb-0">${order.delivery_address || 'Chưa cung cấp'}</p>
                    </div>
                    ${order.notes ? `
                    <div class="col-12">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">
                            <i class="material-symbols-rounded text-xs align-middle">note</i>
                            Ghi chú
                        </label>
                        <div class="alert alert-info mb-0 py-2">
                            <p class="text-sm mb-0 fst-italic">${order.notes}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

/**
 * Build order items card HTML
 * @param {Object} order - Order data
 * @param {string} itemsHtml - HTML string of order items
 * @param {number} totalAmount - Total amount
 * @returns {string} HTML string
 */
function buildOrderItemsCard(order, itemsHtml, totalAmount) {
    return `
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-gradient-success">
                <h6 class="text-white mb-0">
                    <i class="material-symbols-rounded align-middle">restaurant</i>
                    Danh sách món ăn (${order.items.length} món)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Món ăn</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Số lượng</th>
                                <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 pe-4">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                            <tr class="bg-light">
                                <td colspan="2" class="text-end pe-3">
                                    <h6 class="mb-0 text-sm font-weight-bold">Tổng cộng:</h6>
                                </td>
                                <td class="text-end pe-4">
                                    <h6 class="mb-0 text-success font-weight-bold">${formatCurrency(totalAmount)}</h6>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

/**
 * Build order summary card HTML
 * @param {Object} order - Order data
 * @returns {string} HTML string
 */
function buildOrderSummaryCard(order) {
    return `
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-dark">
                <h6 class="text-white mb-0">
                    <i class="material-symbols-rounded align-middle">payments</i>
                    Tổng kết đơn hàng
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">Ngày đặt hàng</label>
                        <p class="text-sm mb-0">${formatDateTime(order.created_at)}</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="text-xs text-uppercase text-muted font-weight-bolder">Cập nhật lần cuối</label>
                        <p class="text-sm mb-0">${formatDateTime(order.updated_at)}</p>
                    </div>
                    <div class="col-12">
                        <hr class="horizontal dark my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Tổng tiền:</h5>
                            <h4 class="mb-0 text-success font-weight-bold">${formatCurrency(order.total_price)}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Show error message in modal
 * @param {HTMLElement} container - Container element
 * @param {string} message - Error message
 */
function showError(container, message) {
    container.innerHTML = `
        <div class="alert alert-danger m-4" role="alert">
            <i class="material-symbols-rounded align-middle">error</i>
            ${message}
        </div>
    `;
}

/**
 * Animate order items with staggered fade-in effect
 */
function animateOrderItems() {
    setTimeout(() => {
        document.querySelectorAll('.order-item-row').forEach((row, index) => {
            row.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.1}s`;
            row.style.opacity = '0';
        });
    }, 100);
}

/**
 * Format currency to Vietnamese Dong
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

/**
 * Format date time to Vietnamese locale
 * @param {string} dateString - Date string to format
 * @returns {string} Formatted date time string
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Initialize status select dropdowns
 */
function initializeStatusSelects() {
    document.querySelectorAll('.status-select').forEach(select => {
        const currentStatus = select.dataset.currentStatus;
        const finalStates = ['Delivered', 'Cancelled', 'Refunded'];

        // Disable select if order is in final state
        if (finalStates.includes(currentStatus)) {
            select.disabled = true;
        }

        // Disable invalid transition options
        Array.from(select.options).forEach(option => {
            if (option.value !== currentStatus &&
                (!validTransitions[currentStatus] ||
                    !validTransitions[currentStatus].includes(option.value))) {
                option.disabled = true;
            }
        });

        // Handle status change
        select.addEventListener('change', handleStatusChange);
    });

    // Setup confirmation button
    const confirmBtn = document.getElementById('confirmChangeBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', confirmStatusChange);
    }

    // Reset select on modal cancel
    const confirmationModalEl = document.getElementById('confirmationModal');
    if (confirmationModalEl) {
        confirmationModalEl.addEventListener('hidden.bs.modal', resetActiveSelect);
    }
}

/**
 * Handle status select change event
 * @param {Event} event - Change event
 */
function handleStatusChange(event) {
    activeSelect = event.target;
    const orderId = activeSelect.dataset.orderId;
    const currentStatus = activeSelect.dataset.currentStatus;
    const newStatus = activeSelect.value;
    const oldStatusText = activeSelect.querySelector(`option[value="${currentStatus}"]`).textContent;
    const newStatusText = activeSelect.querySelector(`option[value="${newStatus}"]`).textContent;

    const confirmationModalBody = document.getElementById('confirmationModalBody');
    confirmationModalBody.textContent = `Bạn có chắc muốn đổi trạng thái đơn hàng #${orderId} từ '${oldStatusText}' sang '${newStatusText}'?`;
    confirmationModal.show();
}

/**
 * Confirm and execute status change
 */
function confirmStatusChange() {
    if (!activeSelect) return;

    const orderId = activeSelect.dataset.orderId;
    const newStatus = activeSelect.value;
    const currentStatus = activeSelect.dataset.currentStatus;

    fetch(`${BASE_URL}api/order/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `order_id=${orderId}&status=${newStatus}`
    })
        .then(response => response.json())
        .then(data => {
            confirmationModal.hide();
            if (data.success) {
                successToast.show();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái.'));
                activeSelect.value = currentStatus; // Revert on failure
            }
        })
        .catch(error => {
            confirmationModal.hide();
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi kết nối đến máy chủ.');
            activeSelect.value = currentStatus; // Revert on error
        });
}

/**
 * Reset active select to original value
 */
function resetActiveSelect() {
    if (activeSelect) {
        activeSelect.value = activeSelect.dataset.currentStatus;
    }
}

