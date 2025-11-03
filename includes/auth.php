<?php

/**
 * Tệp xử lý xác thực và phân quyền người dùng
 *
 * Tệp này chứa các hàm liên quan đến xác thực người dùng, kiểm tra quyền truy cập,
 * và các chức năng liên quan đến giỏ hàng và danh sách yêu thích.
 */

// Khởi động session nếu chưa được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra xem người dùng có đăng nhập hay không
 *
 * @return bool Trả về true nếu đã đăng nhập, false nếu chưa
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Lấy vai trò của người dùng hiện tại
 *
 * @return string|null Vai trò của người dùng (Admin, PremiumUser, User) hoặc null nếu chưa đăng nhập
 */
function get_user_role()
{
    return is_logged_in() ? ($_SESSION['user_role'] ?? null) : null;
}

/**
 * Kiểm tra xem người dùng có phải là Admin không
 *
 * @return bool Trả về true nếu là Admin, false nếu không
 */
function is_admin()
{
    return get_user_role() === 'Admin';
}

/**
 * Kiểm tra xem người dùng có phải là PremiumUser không
 *
 * @return bool Trả về true nếu là PremiumUser, false nếu không
 */
function is_premium()
{
    return get_user_role() === 'PremiumUser';
}

/**
 * Kiểm tra xem người dùng có phải là Admin hoặc PremiumUser không
 *
 * @return bool Trả về true nếu là Admin hoặc PremiumUser, false nếu không
 */
function is_premium_or_admin()
{
    return in_array(get_user_role(), ['Admin', 'PremiumUser']);
}

/**
 * Kiểm tra quyền truy cập dựa trên vai trò
 *
 * Hàm này kiểm tra xem người dùng có quyền truy cập vào trang hiện tại không.
 * Nếu chưa đăng nhập hoặc không có quyền, sẽ chuyển hướng đến trang phù hợp.
 *
 * @param array $allowed_roles Mảng các vai trò được phép truy cập
 * @return void Chuyển hướng nếu không có quyền, không trả về giá trị
 */
function check_permission(array $allowed_roles)
{
    // Kiểm tra đã đăng nhập chưa
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Vui lòng đăng nhập để truy cập.';
        header('Location: login.php');
        exit;
    }

    // Kiểm tra vai trò có trong danh sách cho phép không
    $user_role = get_user_role();
    if (!in_array($user_role, $allowed_roles)) {
        $_SESSION['error_message'] = 'Bạn không có quyền truy cập.';
        header('Location: index.php');
        exit;
    }
}

// Đảm bảo session được khởi động (kiểm tra lại)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * Thêm món ăn vào giỏ hàng
 *
 * Hàm này thêm một món ăn vào giỏ hàng của người dùng.
 * Nếu món đã có trong giỏ, tăng số lượng lên 1.
 * Nếu chưa có giỏ hàng, tạo giỏ hàng mới.
 *
 * @param int $dish_id ID của món ăn cần thêm
 * @return array Mảng kết quả với keys: success (bool), message (string)
 */
function add_to_cart($dish_id)
{
    global $pdo;

    // Kiểm tra đã đăng nhập chưa
    if (!is_logged_in()) {
        error_log('add_to_cart: User not logged in');
        return ['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục'];
    }

    $user_id = $_SESSION['user_id'];
    error_log("add_to_cart: user_id=$user_id, dish_id=$dish_id");

    // Validate dish_id
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

        // Kiểm tra món ăn có tồn tại trong database không
        $stmt = $pdo->prepare("SELECT id FROM dishes WHERE id = ? ");
        $stmt->execute([$dish_id]);
        if (!$stmt->fetch()) {
            error_log("add_to_cart: Dish not found, dish_id=$dish_id");
            return ['success' => false, 'message' => 'Món ăn không tồn tại'];
        }

        // Tìm hoặc tạo giỏ hàng cho người dùng
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            // Tạo giỏ hàng mới nếu chưa có
            $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
            error_log("add_to_cart: Created new cart, cart_id=$cart_id");
        } else {
            // Sử dụng giỏ hàng hiện có
            $cart_id = $cart['id'];
            error_log("add_to_cart: Found existing cart, cart_id=$cart_id");
        }

        // Kiểm tra món đã có trong giỏ chưa
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND dish_id = ?");
        $stmt->execute([$cart_id, $dish_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            // Nếu đã có, tăng số lượng lên 1
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$item['id']]);
            error_log("add_to_cart: Updated quantity for cart_item_id={$item['id']}");
        } else {
            // Nếu chưa có, thêm món mới với số lượng = 1
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$cart_id, $dish_id]);
            error_log("add_to_cart: Added new cart item, dish_id=$dish_id");
        }

        // Cập nhật session cart với dữ liệu mới nhất
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
function buy_now($dish_id)
{
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
    $dish_id = filter_var($dish_id, FILTER_VALIDATE_INT);
    if (!$dish_id) {
        return ['success' => false, 'message' => 'ID món ăn không hợp lệ.'];
    }

    $user_id = get_user_id();

    try {
        global $pdo;

        // Check if the item is already in favorites
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND dish_id = ?");
        $stmt->execute([$user_id, $dish_id]);

        if ($stmt->fetch()) {
            // Item exists, so remove it
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND dish_id = ?");
            $stmt->execute([$user_id, $dish_id]);
            return ['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích.', 'action' => 'removed'];
        } else {
            // Item does not exist, so add it
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, dish_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $dish_id]);
            return ['success' => true, 'message' => 'Đã thêm vào danh sách yêu thích.', 'action' => 'added'];
        }
    } catch (Exception $e) {
        error_log('Function error (add_to_wishlist): ' . $e->getMessage());
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }
}
