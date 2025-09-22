<?php
// ctut-restaurant/user/menu.php
require_once '../templates/header.php';

$stmt = $pdo->query("SELECT d.*, c.name as category_name FROM dishes d JOIN categories c ON d.category_id = c.id WHERE d.status = 'Available' AND d.deleted_at IS NULL");
$dishes = $stmt->fetchAll();
?>
<main>
    <section class="menu">
        <h1>Menu</h1>
        <div class="dish-list">
            <?php foreach ($dishes as $dish): ?>
                <div class="dish-card" data-category="<?php echo $dish['category_name']; ?>">
                    <h3><?php echo htmlspecialchars($dish['name']); ?></h3>
                    <p><?php echo htmlspecialchars($dish['description']); ?></p>
                    <p><?php echo format_currency($dish['price']); ?></p>
                    <button class="btn add-to-cart" data-id="<?php echo $dish['id']; ?>">Thêm vào giỏ</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require_once '../templates/footer.php'; ?>
<script>
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            fetch('<?php echo BASE_URL; ?>api/cart_handler.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dish_id: button.dataset.id, quantity: 1 })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.cart_count;
                    alert('Đã thêm vào giỏ hàng!');
                } else {
                    alert(data.message);
                }
            });
        });
    });
</script>