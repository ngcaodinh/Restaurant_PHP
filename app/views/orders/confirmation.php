<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Xác nhận đơn hàng</title>
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
            <div class="col-md-8 mx-auto">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h2 class="text-success mt-3">Đặt hàng thành công!</h2>
                    <p class="text-muted">Cảm ơn bạn đã đặt hàng tại CTUT Restaurant</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Chi tiết đơn hàng #<?php echo $order['id']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Mã đơn hàng:</strong></div>
                            <div class="col-sm-8">#<?php echo $order['id']; ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ngày đặt:</strong></div>
                            <div class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-warning">Chờ xác nhận</span>
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

                        <hr>

                        <h6>Món đã đặt:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Món ăn</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['dish_name']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Tổng cộng:</th>
                                        <th><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <div class="d-grid gap-2 d-md-block">
                        <a href="/orders" class="btn btn-primary">
                            <i class="fas fa-list"></i> Xem đơn hàng của tôi
                        </a>
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-info-circle"></i> Thông tin quan trọng:</h6>
                    <ul class="mb-0">
                        <li>Đơn hàng của bạn đang được xử lý</li>
                        <li>Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng</li>
                        <li>Thời gian giao hàng dự kiến: 30-45 phút</li>
                        <li>Bạn có thể theo dõi trạng thái đơn hàng trong mục "Đơn hàng của tôi"</li>
                    </ul>
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
</body>

</html>