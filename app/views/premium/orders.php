<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Đơn hàng - Admin</title>

    <!-- Google Fonts -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Material Dashboard CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/material-dashboard/nucleo-icons.css" rel="stylesheet" />
    <link id="pagestyle" href="<?php echo BASE_URL; ?>assets/css/material-dashboard/material-dashboard.min.css" rel="stylesheet" />

    <!-- Custom Admin Orders CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/admin_orders.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php require_once 'app/views/premium/sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="<?php echo BASE_URL; ?>premium/dashboard">Admin</a></li>
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
                                                    <button class="btn btn-link text-secondary font-weight-bold text-xs p-0 view-order-btn"
                                                        data-order-id="<?php echo $order['id']; ?>"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Xem chi tiết đơn hàng">
                                                        <i class="material-symbols-rounded text-sm">visibility</i> Xem
                                                    </button>
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

    <!-- Modals and Toasts -->
    <div id="modals-container">
        <!-- Order Details Modal -->
        <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary">
                        <h5 class="modal-title text-white" id="orderDetailsModalLabel">
                            <i class="material-symbols-rounded align-middle">receipt_long</i>
                            Chi tiết đơn hàng <span id="orderIdDisplay"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0" id="orderDetailsContent">
                        <!-- Loading spinner -->
                        <div class="text-center py-5" id="loadingSpinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-3 text-muted">Đang tải thông tin đơn hàng...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="material-symbols-rounded align-middle">close</i> Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>

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
    </div>
    <!-- End Modals Container -->

    <!-- Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/popper.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/bootstrap.min.js"></script>

    <!-- Define BASE_URL for JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>

    <!-- Custom Premium Orders JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/premium_orders.js"></script>
</body>

</html>