<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$dish_id = $_POST['dish_id'] ?? 0;

error_log('AJAX request: action=' . $action . ', dish_id=' . $dish_id);

$response = ['success' => false, 'message' => 'Hành động không hợp lệ.'];

switch ($action) {
    case 'add_to_cart':
        $response = add_to_cart($dish_id);
        break;
    case 'buy_now':
        $response = buy_now($dish_id);
        break;
    case 'add_to_wishlist':
        $response = add_to_wishlist($dish_id);
        break;
    case 'get_cart_count':
        if (!is_logged_in()) {
            $response = ['success' => false, 'cart_count' => 0];
        } else {
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ? ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'cart_count' => $result['count']];
        }
        break;
    default:
        $response = ['success' => false, 'message' => 'Hành động không được hỗ trợ.'];
}

echo json_encode($response);
exit;
?>