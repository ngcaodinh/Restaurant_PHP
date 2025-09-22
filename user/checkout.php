<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_permission(['Admin', 'User', 'PremiumUser']);

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT ci.*, d.name, d.price FROM cart_items ci JOIN carts c ON ci.cart_id = c.id JOIN dishes d ON ci.dish_id = d.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
$total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart_items));
?>
<main>
    <section class="checkout">
        <h1>Thanh toán</h1>
        <form id="checkout-form" method="POST">
            <div class="form-group">
                <h3>Địa điểm nhận hàng</h3>
                <label><input type="radio" name="delivery_method" value="pickup" checked> Nhận tại cửa hàng</label>
                <label><input type="radio" name="delivery_method" value="delivery"> Giao hàng tận nơi (25,000đ)</label>
                <div class="delivery-address" style="display: none;">
                    <label for="address">Địa chỉ giao hàng</label>
                    <input type="text" id="address" name="address" required>
                </div>
            </div>
            <div class="form-group">
                <h3>Phương thức thanh toán</h3>
                <label><input type="radio" name="payment_method" value="COD" checked> Thanh toán khi nhận hàng</label>
                <label><input type="radio" name="payment_method" value="Online"> VNPay</label>
            </div>
            <div class="cart-summary">
                <p>Tạm tính: <?php echo format_currency($total); ?></p>
                <p>Phí giao hàng: <span id="delivery-fee"><?php echo format_currency(25000); ?></span></p>
                <p>Tổng cộng: <span id="total"><?php echo format_currency($total + 25000); ?></span></p>
            </div>
            <button type="submit" class="btn">Xác nhận thanh toán</button>
        </form>
    </section>
</main>

<script>
    document.querySelectorAll('input[name="delivery_method"]').forEach(input => {
        input.addEventListener('change', () => {
            const deliveryAddress = document.querySelector('.delivery-address');
            const deliveryFee = document.getElementById('delivery-fee');
            const total = document.getElementById('total');
            const baseTotal = <?php echo $total; ?>;
            if (input.value === 'delivery') {
                deliveryAddress.style.display = 'block';
                deliveryFee.textContent = '<?php echo format_currency(25000); ?>';
                total.textContent = '<?php echo format_currency($total + 25000); ?>';
            } else {
                deliveryAddress.style.display = 'none';
                deliveryFee.textContent = '0đ';
                total.textContent = '<?php echo format_currency($total); ?>';
            }
        });
    });
    document.getElementById('checkout-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch('<?php echo BASE_URL; ?>api/cart_handler.php?action=checkout', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => {
            if (data.success) {
                alert('Đơn hàng đã được đặt thành công!');
                window.location.href = '<?php echo BASE_URL; ?>user/orders.php';
            } else {
                alert(data.message);
            }
        });
    });
</script>