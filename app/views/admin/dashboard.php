<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard - CTUT Restaurant</title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <!-- Nucleo Icons -->
    <link href="<?php echo BASE_URL; ?>assets/css/material-dashboard/nucleo-icons.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>assets/css/material-dashboard/nucleo-svg.css" rel="stylesheet" />

    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- CSS Files -->
    <link id="pagestyle" href="<?php echo BASE_URL; ?>assets/css/material-dashboard/material-dashboard.min.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
    <?php require_once 'app/views/admin/sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Admin</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
                    </ol>
                    <h6 class="font-weight-bolder mb-0">Dashboard</h6>
                </nav>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-2">
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-header p-2 ps-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-sm mb-0 text-capitalize">Tổng người dùng</p>
                                    <h4 class="mb-0"><?php echo $userStats['total_users'] ?? 0; ?></h4>
                                </div>
                                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                                    <i class="material-symbols-rounded opacity-10">group</i>
                                </div>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-2 ps-3">
                            <p class="mb-0 text-sm">
                                <span class="text-<?php echo ($userStats['user_growth'] >= 0) ? 'success' : 'danger'; ?> font-weight-bolder">
                                    <?php echo ($userStats['user_growth'] >= 0 ? '+' : '') . number_format($userStats['user_growth'], 2); ?>%
                                </span>
                                so với tháng trước
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-header p-2 ps-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-sm mb-0 text-capitalize">Tổng món ăn</p>
                                    <h4 class="mb-0"><?php echo $dishStats['total_dishes'] ?? 0; ?></h4>
                                </div>
                                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                                    <i class="material-symbols-rounded opacity-10">restaurant_menu</i>
                                </div>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-2 ps-3">
                            <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+<?php echo $dishStats['new_dishes_this_month'] ?? 0; ?></span> món mới trong tháng</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-header p-2 ps-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-sm mb-0 text-capitalize">Tổng đơn hàng</p>
                                    <h4 class="mb-0"><?php echo $orderStats['total_orders'] ?? 0; ?></h4>
                                </div>
                                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                                    <i class="material-symbols-rounded opacity-10">receipt_long</i>
                                </div>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-2 ps-3">
                            <p class="mb-0 text-sm">
                                <span class="text-<?php echo ($orderStats['order_growth_weekly'] >= 0) ? 'success' : 'danger'; ?> font-weight-bolder">
                                    <?php echo ($orderStats['order_growth_weekly'] >= 0 ? '+' : '') . number_format($orderStats['order_growth_weekly'], 2); ?>%
                                </span>
                                so với tuần trước
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-header p-2 ps-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-sm mb-0 text-capitalize">Tổng doanh thu</p>
                                    <h4 class="mb-0"><?php echo number_format($orderStats['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</h4>
                                </div>
                                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                                    <i class="material-symbols-rounded opacity-10">payments</i>
                                </div>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-2 ps-3">
                            <p class="mb-0 text-sm">
                                <span class="text-<?php echo ($orderStats['revenue_growth_monthly'] >= 0) ? 'success' : 'danger'; ?> font-weight-bolder">
                                    <?php echo ($orderStats['revenue_growth_monthly'] >= 0 ? '+' : '') . number_format($orderStats['revenue_growth_monthly'], 2); ?>%
                                </span>
                                so với tháng trước
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-lg-6 col-7">
                                    <h6>Đơn hàng gần đây</h6>
                                    <p class="text-sm mb-0">
                                        <i class="fa fa-check text-info" aria-hidden="true"></i>
                                        <span class="font-weight-bold ms-1">Có <?php echo count($recentOrders); ?> đơn hàng mới</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive">
                                <?php if (empty($recentOrders)): ?>
                                    <p class="text-center p-4">Chưa có đơn hàng nào.</p>
                                <?php else: ?>
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Khách hàng</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tổng tiền</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày đặt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($order['user_name']); ?></h6>
                                                                <p class="text-xs text-secondary mb-0">#<?php echo $order['id']; ?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="text-xs font-weight-bold"> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <?php
                                                        $statusClass = match ($order['status']) {
                                                            'Pending' => 'bg-gradient-warning',
                                                            'Confirmed' => 'bg-gradient-info',
                                                            'Preparing' => 'bg-gradient-primary',
                                                            'Ready' => 'bg-gradient-secondary',
                                                            'Delivered' => 'bg-gradient-success',
                                                            'Cancelled' => 'bg-gradient-danger',
                                                            default => 'bg-gradient-light'
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
                                                        <span class="badge badge-sm <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-secondary text-xs font-weight-bold"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Doanh thu theo tháng</h6>
                            <p class="text-sm">
                                <i class="fa fa-arrow-<?php echo ($orderStats['revenue_growth_daily'] >= 0) ? 'up' : 'down'; ?> text-<?php echo ($orderStats['revenue_growth_daily'] >= 0) ? 'success' : 'danger'; ?>" aria-hidden="true"></i>
                                <span class="font-weight-bold"><?php echo number_format(abs($orderStats['revenue_growth_daily']), 2); ?>%</span> so với hôm qua
                            </p>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart">
                                <canvas id="revenue-chart" class="chart-canvas" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Tổng quan đơn hàng</h6>
                            <p class="text-sm">
                                <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
                                <span class="font-weight-bold">24%</span> trong tháng này
                            </p>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart">
                                <canvas id="orderStatusChart" class="chart-canvas" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!--   Core JS Files   -->
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/popper.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/plugins/perfect-scrollbar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/plugins/smooth-scrollbar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/plugins/chartjs.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const revenueChartCanvas = document.getElementById('revenue-chart');
            const orderStatusChartCanvas = document.getElementById('orderStatusChart');
            if (orderStatusChartCanvas) {
                const orderStatusCtx = orderStatusChartCanvas.getContext('2d');
                new Chart(orderStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo $orderStatusLabels ?? '[]'; ?>,
                        datasets: [{
                            label: 'Đơn hàng',
                            data: <?php echo $orderStatusData ?? '[]'; ?>,
                            backgroundColor: ['#4CAF50', '#FFC107', '#03A9F4', '#F44336', '#9E9E9E', '#795548'],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    pointStyle: 'rectRounded',
                                    color: '#344767'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }

            if (revenueChartCanvas) {
                console.log('Revenue Labels:', <?php echo $revenueLabels ?? '"[]"'; ?>);
                console.log('Revenue Data:', <?php echo $revenueData ?? '"[]"'; ?>);

                const revenueCtx = revenueChartCanvas.getContext('2d');
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo $revenueLabels ?? '[]'; ?>,
                        datasets: [{
                            label: "Doanh thu",
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 2,
                            pointBackgroundColor: "#4CAF50",
                            borderColor: "#4CAF50",
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            fill: true,
                            data: <?php echo $revenueData ?? '[]'; ?>,
                            maxBarThickness: 6
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('vi-VN', {
                                                style: 'currency',
                                                currency: 'VND'
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
                                grid: {
                                    drawBorder: false,
                                    display: true,
                                    drawOnChartArea: true,
                                    drawTicks: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    display: true,
                                    padding: 10,
                                    color: '#b2b9bf',
                                    font: {
                                        size: 11,
                                        family: "Inter",
                                        style: 'normal',
                                        lineHeight: 2
                                    },
                                    callback: function(value) {
                                        return (value / 1000000) + 'M';
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    drawBorder: false,
                                    display: false,
                                    drawOnChartArea: false,
                                    drawTicks: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    display: true,
                                    color: '#b2b9bf',
                                    padding: 20,
                                    font: {
                                        size: 11,
                                        family: "Inter",
                                        style: 'normal',
                                        lineHeight: 2
                                    }
                                }
                            },
                        },
                    },
                });
            }
        });
    </script>

    <script src="<?php echo BASE_URL; ?>assets/js/admin_dashboard.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/material-dashboard.min.js"></script>
</body>

</html>