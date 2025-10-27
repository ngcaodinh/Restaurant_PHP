<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Chi tiết đơn hàng #<?php echo $order['id']; ?></title>
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

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Chi tiết đơn hàng #<?php echo $order['id']; ?></h2>
                    <a href="/orders" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Thông tin đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Mã đơn hàng:</strong></div>
                                    <div class="col-sm-8">#<?php echo $order['id']; ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Khách hàng:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Email:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($order['user_email']); ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Ngày đặt:</strong></div>
                                    <div class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                                    <div class="col-sm-8">
                                        <?php
                                        $statusClass = match($order['status']) {
                                            'Pending' => 'warning',
                                            'Confirmed' => 'info',
                                            'Preparing' => 'primary',
                                            'Ready' => 'success',
                                            'Delivered' => 'success',
                                            'Cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusText = match($order['status']) {
                                            'Pending' => 'Chờ xác nhận',
                                            'Confirmed' => 'Đã xác nhận',
                                            'Preparing' => 'Đang chuẩn bị',
                                            'Ready' => 'Sẵn sàng',
                                            'Delivered' => 'Đã giao',
                                            'Cancelled' => 'Đã hủy',
                                            default => $order['status']
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?> fs-6">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Địa chỉ giao hàng:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($order['delivery_address']); ?></div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Số điện thoại:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($order['phone']); ?></div>
                                </div>
                                
                                <?php if (!empty($order['notes'])): ?>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Ghi chú:</strong></div>
                                    <div class="col-sm-8"><?php echo htmlspecialchars($order['notes']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Món đã đặt</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Hình ảnh</th>
                                                <th>Món ăn</th>
                                                <th>Số lượng</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($order['items'] as $item): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($item['dish_image'] ?: 'https://via.placeholder.com/50x50?text=No+Image'); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['dish_name']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                                </td>
                                                <td><?php echo htmlspecialchars($item['dish_name']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <th colspan="4">Tổng cộng:</th>
                                                <th><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Thao tác</h5>
                            </div>
                            <div class="card-body">
                                <?php if (in_array($order['status'], ['Pending', 'Confirmed']) && $order['user_id'] == $_SESSION['user_id']): ?>
                                    <button class="btn btn-danger w-100 mb-2" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i> Hủy đơn hàng
                                    </button>
                                <?php endif; ?>
                                
                                <a href="/orders" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-list"></i> Xem tất cả đơn hàng
                                </a>
                                
                                <a href="/" class="btn btn-outline-success w-100">
                                    <i class="fas fa-home"></i> Về trang chủ
                                </a>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h6>Trạng thái đơn hàng</h6>
                            </div>
                            <div class="card-body">
                                <div class="order-timeline">
                                    <div class="timeline-item <?php echo in_array($order['status'], ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivered']) ? 'completed' : ''; ?>">
                                        <i class="fas fa-clock"></i> Đã đặt hàng
                                    </div>
                                    <div class="timeline-item <?php echo in_array($order['status'], ['Confirmed', 'Preparing', 'Ready', 'Delivered']) ? 'completed' : ''; ?>">
                                        <i class="fas fa-check"></i> Đã xác nhận
                                    </div>
                                    <div class="timeline-item <?php echo in_array($order['status'], ['Preparing', 'Ready', 'Delivered']) ? 'completed' : ''; ?>">
                                        <i class="fas fa-utensils"></i> Đang chuẩn bị
                                    </div>
                                    <div class="timeline-item <?php echo in_array($order['status'], ['Ready', 'Delivered']) ? 'completed' : ''; ?>">
                                        <i class="fas fa-box"></i> Sẵn sàng
                                    </div>
                                    <div class="timeline-item <?php echo $order['status'] === 'Delivered' ? 'completed' : ''; ?>">
                                        <i class="fas fa-truck"></i> Đã giao hàng
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
        function cancelOrder(orderId) {
            if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;
            
            fetch('/api/order/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }
    </script>

    <style>
        .order-timeline .timeline-item {
            padding: 10px 0;
            color: #6c757d;
        }
        .order-timeline .timeline-item.completed {
            color: #28a745;
            font-weight: bold;
        }
        .order-timeline .timeline-item i {
            margin-right: 8px;
        }
    </style>
</body>
</html>
