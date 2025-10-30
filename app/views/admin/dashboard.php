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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_dashboard.css">
</head>

<body>
    <!-- Background Overlay -->
    <div class="background-overlay"></div>

    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    ?>

    <div class="main-container">
        <div class="container-fluid">
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
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="page-title">
                            <div class="icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div>
                                <h1>Dashboard Quản Trị</h1>
                                <p class="page-subtitle">Tổng quan hệ thống và hoạt động kinh doanh</p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card" data-stat="users">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <div class="stat-value" id="totalUsers"><?php echo $userStats['total_users'] ?? 0; ?></div>
                                    <div class="stat-label">Tổng người dùng</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+12% so với tháng trước</span>
                            </div>
                        </div>

                        <div class="stat-card" data-stat="dishes">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <div class="stat-value" id="totalDishes"><?php echo $dishStats['total_dishes'] ?? 0; ?></div>
                                    <div class="stat-label">Tổng món ăn</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            </div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+5 món mới tuần này</span>
                            </div>
                        </div>

                        <div class="stat-card" data-stat="orders">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <div class="stat-value" id="totalOrders"><?php echo $orderStats['total_orders'] ?? 0; ?></div>
                                    <div class="stat-label">Tổng đơn hàng</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+23% so với tuần trước</span>
                            </div>
                        </div>

                        <div class="stat-card" data-stat="revenue">
                            <div class="stat-header">
                                <div class="stat-info">
                                    <div class="stat-value" id="totalRevenue"><?php echo number_format($orderStats['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</div>
                                    <div class="stat-label">Tổng doanh thu</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                            <div class="stat-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+18% so với tháng trước</span>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-lg-8 mb-4">
                            <div class="content-card">
                                <div class="content-header">
                                    <h3 class="content-title">
                                        <i class="fas fa-chart-line"></i> Biểu đồ doanh thu
                                    </h3>
                                </div>
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <div class="content-card">
                                <div class="content-header">
                                    <h3 class="content-title">
                                        <i class="fas fa-chart-pie"></i> Phân loại đơn hàng
                                    </h3>
                                </div>
                                <div class="chart-container">
                                    <canvas id="orderStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="content-card mb-4">
                        <div class="content-header">
                            <h3 class="content-title">
                                <i class="fas fa-clock"></i> Đơn hàng gần đây
                            </h3>
                        </div>
                        <div class="table-container">
                            <?php if (empty($recentOrders)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Chưa có đơn hàng nào</p>
                                </div>
                            <?php else: ?>
                                <div class="table-wrapper">
                                    <table class="table">
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
                                                <tr class="fade-in">
                                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                                    <td>
                                                        <div class="customer-info">
                                                            <div class="customer-avatar">
                                                                <?php echo strtoupper(substr($order['user_name'], 0, 1)); ?>
                                                            </div>
                                                            <div class="customer-details">
                                                                <h4><?php echo htmlspecialchars($order['user_name']); ?></h4>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ</strong></td>
                                                    <td>
                                                        <?php
                                                        $statusClass = match ($order['status']) {
                                                            'Pending' => 'status-badge status-pending',
                                                            'Confirmed' => 'status-badge status-confirmed',
                                                            'Preparing' => 'status-badge status-preparing',
                                                            'Ready' => 'status-badge status-ready',
                                                            'Delivered' => 'status-badge status-delivered',
                                                            'Cancelled' => 'status-badge status-cancelled',
                                                            default => 'status-badge'
                                                        };
                                                        $statusText = match ($order['status']) {
                                                            'Pending' => 'Chờ xác nhận',
                                                            'Confirmed' => 'Đã xác nhận',
                                                            'Preparing' => 'Đang chuẩn bị',
                                                            'Ready' => 'Sẵn sàng',
                                                            'Delivered' => 'Đã giao',
                                                            'Cancelled' => 'Đã hủy',
                                                            default => $order['status']
                                                        };
                                                        ?>
                                                        <span class="<?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="btn btn-icon btn-primary" title="Xem chi tiết">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="content-card">
                        <div class="content-header">
                            <h3 class="content-title">
                                <i class="fas fa-bolt"></i> Thao tác nhanh
                            </h3>
                        </div>
                        <div class="quick-actions">
                            <a href="/admin/users" class="quick-action-card">
                                <div class="quick-action-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="quick-action-info">
                                    <h4>Quản lý người dùng</h4>
                                    <p>Xem và quản lý tài khoản</p>
                                </div>
                            </a>

                            <a href="/admin/dishes" class="quick-action-card">
                                <div class="quick-action-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <div class="quick-action-info">
                                    <h4>Quản lý món ăn</h4>
                                    <p>Thêm và chỉnh sửa món ăn</p>
                                </div>
                            </a>

                            <a href="/admin/orders" class="quick-action-card">
                                <div class="quick-action-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="quick-action-info">
                                    <h4>Quản lý đơn hàng</h4>
                                    <p>Theo dõi và xử lý đơn</p>
                                </div>
                            </a>

                            <a href="/" class="quick-action-card">
                                <div class="quick-action-icon" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="quick-action-info">
                                    <h4>Xem trang chủ</h4>
                                    <p>Trở về trang người dùng</p>
                                </div>
                            </a>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/admin_dashboard.js"></script>
</body>

</html>