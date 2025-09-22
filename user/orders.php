<?php
// ctut-restaurant/user/orders.php
require_once '../templates/header.php';
require_login();

$user_id = get_user_id();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<main>
    <section class="orders">
        <h1>Lịch sử đơn hàng</h1>
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Tổng tiền</th>
                    <th>Phương thức</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="6">Bạn chưa có đơn hàng nào</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo format_currency($order['total_price']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><button class="btn" onclick="viewOrder(<?php echo $order['id']; ?>)">Xem</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require_once '../templates/footer.php'; ?>
<script>
    function viewOrder(id) {
        fetch('<?php echo BASE_URL; ?>api/order_handler.php?action=get&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display order details (implement modal for viewing)
                    alert('Chức năng xem chi tiết đang được phát triển!');
                } else {
                    alert(data.message);
                }
            });
    }
</script>