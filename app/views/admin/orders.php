<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Đơn hàng - Admin</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="<?php echo BASE_URL; ?>assets/css/material-dashboard/nucleo-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="<?php echo BASE_URL; ?>assets/css/material-dashboard/material-dashboard.min.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php require_once 'app/views/admin/sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="/admin/dashboard">Admin</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Đơn hàng</li>
                    </ol>
                    <h6 class="font-weight-bolder mb-0">Quản lý Đơn hàng</h6>
                </nav>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Danh sách đơn hàng</h6>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Khách hàng</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tổng tiền</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày đặt</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0 px-3">#<?php echo $order['id']; ?></p>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($order['user_name']); ?></h6>
                                                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($order['user_email']); ?></p>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center text-sm"><span class="text-secondary text-xs font-weight-bold"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</span></td>
                                                <td class="align-middle text-center">
                                                    <select class="form-select form-select-sm status-select"
                                                        data-order-id="<?php echo $order['id']; ?>"
                                                        data-current-status="<?php echo $order['status']; ?>">
                                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                                        <option value="Confirmed" <?php echo $order['status'] == 'Confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                                        <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                        <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Đang giao</option>
                                                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Đã giao</option>
                                                        <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                        <option value="Refunded" <?php echo $order['status'] == 'Refunded' ? 'selected' : ''; ?>>Đã hoàn tiền</option>
                                                    </select>
                                                </td>
                                                <td class="align-middle text-center"><span class="text-secondary text-xs font-weight-bold"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span></td>
                                                <td class="align-middle">
                                                    <a href="<?php echo BASE_URL; ?>order?id=<?php echo $order['id']; ?>" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Xem chi tiết">
                                                        Xem
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <!-- Pagination will be added here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Xác nhận thay đổi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmationModalBody">
                    <!-- Confirmation message will be injected here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="confirmChangeBtn">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="position-fixed top-3 end-3 p-3" style="z-index: 1100">
        <div id="successToast" class="toast hide bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white border-0">
                <i class="material-symbols-rounded me-2">check_circle</i>
                <strong class="me-auto">Thành công</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Cập nhật trạng thái thành công!
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/popper.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            const successToast = new bootstrap.Toast(document.getElementById('successToast'), {
                delay: 5000
            });
            const confirmBtn = document.getElementById('confirmChangeBtn');
            const confirmationModalBody = document.getElementById('confirmationModalBody');
            let activeSelect = null;

            const validTransitions = {
                'Pending': ['Confirmed', 'Cancelled'],
                'Confirmed': ['Processing', 'Cancelled'],
                'Processing': ['Shipped'],
                'Shipped': ['Delivered'],
                'Delivered': [],
                'Cancelled': [],
                'Refunded': []
            };

            document.querySelectorAll('.status-select').forEach(select => {
                const currentStatus = select.dataset.currentStatus;
                const finalStates = ['Delivered', 'Cancelled', 'Refunded'];

                if (finalStates.includes(currentStatus)) {
                    select.disabled = true;
                }

                Array.from(select.options).forEach(option => {
                    if (option.value !== currentStatus && (!validTransitions[currentStatus] || !validTransitions[currentStatus].includes(option.value))) {
                        option.disabled = true;
                    }
                });

                select.addEventListener('change', function(event) {
                    activeSelect = event.target;
                    const orderId = activeSelect.dataset.orderId;
                    const newStatus = activeSelect.value;
                    const oldStatusText = activeSelect.querySelector(`option[value="${currentStatus}"]`).textContent;
                    const newStatusText = activeSelect.querySelector(`option[value="${newStatus}"]`).textContent;

                    confirmationModalBody.textContent = `Bạn có chắc muốn đổi trạng thái đơn hàng #${orderId} từ '${oldStatusText}' sang '${newStatusText}'?`;
                    confirmationModal.show();
                });
            });

            confirmBtn.addEventListener('click', function() {
                if (!activeSelect) return;

                const orderId = activeSelect.dataset.orderId;
                const newStatus = activeSelect.value;
                const currentStatus = activeSelect.dataset.currentStatus;

                fetch('<?php echo BASE_URL; ?>api/order/update-status', {
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
            });

            // Reset select if modal is cancelled
            document.getElementById('confirmationModal').addEventListener('hidden.bs.modal', function() {
                if (activeSelect) {
                    activeSelect.value = activeSelect.dataset.currentStatus;
                }
            });
        });
    </script>
</body>

</html>