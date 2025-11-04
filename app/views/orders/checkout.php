<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/checkout.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/footer.css">
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

    <div class="container checkout-container">
        <!-- Page Header -->
        <div class="checkout-page-header">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="checkout-title">
                        <i class="fas fa-credit-card me-3"></i>Thanh toán đơn hàng
                    </h1>
                    <p class="checkout-subtitle">Hoàn tất đơn hàng của bạn</p>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (isset($_SESSION['checkout_errors']) && !empty($_SESSION['checkout_errors'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-danger alert-modern">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <h5 class="alert-title">Có lỗi xảy ra</h5>
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['checkout_errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['checkout_errors']); ?>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Left Column - Delivery Information -->
            <div class="col-lg-7">
                <div class="checkout-card">
                    <div class="checkout-card-header">
                        <div class="card-header-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="card-header-content">
                            <h3>Thông tin giao hàng</h3>
                            <p>Vui lòng điền đầy đủ thông tin để nhận hàng</p>
                        </div>
                    </div>

                    <div class="checkout-card-body">
                        <form id="checkout-form" method="POST" action="<?php echo BASE_URL; ?>order/create">
                            <input type="hidden" name="checkout_items" value="<?php echo htmlspecialchars(json_encode(array_column($cartItems, 'id'))); ?>">
                            <input type="hidden" name="total_price" value="<?php echo $cartTotal; ?>">

                            <!-- Delivery Address -->
                            <div class="form-group">
                                <label for="delivery_address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ giao hàng *
                                </label>
                                <textarea class="form-control modern-input" id="delivery_address" name="delivery_address"
                                    rows="3" required placeholder="Nhập địa chỉ giao hàng chi tiết (số nhà, tên đường, phường/xã, quận/huyện)"></textarea>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Địa chỉ càng chi tiết, shipper sẽ giao hàng càng nhanh
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Số điện thoại *
                                </label>
                                <input type="tel" class="form-control modern-input" id="phone" name="phone"
                                    required placeholder="Nhập số điện thoại liên hệ (10 số)" pattern="[0][0-9]{9}">
                                <div class="form-hint">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Chúng tôi sẽ liên hệ qua số này nếu cần
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="form-group">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-2"></i>Ghi chú đơn hàng
                                </label>
                                <textarea class="form-control modern-input" id="notes" name="notes"
                                    rows="3" placeholder="Ghi chú thêm cho đơn hàng (tùy chọn): Ví dụ: Không cay, ít đường..."></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-checkout">
                                    <span class="btn-content">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Xác nhận đặt hàng
                                    </span>
                                    <span class="btn-shine"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="col-lg-5">
                <div class="checkout-card sticky-summary">
                    <div class="checkout-card-header">
                        <div class="card-header-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="card-header-content">
                            <h3>Tóm tắt đơn hàng</h3>
                            <p>Chi tiết sản phẩm và thanh toán</p>
                        </div>
                    </div>

                    <div class="checkout-card-body">
                        <!-- Order Items -->
                        <div class="order-items-list">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item">
                                    <div class="order-item-image">
                                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/images/placeholder.jpg'); ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <span class="item-quantity"><?php echo $item['quantity']; ?>x</span>
                                    </div>
                                    <div class="order-item-info">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                                    </div>
                                    <div class="order-item-total">
                                        <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Divider -->
                        <div class="summary-divider"></div>

                        <!-- Price Breakdown -->
                        <div class="price-breakdown">
                            <div class="price-row">
                                <span class="price-label">
                                    <i class="fas fa-calculator me-2"></i>Tạm tính
                                </span>
                                <span class="price-value"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                            </div>

                            <div class="price-row">
                                <span class="price-label">
                                    <i class="fas fa-shipping-fast me-2"></i>Phí giao hàng
                                </span>
                                <span class="price-value price-free">
                                    <span class="badge badge-free">Miễn phí</span>
                                </span>
                            </div>

                            <div class="price-row discount-row">
                                <span class="price-label">
                                    <i class="fas fa-tag me-2"></i>Giảm giá
                                </span>
                                <span class="price-value">0đ</span>
                            </div>
                        </div>

                        <!-- Total Divider -->
                        <div class="summary-divider-bold"></div>

                        <!-- Total Price -->
                        <div class="total-price-section">
                            <div class="total-row">
                                <span class="total-label">Tổng cộng</span>
                                <span class="total-value"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                            </div>
                            <p class="total-note">
                                <i class="fas fa-info-circle me-1"></i>
                                Đã bao gồm VAT (nếu có)
                            </p>
                        </div>

                        <!-- Back to Cart Button -->
                        <div class="back-to-cart">
                            <a href="<?php echo BASE_URL; ?>cart" class="btn btn-back">
                                <i class="fas fa-arrow-left me-2"></i>
                                Quay lại giỏ hàng
                            </a>
                        </div>

                        <!-- Trust Badges -->
                        <div class="trust-badges">
                            <div class="trust-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Thanh toán an toàn</span>
                            </div>
                            <div class="trust-badge">
                                <i class="fas fa-truck"></i>
                                <span>Giao hàng nhanh</span>
                            </div>
                            <div class="trust-badge">
                                <i class="fas fa-headset"></i>
                                <span>Hỗ trợ 24/7</span>
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
        document.addEventListener('DOMContentLoaded', function() {
            const checkoutForm = document.getElementById('checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function(event) {
                    const address = document.getElementById('delivery_address').value.trim();
                    const phone = document.getElementById('phone').value.trim();

                    if (!address || !phone) {
                        event.preventDefault(); // Stop form submission
                        alert('Vui lòng điền đầy đủ Địa chỉ giao hàng và Số điện thoại.');
                        return;
                    }

                    const submitButton = checkoutForm.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
                    }
                });
            }
        });
    </script>

</body>

</html>