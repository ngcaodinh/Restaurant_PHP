<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kiểm tra quyền truy cập
check_permission(['Admin', 'User', 'PremiumUser']);

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý thêm món vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $dish_id = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);

    if ($dish_id) {
        try {
            $pdo->beginTransaction();

            // Kiểm tra xem giỏ hàng đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ");
            $stmt->execute([$user_id]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cart) {
                // Tạo giỏ hàng mới
                $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
                $cart_id = $pdo->lastInsertId();
            } else {
                $cart_id = $cart['id'];
            }

            // Kiểm tra món ăn đã có trong giỏ chưa
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND dish_id = ? ");
            $stmt->execute([$cart_id, $dish_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                // Cập nhật số lượng
                $new_quantity = $cart_item['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$new_quantity, $cart_item['id']]);
            } else {
                // Thêm món mới
                $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$cart_id, $dish_id, $quantity]);
            }

            $pdo->commit();
            header('Location: cart.php?success=Thêm món thành công');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: cart.php?error=Không thể thêm món vào giỏ hàng');
            exit();
        }
    }
}

// Xử lý cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
    $cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($cart_item_id && $quantity) {
        try {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? ");
            $stmt->execute([$quantity, $cart_item_id]);
            header('Location: cart.php?success=Cập nhật số lượng thành công');
            exit();
        } catch (Exception $e) {
            header('Location: cart.php?error=Không thể cập nhật số lượng');
            exit();
        }
    }
}

// Xử lý xóa món khỏi giỏ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_item') {
    header('Content-Type: application/json'); // Đặt header JSON
    $cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);

    error_log("cart.php: Received remove_item request, cart_item_id=$cart_item_id"); // Ghi log

    if ($cart_item_id === false || $cart_item_id === null) {
        error_log('cart.php: Invalid cart_item_id input');
        echo json_encode(['success' => false, 'message' => 'ID món không hợp lệ']);
        ob_end_flush();
        exit();
    }

    try {
        // Kiểm tra xem cart_item_id tồn tại và thuộc về user
        $stmt = $pdo->prepare("SELECT ci.id FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE ci.id = ? AND c.user_id = ?");
        $stmt->execute([$cart_item_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
            $stmt->execute([$cart_item_id]);
            error_log("cart.php: Deleted cart item, cart_item_id=$cart_item_id");
            echo json_encode(['success' => true, 'message' => 'Xóa món thành công']);
        } else {
            error_log("cart.php: Invalid cart_item_id=$cart_item_id or not owned by user_id=$user_id");
            echo json_encode(['success' => false, 'message' => 'ID món không hợp lệ hoặc không thuộc giỏ hàng của bạn']);
        }
    } catch (Exception $e) {
        error_log('cart.php: Error removing item - ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Không thể xóa món: ' . $e->getMessage()]);
    }

    ob_end_flush();
    exit();
}
// Lấy danh sách món trong giỏ hàng
try {
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.quantity, d.id AS dish_id, d.name, d.price, d.image
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN dishes d ON ci.dish_id = d.id
        WHERE c.user_id = ? AND d.deleted_at IS NULL
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $cart_items = [];
    $error = "Lỗi khi lấy dữ liệu giỏ hàng";
}

// Tính tổng tiền
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Giỏ hàng CTUT Restaurant - Chọn món ăn yêu thích và thanh toán dễ dàng">
    <title>CTUT Restaurant - Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&libraries=places,geocoding&callback=initMap" async defer></script>
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <!-- Thêm BASE_URL -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>

