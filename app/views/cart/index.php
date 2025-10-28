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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .cart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-top: 120px;
            margin-bottom: 40px;
            overflow: hidden;
        }

        .cart-header {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .cart-header h2 {
            margin: 0;
            font-weight: 800;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .cart-item {
            background: white;
            border-radius: 15px;
            margin-bottom: 1rem;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .item-name {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .item-price {
            color: #e53e3e;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        .quantity-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            transform: none;
        }

        .quantity-display {
            background: #f7fafc;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            min-width: 50px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }

        .remove-btn {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
        }

        .cart-summary {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: sticky;
            top: 140px;
        }

        .summary-header {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .summary-content {
            padding: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }

        .summary-row.total {
            border-top: 2px solid #e2e8f0;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: #2d3748;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(72, 187, 120, 0.3);
        }

        .clear-btn {
            width: 100%;
            padding: 0.8rem;
            background: transparent;
            color: #718096;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .clear-btn:hover {
            background: #fed7d7;
            border-color: #fc8181;
            color: #e53e3e;
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .empty-cart h3 {
            color: #4a5568;
            margin-bottom: 1rem;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, .3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .cart-container {
                margin-top: 100px;
                margin-left: 1rem;
                margin-right: 1rem;
            }

            .cart-item {
                padding: 1rem;
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .cart-summary {
                position: static;
                margin-top: 2rem;
            }
        }
    </style>
</head>

<body>
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    ?>

    <div class="container">
        <div class="cart-container">
            <div class="cart-header">
                <h2>
                    <i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn
                    <span class="badge bg-light text-dark ms-2" id="cart-count"><?php echo $cartCount; ?></span>
                </h2>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Giỏ hàng trống</h3>
                    <p>Bạn chưa có món ăn nào trong giỏ hàng</p>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-utensils"></i> Xem thực đơn
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="cart-items p-3">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item" data-dish-id="<?php echo $item['id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 col-3">
                                            <img src="<?php echo htmlspecialchars($item['image'] ?: 'https://via.placeholder.com/100x100?text=No+Image'); ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                class="item-image">
                                        </div>
                                        <div class="col-md-4 col-9">
                                            <h5 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <p class="item-price mb-0"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                                        </div>
                                        <div class="col-md-3 col-6 mt-2 mt-md-0">
                                            <div class="quantity-controls">
                                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-4 mt-2 mt-md-0">
                                            <strong class="item-price"><?php echo number_format($item['total_price'], 0, ',', '.'); ?>đ</strong>
                                        </div>
                                        <div class="col-md-1 col-2 mt-2 mt-md-0">
                                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
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
                            <div class="summary-header">
                                <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                            </div>
                            <div class="summary-content">
                                <div class="summary-row">
                                    <span>Tạm tính:</span>
                                    <span id="subtotal"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</span>
                                </div>
                                <div class="summary-row">
                                    <span>Phí giao hàng:</span>
                                    <span>Miễn phí</span>
                                </div>
                                <div class="summary-row total">
                                    <strong>Tổng cộng:</strong>
                                    <strong id="total"><?php echo number_format($cartTotal, 0, ',', '.'); ?>đ</strong>
                                </div>
                                <button class="checkout-btn" onclick="goToCheckout()">
                                    <i class="fas fa-credit-card"></i> Thanh toán
                                </button>
                                <button class="clear-btn" onclick="clearCart()">
                                    <i class="fas fa-trash"></i> Xóa tất cả
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

        function showLoading(element) {
            element.disabled = true;
            element.innerHTML = '<span class="spinner"></span> Đang xử lý...';
        }

        function hideLoading(element, originalText) {
            element.disabled = false;
            element.innerHTML = originalText;
        }

        function updateCartDisplay(cartData) {
            if (cartData.items) {
                // Update cart items dynamically
                const cartItemsContainer = document.querySelector('.cart-items');
                if (cartData.items.length === 0) {
                    // Show empty cart
                    document.querySelector('.cart-container').innerHTML = `
                        <div class="cart-header">
                            <h2><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn <span class="badge bg-light text-dark ms-2">0</span></h2>
                        </div>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Giỏ hàng trống</h3>
                            <p>Bạn chưa có món ăn nào trong giỏ hàng</p>
                            <a href="${BASE_URL}" class="btn btn-primary">
                                <i class="fas fa-utensils"></i> Xem thực đơn
                            </a>
                        </div>
                    `;
                } else {
                    // Update quantities and totals
                    document.getElementById('cart-count').textContent = cartData.count;
                    document.getElementById('subtotal').textContent = new Intl.NumberFormat('vi-VN').format(cartData.total) + 'đ';
                    document.getElementById('total').textContent = new Intl.NumberFormat('vi-VN').format(cartData.total) + 'đ';
                }
            }
        }

        function updateQuantity(dishId, quantity) {
            if (quantity < 0) return;

            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            showLoading(button);

            fetch(BASE_URL + 'api/cart/update-quantity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `dish_id=${dishId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading(button, originalText);
                    if (data.success) {
                        if (quantity === 0) {
                            // Remove item from DOM
                            const cartItem = document.querySelector(`[data-dish-id="${dishId}"]`);
                            cartItem.style.animation = 'slideOut 0.3s ease-in-out forwards';
                            setTimeout(() => {
                                cartItem.remove();
                                // Check if cart is empty
                                const remainingItems = document.querySelectorAll('.cart-item');
                                if (remainingItems.length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        } else {
                            location.reload(); // For now, reload to update totals
                        }
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    hideLoading(button, originalText);
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật giỏ hàng');
                });
        }

        function removeFromCart(dishId) {
            if (!confirm('Bạn có chắc muốn xóa món này khỏi giỏ hàng?')) return;

            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            showLoading(button);

            fetch(BASE_URL + 'api/cart/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `dish_id=${dishId}`
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading(button, originalText);
                    if (data.success) {
                        const cartItem = document.querySelector(`[data-dish-id="${dishId}"]`);
                        cartItem.style.animation = 'slideOut 0.3s ease-in-out forwards';
                        setTimeout(() => {
                            cartItem.remove();
                            // Check if cart is empty
                            const remainingItems = document.querySelectorAll('.cart-item');
                            if (remainingItems.length === 0) {
                                location.reload();
                            } else {
                                // Update totals
                                location.reload();
                            }
                        }, 300);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    hideLoading(button, originalText);
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa món khỏi giỏ hàng');
                });
        }

        function clearCart() {
            if (!confirm('Bạn có chắc muốn xóa tất cả món trong giỏ hàng?')) return;

            const button = event.target;
            const originalText = button.innerHTML;
            showLoading(button);

            fetch(BASE_URL + 'api/cart/clear', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading(button, originalText);
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    hideLoading(button, originalText);
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa giỏ hàng');
                });
        }

        function goToCheckout() {
            window.location.href = BASE_URL + 'checkout';
        }

        // Add slide-out animation for removed items
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>