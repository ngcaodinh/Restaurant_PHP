<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/cart.css">
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
                    <i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn
                    <span class="badge bg-primary ms-2"><?php echo $cartCount; ?></span>
                </h2>
            </div>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="row">
                <div class="col-12 text-center">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                        <h3>Giỏ hàng trống</h3>
                        <p class="text-muted">Bạn chưa có món ăn nào trong giỏ hàng</p>
                        <a href="/" class="btn btn-primary">
                            <i class="fas fa-utensils"></i> Xem thực đơn
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-dish-id="<?php echo $item['id']; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?php echo htmlspecialchars($item['image'] ?: 'https://via.placeholder.com/100x100?text=No+Image'); ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-4">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-muted mb-0"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="quantity-controls">
                                            <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="quantity mx-2"><?php echo $item['quantity']; ?></span>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <strong><?php echo number_format($item['total_price'], 0, ',', '.'); ?>đ</strong>
                                    </div>
                                    <div class="col-md-1">
                                        <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tạm tính:</span>
                                    <span id="subtotal"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phí giao hàng:</span>
                                    <span>Miễn phí</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Tổng cộng:</strong>
                                    <strong id="total"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="/checkout" class="btn btn-primary btn-lg">
                                        <i class="fas fa-credit-card"></i> Thanh toán
                                    </a>
                                    <button class="btn btn-outline-secondary" onclick="clearCart()">
                                        <i class="fas fa-trash"></i> Xóa tất cả
                                    </button>
                                </div>
                            </div>
                        </div>
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
        const BASE_URL = '<?php echo BASE_URL; ?>';

        function updateQuantity(dishId, quantity) {
            if (quantity < 0) return;

            fetch('/api/cart/update-quantity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `dish_id=${dishId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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

        function removeFromCart(dishId) {
            if (!confirm('Bạn có chắc muốn xóa món này khỏi giỏ hàng?')) return;

            fetch('/api/cart/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `dish_id=${dishId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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

        function clearCart() {
            if (!confirm('Bạn có chắc muốn xóa tất cả món trong giỏ hàng?')) return;

            fetch('/api/cart/clear', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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