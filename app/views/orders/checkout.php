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
    <div class="background-overlay"></div>
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
                    <i class="fas fa-credit-card"></i> Thanh toán đơn hàng
                </h2>
            </div>
        </div>

        <?php if (isset($_SESSION['checkout_errors']) && !empty($_SESSION['checkout_errors'])): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['checkout_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['checkout_errors']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Thông tin giao hàng</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkout-form" method="POST" action="<?php echo BASE_URL; ?>checkout">
                            <input type="hidden" name="checkout_items" value="<?php echo htmlspecialchars(json_encode(array_column($cartItems, 'id'))); ?>">
                            <div class="mb-3">
                                <input type="hidden" name="total_price" value="<?php echo $cartTotal; ?>">
                                <label for="delivery_address" class="form-label">Địa chỉ giao hàng *</label>
                                <textarea class="form-control" id="delivery_address" name="delivery_address"
                                    rows="3" required placeholder="Nhập địa chỉ giao hàng chi tiết"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại *</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    required placeholder="Nhập số điện thoại liên hệ">
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Ghi chú</label>
                                <textarea class="form-control" id="notes" name="notes"
                                    rows="2" placeholder="Ghi chú thêm cho đơn hàng (tùy chọn)"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check"></i> Xác nhận đặt hàng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Tóm tắt đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="order-summary">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                        <small class="text-muted"> x<?php echo $item['quantity']; ?></small>
                                    </div>
                                    <span><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</span>
                                </div>
                            <?php endforeach; ?>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí giao hàng:</span>
                                <span class="text-success">Miễn phí</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <strong>Tổng cộng:</strong>
                                <strong class="text-primary"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>cart" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                    </a>
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