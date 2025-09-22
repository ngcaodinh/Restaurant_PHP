<?php
// ctut-restaurant/user/index.php
require_once '../templates/header.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([get_user_id()]);
$user = $stmt->fetch();
?>
<main>
    <section>
        <h1>Chào mừng, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p>Khám phá menu hoặc xem giỏ hàng của bạn.</p>
        <a href="<?php echo BASE_URL; ?>user/menu.php" class="btn">Xem Menu</a>
        <a href="<?php echo BASE_URL; ?>user/cart.php" class="btn">Giỏ hàng</a>
    </section>
</main>
<?php require_once '../templates/footer.php'; ?>