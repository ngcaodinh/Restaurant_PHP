<?php
require_once __DIR__ . '/core/bootstrap.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$dish_id = $_POST['dish_id'] ?? $_GET['dish_id'] ?? null;

$response = ['success' => false, 'message' => 'Invalid action.'];

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
    case 'check_login_status':
        if (is_logged_in()) {
            $response = [
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'name' => get_user_name(),
                    'role' => get_user_role()
                ]
            ];
        } else {
            $response = ['success' => true, 'logged_in' => false];
        }
        break;
    case 'search':
        // This case can be implemented if you have a search function
        break;
}

echo json_encode($response);
?>
