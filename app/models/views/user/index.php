<?php
// View for user dashboard, preserving legacy UI
require_once __DIR__ . '/../../../templates/header.php';
?>
<main>
    <section>
        <h1>Chào mừng, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p>Khám phá menu hoặc xem giỏ hàng của bạn.</p>
        <a href="<?php echo BASE_URL; ?>user/menu.php" class="btn">Xem Menu</a>
        <a href="<?php echo BASE_URL; ?>user/cart.php" class="btn">Giỏ hàng</a>
    </section>
</main>
<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>

