<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Giỏ hàng CTUT Restaurant - Chọn món ăn yêu thích và thanh toán dễ dàng">
    <title>CTUT Restaurant - Giỏ hàng</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/header.css">

    <!-- Base URL for JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        <?php
        if (isset($_SESSION['error_message'])) {
            echo 'const sessionErrorMessage = ' . json_encode($_SESSION['error_message']) . ';';
            unset($_SESSION['error_message']);
        }
        ?>
    </script>
</head>

<body>
    <!-- Background Overlay -->
    <div class="background-overlay"></div>

    <!-- Header -->
    <?php include 'templates/header.php'; ?>

    <!-- Alert Messages -->
    <div id="alert-container" class="alert-container"></div>

    <!-- Main Content -->
    <div class="container-xxl px-3">
        <div class="row g-3">
            <!-- Cart Items Section -->
            <div class="col-lg-7 col-md-7">
                <div class="cart-section">
                    <!-- Cart Header -->
                    <div class="cart-header">
                        <h2><i class="fas fa-shopping-cart me-3"></i>Giỏ hàng của bạn</h2>
                        <p class="mb-0">Chọn món và thanh toán dễ dàng</p>
                    </div>



                    <?php if (!empty($cart_items)): ?>
                        <!-- Select All Section -->
                        <div class="select-all-section">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <label class="select-all-checkbox d-flex align-items-center">
                                    <input type="checkbox" id="select-all" class="me-2">
                                    <span>Chọn tất cả</span>
                                </label>
                                <div class="selected-info">
                                    <span class="selected-count" id="selected-count">0</span>
                                    <span>món đã chọn</span>
                                </div>
                            </div>
                        </div>

                        <!-- Search Section -->
                        <div class="search-section">
                            <div class="search-box position-relative">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-input" id="search-input"
                                    placeholder="Tìm kiếm món ăn trong giỏ hàng..." autocomplete="off">
                                <button class="search-clear" id="search-clear">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Cart Items -->
                    <div class="cart-items" id="cart-items">
                        <?php if (empty($cart_items)): ?>
                            <div class="empty-cart text-center py-5">
                                <i class="fas fa-shopping-cart mb-3"></i>
                                <h3>Giỏ hàng trống</h3>
                                <p class="mb-4">Thêm món ăn yêu thích của bạn để tiếp tục!</p>
                                <a href="index.php" class="btn btn-primary btn-view-menu">
                                    <i class="fas fa-utensils me-2"></i>Xem thực đơn
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-id="<?php echo $item['id']; ?>">

                                    <div class="item-checkbox-container">
                                        <input type="checkbox" class="item-checkbox"
                                            data-id="<?php echo $item['id']; ?>"
                                            id="item-<?php echo $item['id']; ?>">
                                    </div>

                                    <div class="item-image-container">
                                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/images/placeholder.jpg'); ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            class="item-image" loading="lazy">
                                    </div>

                                    <div class="item-info flex-grow-1">
                                        <h5 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <div class="item-price fw-bold text-danger">
                                            <?php echo number_format($item['price'], 0, ',', '.') . 'đ'; ?>
                                        </div>
                                    </div>

                                    <div class="item-controls">
                                        <div class="quantity-controls d-flex align-items-center">
                                            <button type="button" class="quantity-btn decrease"
                                                data-cart-item-id="<?php echo $item['id']; ?>"
                                                data-action="decrease"
                                                <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="quantity-display mx-3"><?php echo $item['quantity']; ?></span>
                                            <button type="button" class="quantity-btn increase"
                                                data-cart-item-id="<?php echo $item['id']; ?>"
                                                data-action="increase">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="remove-btn ms-3"
                                                data-cart-item-id="<?php echo $item['id']; ?>"
                                                title="Xóa món khỏi giỏ hàng">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Checkout Section -->
            <div class="col-lg-5 col-md-5">
                <div class="checkout-section sticky-top">
                    <!-- Checkout Header -->
                    <div class="checkout-header">
                        <h3><i class="fas fa-credit-card me-2"></i>Thanh toán</h3>
                    </div>

                    <div class="checkout-content">
                        <!-- Delivery Options -->
                        <div class="delivery-section mb-4">
                            <div class="section-title mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span>Địa điểm nhận hàng</span>
                            </div>
                            <div class="delivery-options">
                                <div class="delivery-option selected" data-delivery="pickup">
                                    <input type="radio" name="delivery" value="pickup" id="pickup" checked>
                                    <label for="pickup" class="flex-grow-1">
                                        <strong>Nhận tại cửa hàng</strong>
                                        <div class="text-muted small">Miễn phí - Sẵn sàng trong 15 phút</div>
                                    </label>
                                </div>
                                <div class="delivery-option" data-delivery="delivery">
                                    <input type="radio" name="delivery" value="delivery" id="delivery">
                                    <label for="delivery" class="flex-grow-1">
                                        <strong>Giao hàng tận nơi</strong>
                                        <div class="text-muted small">Phí giao hàng 25,000đ - 30-45 phút</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Address Input Container -->
                            <div class="address-input-container" id="address-input-container">
                                <div class="mt-3">
                                    <input type="text" id="address-input" class="form-control mb-2"
                                        placeholder="Nhập địa chỉ giao hàng...">
                                    <input type="tel" id="phone-input" class="form-control mb-2"
                                        placeholder="Nhập số điện thoại (10 số)..." pattern="[0][0-9]{9}">
                                    <button class="btn btn-outline-primary w-100" id="confirm-address-btn" disabled>
                                        Xác nhận địa chỉ
                                    </button>
                                    <div id="map" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="payment-section mb-4">
                            <div class="section-title mb-3">
                                <i class="fas fa-wallet me-2"></i>
                                <span>Phương thức thanh toán</span>
                            </div>
                            <div class="payment-methods">
                                <div class="payment-method selected" data-payment="cod">
                                    <input type="radio" name="payment" value="cod" id="cod" checked>
                                    <label for="cod" class="flex-grow-1">
                                        <strong>Thanh toán khi nhận hàng</strong>
                                        <div class="text-muted small">Tiền mặt hoặc thẻ</div>
                                    </label>
                                </div>
                                <div class="payment-method" data-payment="vnpay">
                                    <input type="radio" name="payment" value="vnpay" id="vnpay">
                                    <label for="vnpay" class="flex-grow-1">
                                        <strong>VNPay</strong>
                                        <div class="text-muted small">Thanh toán online an toàn</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary mb-4">
                            <div class="summary-row d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span id="subtotal"><?php echo number_format($total, 0, ',', '.') . 'đ'; ?></span>
                            </div>
                            <div class="summary-row d-flex justify-content-between mb-2">
                                <span>Phí giao hàng:</span>
                                <span id="delivery-fee">0đ</span>
                            </div>
                            <div class="summary-row d-flex justify-content-between mb-2">
                                <span>Giảm giá:</span>
                                <span id="discount">0đ</span>
                            </div>
                            <hr>
                            <div class="summary-row total d-flex justify-content-between">
                                <span class="fw-bold">Tổng cộng:</span>
                                <span id="total" class="fw-bold text-danger">
                                    <?php echo number_format($total, 0, ',', '.') . 'đ'; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <form action="<?php echo BASE_URL; ?>cart/process-selection" method="POST" id="checkout-form">
                            <input type="hidden" name="selected_items" id="selected-items-input">
                            <button type="button" class="checkout-btn btn btn-success w-100" id="checkout-btn"
                                <?php echo empty($cart_items) ? 'disabled' : ''; ?>>
                                <i class="fas fa-check-circle me-2"></i>
                                Thanh toán (<span id="checkout-count">0</span> món)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&libraries=places,geocoding&callback=initMap" async defer></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/cart.js"></script>
</body>

</html>