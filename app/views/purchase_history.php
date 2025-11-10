<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Lịch sử mua hàng'; ?> - CTUT Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/purchase_history.css">
</head>

<body>
    <div class="background-overlay"></div>
    <?php include_once __DIR__ . '/../../templates/header.php'; ?>

    <div class="history-wrapper">
        <div class="history-container">
            <div class="history-title">
                <h1>Lịch sử mua hàng</h1>
                <p>Theo dõi tất cả đơn hàng của bạn</p>
            </div>

            <?php if (isset($error) && $error) : ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <h4>Lỗi!</h4>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php else : ?>
                <div class="history-filters">
                    <div class="filter-item">
                        <label for="orderIdFilter"><i class="fas fa-hashtag"></i> Tìm kiếm theo mã đơn hàng</label>
                        <div class="filter-input-wrapper">
                            <input type="text" id="orderIdFilter" placeholder="Nhập mã đơn hàng ...">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <div class="filter-item">
                        <label for="dateFilter"><i class="fas fa-calendar-alt"></i> Tìm kiếm theo ngày đặt hàng</label>
                        <input type="date" id="dateFilter">
                    </div>
                </div>

                <?php if (empty($orders)) : ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Chưa có đơn hàng nào</h3>
                        <p>Bạn chưa thực hiện đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
                        <a href="<?php echo BASE_URL; ?>" class="shop-now-btn">
                            <i class="fas fa-shopping-cart"></i> Mua sắm ngay
                        </a>
                    </div>
                <?php else : ?>
                    <div class="orders-grid">
                        <?php foreach ($orders as $order) : ?>
                            <div class="order-box">
                                <div class="order-box-header">
                                    <div class="order-info-row">
                                        <span class="order-number">
                                            <i class="fas fa-receipt"></i> #<?php echo htmlspecialchars($order['id']); ?>
                                        </span>
                                        <span class="order-time">
                                            <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                        </span>
                                    </div>
                                    <span class="order-badge badge-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                        <?php
                                        $statusText = match ($order['status']) {
                                            'Pending' => 'Chờ xác nhận',
                                            'Confirmed' => 'Đã xác nhận',
                                            'Preparing' => 'Đang chuẩn bị',
                                            'Ready' => 'Sẵn sàng',
                                            'Delivered' => 'Đã giao',
                                            'Cancelled' => 'Đã hủy',
                                            default => htmlspecialchars($order['status'])
                                        };
                                        echo $statusText;
                                        ?>
                                    </span>
                                </div>

                                <div class="order-box-content">
                                    <?php if (!empty($order['items'])) : ?>
                                        <?php
                                        $itemsToShow = array_slice($order['items'], 0, 2);
                                        $totalItems = count($order['items']);
                                        ?>
                                        <?php foreach ($itemsToShow as $item) : ?>
                                            <div class="order-product">
                                                <img src="<?php echo htmlspecialchars($item['dish_image']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['dish_name']); ?>"
                                                    class="product-img">
                                                <div class="product-info">
                                                    <h4><?php echo htmlspecialchars($item['dish_name']); ?></h4>
                                                    <span class="product-qty">x<?php echo htmlspecialchars($item['quantity']); ?></span>
                                                </div>
                                                <div class="product-price">
                                                    <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if ($totalItems > 2) : ?>
                                            <div class="see-more-container">
                                                <a href="<?php echo BASE_URL . 'order?id=' . $order['id']; ?>" class="see-more-link">
                                                    Xem thêm <?php echo $totalItems - 2; ?> sản phẩm khác...
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="order-box-footer">
                                    <span class="total-label">Tổng cộng:</span>
                                    <span class="total-amount"><?php echo number_format($order['total_price'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include_once __DIR__ . '/../../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/purchase_history.js"></script>
</body>

</html>