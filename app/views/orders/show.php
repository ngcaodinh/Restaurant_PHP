<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Chi tiết đơn hàng #<?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/show.css">
</head>

<body>
    <div class="background-overlay"></div>

    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    ?>

    <div class="order-details-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-10 mx-auto">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Chi tiết đơn hàng #<?php echo $order['id']; ?></h5>
                                    <a href="<?php echo BASE_URL; ?>purchase-history" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center">
                                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                                    </a>
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
                                            $statusConfig = [
                                                'Pending' => ['class' => 'warning', 'text' => 'Chờ xác nhận'],
                                                'Confirmed' => ['class' => 'info', 'text' => 'Đã xác nhận'],
                                                'Processing' => ['class' => 'primary', 'text' => 'Đang xử lý'],
                                                'Shipped' => ['class' => 'info', 'text' => 'Đang giao'],
                                                'Delivered' => ['class' => 'success', 'text' => 'Đã giao'],
                                                'Cancelled' => ['class' => 'danger', 'text' => 'Đã hủy'],
                                                'Refunded' => ['class' => 'dark', 'text' => 'Đã hoàn tiền']
                                            ];
                                            $currentStatusConfig = $statusConfig[$order['status']] ?? ['class' => 'light', 'text' => $order['status']];
                                            $statusClass = $currentStatusConfig['class'];
                                            $statusText = $currentStatusConfig['text'];
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
                                                    <th><?php echo number_format($order['total_price'], 0, ',', '.'); ?>đ</th>
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

                                    <a href="<?php echo BASE_URL; ?>purchase-history" class="btn btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-list"></i> Xem tất cả đơn hàng
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-success w-100">
                                        <i class="fas fa-home"></i> Về trang chủ
                                    </a>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6>Tiến trình đơn hàng</h6>
                                </div>
                                <div class="card-body pt-2">
                                    <?php
                                    $statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered'];
                                    $currentStatusIndex = array_search($order['status'], $statuses);
                                    $isCancelled = in_array($order['status'], ['Cancelled', 'Refunded']);

                                    if ($isCancelled) {
                                        echo "<div class='alert alert-danger'>Đơn hàng đã bị hủy hoặc hoàn tiền.</div>";
                                    } else {
                                        $progressPercentage = $currentStatusIndex !== false ? ($currentStatusIndex / (count($statuses) - 1)) * 100 : 0;
                                    ?>
                                        <div class="progress-wrapper">
                                            <div class="progress-info">
                                                <div class="progress-percentage">
                                                    <span class="text-sm font-weight-bold"><?php echo $statusText; ?></span>
                                                </div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progressPercentage; ?>%;"></div>
                                            </div>
                                        </div>
                                    <?php } ?>
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
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/show.js"></script>


</body>

</html>