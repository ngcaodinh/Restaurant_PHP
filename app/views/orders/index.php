<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Đơn hàng của tôi</title>
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
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-receipt"></i> Đơn hàng của tôi
                </h2>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="row">
                <div class="col-12 text-center">
                    <div class="empty-orders">
                        <i class="fas fa-receipt fa-5x text-muted mb-3"></i>
                        <h3>Chưa có đơn hàng nào</h3>
                        <p class="text-muted">Bạn chưa đặt đơn hàng nào</p>
                        <a href="/" class="btn btn-primary">
                            <i class="fas fa-utensils"></i> Đặt món ngay
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Đơn hàng #<?php echo $order['id']; ?></h5>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </small>
                                    </div>
                                    <div>
                                        <?php
                                        $statusClass = match ($order['status']) {
                                            'Pending' => 'warning',
                                            'Confirmed' => 'primary',
                                            'Processing' => 'info',
                                            'Shipped' => 'info',
                                            'Delivered' => 'success',
                                            'Cancelled' => 'danger',
                                            'Refunded' => 'dark',
                                            default => 'secondary'
                                        };
                                        $statusText = match ($order['status']) {
                                            'Pending' => 'Chờ xác nhận',
                                            'Confirmed' => 'Đã xác nhận',
                                            'Processing' => 'Đang xử lý',
                                            'Shipped' => 'Đang giao hàng',
                                            'Delivered' => 'Đã giao',
                                            'Cancelled' => 'Đã hủy',
                                            'Refunded' => 'Đã hoàn tiền',
                                            default => $order['status']
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</p>
                                            <p><strong>Số món:</strong> <?php echo $order['item_count']; ?> món</p>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if (!empty($order['delivery_address'])): ?>
                                                <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($order['phone'])): ?>
                                                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="/order?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </a>
                                        <?php if (in_array($order['status'], ['Pending', 'Confirmed'])): ?>
                                            <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-times"></i> Hủy đơn
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
</body>

</html>