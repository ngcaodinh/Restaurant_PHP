<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
</head>
<body>
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <?php
                $sidebarPath = dirname(dirname(dirname(__DIR__))) . '/templates/sidebar_admin.php';
                if (file_exists($sidebarPath)) {
                    include $sidebarPath;
                }
                ?>
            </div>
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</h2>
                    <div>
                        <span class="badge bg-primary">Tổng: <?php echo $orderStats['total_orders'] ?? 0; ?></span>
                        <span class="badge bg-success">Doanh thu: <?php echo number_format($orderStats['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Email</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['user_email']); ?></td>
                                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                        <td>
                                            <select class="form-select form-select-sm" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                                <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                                <option value="Confirmed" <?php echo $order['status'] === 'Confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                                <option value="Preparing" <?php echo $order['status'] === 'Preparing' ? 'selected' : ''; ?>>Đang chuẩn bị</option>
                                                <option value="Ready" <?php echo $order['status'] === 'Ready' ? 'selected' : ''; ?>>Sẵn sàng</option>
                                                <option value="Delivered" <?php echo $order['status'] === 'Delivered' ? 'selected' : ''; ?>>Đã giao</option>
                                                <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                            </select>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    $footerPath = dirname(dirname(dirname(__DIR__))) . '/templates/footer.php';
    if (file_exists($footerPath)) {
        require_once $footerPath;
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateOrderStatus(orderId, status) {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);
            
            fetch('/api/order/update-status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message briefly
                    const originalText = event.target.parentElement.innerHTML;
                    event.target.parentElement.innerHTML = '<span class="text-success">Đã cập nhật!</span>';
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
                location.reload();
            });
        }

        function viewOrderDetails(orderId) {
            // For now, redirect to order details page
            window.open('/order?id=' + orderId, '_blank');
        }
    </script>
</body>
</html>
