<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Admin Dashboard</title>
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
                <h2 class="mb-4">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Quản Trị
                </h2>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $userStats['total_users'] ?? 0; ?></h4>
                                        <p class="mb-0">Tổng người dùng</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $dishStats['total_dishes'] ?? 0; ?></h4>
                                        <p class="mb-0">Tổng món ăn</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-utensils fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $orderStats['total_orders'] ?? 0; ?></h4>
                                        <p class="mb-0">Tổng đơn hàng</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($orderStats['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</h4>
                                        <p class="mb-0">Tổng doanh thu</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clock"></i> Đơn hàng gần đây</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentOrders)): ?>
                                    <p class="text-muted">Chưa có đơn hàng nào</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Khách hàng</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày đặt</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</td>
                                                    <td>
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
                                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                    <td>
                                                        <a href="/order?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bolt"></i> Thao tác nhanh</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <a href="/admin/users" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="fas fa-users"></i><br>
                                            Quản lý người dùng
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/admin/dishes" class="btn btn-outline-success w-100 mb-2">
                                            <i class="fas fa-utensils"></i><br>
                                            Quản lý món ăn
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/admin/orders" class="btn btn-outline-warning w-100 mb-2">
                                            <i class="fas fa-shopping-cart"></i><br>
                                            Quản lý đơn hàng
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="/" class="btn btn-outline-info w-100 mb-2">
                                            <i class="fas fa-home"></i><br>
                                            Xem trang chủ
                                        </a>
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
</body>
</html>
