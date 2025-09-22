<?php
// ctut-restaurant/user/profile.php
require_once '../templates/header.php';
require_login();

$user_id = get_user_id();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $address = sanitize_input($_POST['address']);
    $phone = sanitize_input($_POST['phone']);
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, address = ?, phone = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$name, $address, $phone, $user_id]);
    set_flash_message('success', 'Cập nhật hồ sơ thành công!');
    redirect(BASE_URL . 'user/profile.php');
}
?>
<main>
    <section class="profile">
        <h1>Hồ sơ cá nhân</h1>
        <form method="POST">
            <div class="form-group">
                <label for="name">Họ và tên</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
            <button type="submit" class="btn">Cập nhật</button>
        </form>
    </section>
</main>
<?php require_once '../templates/footer.php'; ?>