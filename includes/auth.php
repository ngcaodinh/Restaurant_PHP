<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra xem người dùng có đăng nhập hay không
 * @return bool True nếu đã đăng nhập, False nếu chưa
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Lấy vai trò của người dùng hiện tại
 * @return string|null Vai trò của người dùng hoặc null nếu chưa đăng nhập
 */
function get_user_role()
{
    return is_logged_in() ? ($_SESSION['user_role'] ?? null) : null;
}

/**
 * Kiểm tra xem người dùng có phải là Admin không
 * @return bool True nếu là Admin, False nếu không
 */
function is_admin()
{
    return get_user_role() === 'Admin';
}

/**
 * Kiểm tra xem người dùng có phải là PremiumUser không
 * @return bool True nếu là PremiumUser, False nếu không
 */
function is_premium()
{
    return get_user_role() === 'PremiumUser';
}

/**
 * Kiểm tra xem người dùng có phải là Admin hoặc PremiumUser không
 * @return bool True nếu là Admin hoặc PremiumUser, False nếu không
 */
function is_premium_or_admin()
{
    return in_array(get_user_role(), ['Admin', 'PremiumUser']);
}

/**
 * Kiểm tra quyền truy cập dựa trên vai trò
 * @param array $allowed_roles Mảng các vai trò được phép truy cập
 * @return void Chuyển hướng nếu không có quyền
 */
function check_permission(array $allowed_roles)
{
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Vui lòng đăng nhập để truy cập.';
        header('Location: login.php');
        exit;
    }

    $user_role = get_user_role();
    if (!in_array($user_role, $allowed_roles)) {
        $_SESSION['error_message'] = 'Bạn không có quyền truy cập.';
        header('Location: index.php');
        exit;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * Thêm món ăn vào giỏ hàng
 * @param int $dish_id ID của món ăn
 * @return array Kết quả xử lý
 */
function add_to_cart($dish_id) {
    global $pdo;
    if (!is_logged_in()) {
        error_log('add_to_cart: User not logged in');
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục'];
    }
    $user_id = $_SESSION['user_id'];
    error_log("add_to_cart: user_id=$user_id, dish_id=$dish_id");

    if (!filter_var($dish_id, FILTER_VALIDATE_INT) || $dish_id <= 0) {
        error_log('add_to_cart: Invalid dish_id');
        return ['success' => false, 'message' => 'Món ăn không hợp lệ'];
    }

    try {
        // Kiểm tra kết nối PDO
        if (!$pdo) {
            error_log('add_to_cart: PDO connection is null');
            return ['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu'];
        }

        // Kiểm tra món ăn tồn tại
        $stmt = $pdo->prepare("SELECT id FROM dishes WHERE id = ? ");
        $stmt->execute([$dish_id]);
        if (!$stmt->fetch()) {
            error_log("add_to_cart: Dish not found, dish_id=$dish_id");
            return ['success' => false, 'message' => 'Món ăn không tồn tại'];
        }

        // Kiểm tra giỏ hàng
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
            error_log("add_to_cart: Created new cart, cart_id=$cart_id");
        } else {
            $cart_id = $cart['id'];
            error_log("add_to_cart: Found existing cart, cart_id=$cart_id");
        }

        // Kiểm tra món đã có trong giỏ
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND dish_id = ?");
        $stmt->execute([$cart_id, $dish_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$item['id']]);
            error_log("add_to_cart: Updated quantity for cart_item_id={$item['id']}");
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$cart_id, $dish_id]);
            error_log("add_to_cart: Added new cart item, dish_id=$dish_id");
        }

        // Cập nhật $_SESSION['cart']
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
        $stmt = $pdo->prepare("
            SELECT d.id, d.name, d.price, d.image, ci.quantity 
            FROM dishes d 
            JOIN cart_items ci ON d.id = ci.dish_id 
            WHERE ci.cart_id = ? 
        ");
        $stmt->execute([$cart_id]);
        $_SESSION['cart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log('add_to_cart: Updated session cart, count=' . count($_SESSION['cart']));

        return ['success' => true, 'message' => 'Đã thêm món vào giỏ hàng'];
    } catch (Exception $e) {
        error_log('add_to_cart: Exception - ' . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi khi thêm món vào giỏ hàng: ' . $e->getMessage()];
    }
}

/**
 * Mua ngay món ăn
 * @param int $dish_id ID của món ăn
 * @return array Kết quả xử lý
 */
function buy_now($dish_id) {
    global $pdo;
    if (!is_logged_in()) {
        error_log('buy_now: User not logged in');
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục'];
    }
    $user_id = $_SESSION['user_id'];
    error_log("buy_now: user_id=$user_id, dish_id=$dish_id");

    if (!filter_var($dish_id, FILTER_VALIDATE_INT) || $dish_id <= 0) {
        error_log('buy_now: Invalid dish_id');
        return ['success' => false, 'message' => 'Món ăn không hợp lệ'];
    }

    try {
        // Kiểm tra kết nối PDO
        if (!$pdo) {
            error_log('buy_now: PDO connection is null');
            return ['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu'];
        }

        // Kiểm tra món ăn tồn tại
        $stmt = $pdo->prepare("SELECT id FROM dishes WHERE id = ? ");
        $stmt->execute([$dish_id]);
        if (!$stmt->fetch()) {
            error_log("buy_now: Dish not found, dish_id=$dish_id");
            return ['success' => false, 'message' => 'Món ăn không tồn tại'];
        }

        // Kiểm tra giỏ hàng
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
            error_log("buy_now: Created new cart, cart_id=$cart_id");
        } else {
            $cart_id = $cart['id'];
            error_log("buy_now: Found existing cart, cart_id=$cart_id");
        }

        // Xóa các món hiện có
       $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        error_log("buy_now: Cleared existing cart items for cart_id=$cart_id");

        // Thêm món mới
        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$cart_id, $dish_id]);
        error_log("buy_now: Added new cart item, dish_id=$dish_id");

        // Cập nhật $_SESSION['cart']
        $_SESSION['cart'] = [];
        $stmt = $pdo->prepare("
            SELECT d.id, d.name, d.price, d.image, ci.quantity 
            FROM dishes d 
            JOIN cart_items ci ON d.id = ci.dish_id 
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart_id]);
        $_SESSION['cart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log('buy_now: Updated session cart, count=' . count($_SESSION['cart']));

        return ['success' => true, 'message' => 'Đã thêm món để thanh toán'];
    } catch (Exception $e) {
        error_log('buy_now: Exception - ' . $e->getMessage());
        return ['success' => false, 'message' => 'Lỗi khi thêm món vào giỏ hàng: ' . $e->getMessage()];
    }
}

/**
 * Thêm món ăn vào danh sách yêu thích
 * @param int $dish_id ID của món ăn
 * @return array Kết quả xử lý
 */
function add_to_wishlist($dish_id)
{
    if (!is_logged_in()) {
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào danh sách yêu thích.'];
    }
    // Logic thêm vào danh sách yêu thích
    // Ví dụ: Thêm vào $_SESSION['wishlist']
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    if (!in_array($dish_id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $dish_id;
    }
    return ['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích.'];
}