<body>
    <?php include 'templates/header.php'; ?>

    <!-- Hiển thị thông báo -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Cart Section -->
        <section class="cart-section" aria-label="Giỏ hàng">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h2>
                <p>Chọn món và thanh toán dễ dàng</p>
            </div>

            <!-- Select All Section -->
            <div class="select-all-section">
                <div class="select-all-container">
                    <label class="select-all-checkbox">
                        <input type="checkbox" id="select-all" aria-label="Chọn tất cả món ăn" />
                        <span>Chọn tất cả</span>
                    </label>
                    <div class="selected-info">
                        <span class="selected-count" id="selected-count">0</span>
                        <span>món đã chọn</span>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <div class="search-box">
                    <i class="fas fa-search search-icon" aria-hidden="true"></i>
                    <input
                        type="text"
                        class="search-input"
                        id="search-input"
                        placeholder="Tìm kiếm món ăn trong giỏ hàng..."
                        autocomplete="off"
                        aria-label="Tìm kiếm món ăn trong giỏ hàng">
                    <button class="search-clear" id="search-clear" aria-label="Xóa nội dung tìm kiếm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="cart-items" id="cart-items">
                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Giỏ hàng trống</h3>
                        <p>Thêm món ăn yêu thích của bạn để tiếp tục!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                            <input type="checkbox" class="item-checkbox" data-id="<?php echo $item['id']; ?>" id="item-<?php echo $item['id']; ?>" aria-label="Chọn món <?php echo htmlspecialchars($item['name']); ?>">
                            <img src="<?php echo htmlspecialchars($item['image'] ?? '/Restaurant_PHP/assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image" loading="lazy">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-price"><?php echo number_format($item['price'], 0, ',', '.') . 'đ'; ?></div>
                                <div class="quantity-controls">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="quantity-btn decrease" aria-label="Giảm số lượng <?php echo htmlspecialchars($item['name']); ?>" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                                    </form>
                                    <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="quantity-btn increase" aria-label="Tăng số lượng <?php echo htmlspecialchars($item['name']); ?>">+</button>
                                    </form>
                                </div>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove_item">
                                <input type="hidden" name="cart_item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="remove-btn" aria-label="Xóa món <?php echo htmlspecialchars($item['name']); ?>">
                                    <i class="fas fa-trash-alt"></i> Xóa
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Checkout Section -->
        <section class="checkout-section" aria-label="Thanh toán">
            <div class="checkout-header">
                <h3><i class="fas fa-credit-card"></i> Thanh toán</h3>
            </div>
            <div class="checkout-content">
                <!-- Delivery Options -->
                <div class="delivery-section">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Địa điểm nhận hàng</span>
                    </div>
                    <div class="delivery-options">
                        <div class="delivery-option selected" data-delivery="pickup">
                            <input type="radio" name="delivery" value="pickup" id="pickup" checked aria-label="Nhận tại cửa hàng">
                            <label for="pickup">
                                <strong>Nhận tại cửa hàng</strong>
                                <div style="font-size: 0.9rem; color: #6b7280;">Miễn phí - Sẵn sàng trong 15 phút</div>
                            </label>
                        </div>
                        <div class="delivery-option" data-delivery="delivery">
                            <input type="radio" name="delivery" value="delivery" id="delivery" aria-label="Giao hàng tận nơi">
                            <label for="delivery">
                                <strong>Giao hàng tận nơi</strong>
                                <div style="font-size: 0.9rem; color: #6b7280;">Phí giao hàng 25,000đ - 30-45 phút</div>
                            </label>
                        </div>
                        <div class="address-input-container" id="address-input-container">
                            <input
                                type="text"
                                id="address-input"
                                class="address-input"
                                placeholder="Nhập địa chỉ giao hàng..."
                                aria-label="Nhập địa chỉ giao hàng">
                            <input
                                type="tel"
                                id="phone-input"
                                class="phone-input"
                                placeholder="Nhập số điện thoại (10 số)..."
                                aria-label="Nhập số điện thoại"
                                pattern="[0][0-9]{9}">
                            <button class="confirm-address-btn" id="confirm-address-btn" disabled aria-label="Xác nhận địa chỉ">
                                Xác nhận địa chỉ
                            </button>
                            <div id="map"></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class="fas fa-wallet"></i>
                        <span>Phương thức thanh toán</span>
                    </div>
                    <div class="payment-methods">
                        <div class="payment-method selected" data-payment="cod">
                            <input type="radio" name="payment" value="cod" id="cod" checked aria-label="Thanh toán khi nhận hàng">
                            <label for="cod">
                                <strong>Thanh toán khi nhận hàng</strong>
                                <div style="font-size: 0.9rem; color: #6b7280;">Tiền mặt hoặc thẻ</div>
                            </label>
                        </div>
                        <div class="payment-method" data-payment="vnpay">
                            <input type="radio" name="payment" value="vnpay" id="vnpay" aria-label="Thanh toán bằng VNPay">
                            <label for="vnpay">
                                <strong>VNPay</strong>
                                <div style="font-size: 0.9rem; color: #6b7280;">Thanh toán online an toàn</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span id="subtotal"><?php echo number_format($total, 0, ',', '.') . 'đ'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Phí giao hàng:</span>
                        <span id="delivery-fee">0đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Giảm giá:</span>
                        <span id="discount">0đ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span id="total"><?php echo number_format($total, 0, ',', '.') . 'đ'; ?></span>
                    </div>
                </div>

                <a href="checkout.php" class="checkout-btn" id="checkout-btn" aria-label="Thanh toán đơn hàng">
                    <i class="fas fa-check-circle"></i> Thanh toán (<span id="checkout-count"><?php echo count($cart_items); ?></span> món)
                </a>
            </div>
        </section>
    </div>

    <script src="/Restaurant_PHP/assets/js/cart.js"></script>
</body>

</html>