<?php
// ctut-restaurant/user/cart.php
require_once '../templates/header.php';
require_login();

$user_id = get_user_id();
$stmt = $pdo->prepare("SELECT ci.*, d.name, d.price FROM cart_items ci JOIN carts c ON ci.cart_id = c.id JOIN dishes d ON ci.dish_id = d.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
$total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart_items));
?>
<main>
    <section class="cart">
        <h1>Giỏ hàng của bạn</h1>
        <p>Chọn món và thanh toán dễ dàng</p>
        <div class="cart-items">
            <?php if (empty($cart_items)): ?>
                <p>Giỏ hàng trống</p>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><?php echo format_currency($item['price']); ?> x <input type="number" class="quantity" value="<?php echo $item['quantity']; ?>" min="1"></p>
                        <button class="btn remove-item">Xóa</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="cart-summary">
            <p>Tạm tính: <?php echo format_currency($total); ?></p>
            <p>Phí giao hàng: <?php echo format_currency(25000); ?></p>
            <p>Tổng cộng: <?php echo format_currency($total + 25000); ?></p>
            <a href="<?php echo BASE_URL; ?>user/checkout.php" class="btn">Thanh toán (<?php echo count($cart_items); ?> món)</a>
        </div>
    </section>
</main>
<?php require_once '../templates/footer.php'; ?>
<script>
    document.querySelectorAll('.quantity').forEach(input => {
        input.addEventListener('change', () => {
            fetch('<?php echo BASE_URL; ?>api/cart_handler.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: input.closest('.cart-item').dataset.id, quantity: input.value })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        });
    });
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', () => {
            fetch('<?php echo BASE_URL; ?>api/cart_handler.php?action=remove', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: button.closest('.cart-item').dataset.id })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        });
    });
</script>