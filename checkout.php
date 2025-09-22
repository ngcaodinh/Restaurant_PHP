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
$cart_items = [];
$error = '';
$total = 0;
$delivery_address = '';
$phone = '';

try {
    // Lấy dữ liệu giỏ hàng từ cơ sở dữ liệu
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.quantity, d.id AS dish_id, d.name, d.price, d.image
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN dishes d ON ci.dish_id = d.id
        WHERE c.user_id = ? AND d.deleted_at IS NULL
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['cart'] = $cart_items; // Đồng bộ session
    error_log('checkout.php: Fetched cart items, count=' . count($cart_items));

    // Tính tổng giá trị
    $total = array_sum(array_map(function($item) {
        return (float)$item['price'] * (int)$item['quantity'];
    }, $cart_items));
    error_log('checkout.php: Total calculated=' . $total);

    // Xử lý tạo đơn hàng khi nhấn nút "Xác nhận thanh toán"
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
        $delivery_address = trim($_POST['delivery_address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'COD';

        // Kiểm tra dữ liệu đầu vào
        if (empty($cart_items)) {
            $error = "Giỏ hàng trống, không thể tạo đơn hàng.";
        } elseif (empty($delivery_address)) {
            $error = "Vui lòng nhập địa chỉ giao hàng.";
        } elseif (empty($phone) || !preg_match('/^[0-9]{10,15}$/', $phone)) {
            $error = "Vui lòng nhập số điện thoại hợp lệ (10-15 chữ số).";
        } else {
            try {
                // Bắt đầu giao dịch
                $pdo->beginTransaction();

                // Tạo đơn hàng trong bảng orders
                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_price, delivery_address, phone, payment_method, status)
                    VALUES (?, ?, ?, ?, ?, 'Pending')
                ");
                $stmt->execute([$user_id, $total, $delivery_address, $phone, $payment_method]);
                $order_id = $pdo->lastInsertId();
                error_log("checkout.php: Created order, order_id=$order_id");

                // Thêm các mục đơn hàng vào order_items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, dish_id, quantity, price, dish_name)
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($cart_items as $item) {
                    $stmt->execute([$order_id, $item['dish_id'], $item['quantity'], $item['price'], $item['name']]);
                }
                error_log("checkout.php: Added order items for order_id=$order_id");

                // Cập nhật sales_count trong bảng dishes
                $stmt = $pdo->prepare("
                    UPDATE dishes 
                    SET sales_count = sales_count + ? 
                    WHERE id = ? AND deleted_at IS NULL
                ");
                foreach ($cart_items as $item) {
                    $stmt->execute([$item['quantity'], $item['dish_id']]);
                    error_log("checkout.php: Updated sales_count for dish_id={$item['dish_id']}, quantity={$item['quantity']}");
                }

                // Xóa giỏ hàng sau khi tạo đơn hàng
                $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $cart = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($cart) {
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                    $stmt->execute([$cart['id']]);
                    error_log("checkout.php: Cleared cart items for cart_id={$cart['id']}");
                }

                // Xóa session cart
                $_SESSION['cart'] = [];
                $pdo->commit();
                $_SESSION['success_message'] = "Đơn hàng đã được tạo thành công! Mã đơn hàng: #$order_id";
                header('Location: order_confirmation.php');
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('checkout.php: Error creating order - ' . $e->getMessage());
                $error = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
            }
        }
    }
} catch (Exception $e) {
    error_log('checkout.php: Error fetching cart items - ' . $e->getMessage());
    $error = "Lỗi khi lấy dữ liệu giỏ hàng: " . $e->getMessage();
    $cart_items = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - CTUT Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/checkout.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">

</head>
<body>
    <div class="background-overlay"></div>
    <?php include 'templates/header.php'; ?>
    <div class="container">
        <div class="checkout-box">
            <h2>Thanh toán</h2>
            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($cart_items)): ?>
                <div class="empty-cart text-center">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <h3>Giỏ hàng trống</h3>
                    <p>Thêm món ăn từ menu để bắt đầu!</p>
                    <a href="<?php echo BASE_URL; ?>index.php#dishes" class="btn btn-primary">Xem menu</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <h4>Chi tiết đơn hàng</h4>
                        <div class="cart-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item">
                                    <img src="<?php echo htmlspecialchars($item['image'] ?: 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div class="cart-item-info">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p>Giá: <?php echo number_format((float)$item['price'], 0, ',', '.'); ?>đ</p>
                                        <p>Số lượng: <?php echo htmlspecialchars((int)$item['quantity']); ?></p>
                                        <p>Tổng: <?php echo number_format((float)$item['price'] * (int)$item['quantity'], 0, ',', '.'); ?>đ</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="cart-total">
                            <h4>Tổng cộng: <?php echo number_format($total, 0, ',', '.'); ?>đ</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>Thông tin giao hàng</h4>
                        <form method="POST">
                            <div class="form-group">
                                <label for="delivery_address" class="form-label">Địa chỉ giao hàng</label>
                                <textarea class="form-control" id="delivery_address" name="delivery_address" rows="4" required><?php echo htmlspecialchars($delivery_address); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required pattern="[0-9]{10,15}">
                            </div>
                            <div class="form-group">
                                <label for="payment_method" class="form-label">Phương thức thanh toán</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="COD" <?php echo ($payment_method ?? 'COD') === 'COD' ? 'selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option>
                                    <option value="Online" <?php echo ($payment_method ?? '') === 'Online' ? 'selected' : ''; ?>>Thanh toán trực tuyến</option>
                                </select>
                            </div>
                            <button type="submit" name="confirm_checkout" class="btn btn-primary">Xác nhận thanh toán</button>
                            <a href="<?php echo BASE_URL; ?>cart.php" class="btn btn-secondary mt-2">Quay lại giỏ hàng</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

</body>
</html>