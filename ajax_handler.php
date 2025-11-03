<?php

/**
 * Tệp xử lý các yêu cầu AJAX
 *
 * Tệp này xử lý các yêu cầu AJAX từ phía client và trả về kết quả dưới dạng JSON.
 * Hỗ trợ các chức năng như thêm vào giỏ hàng, mua ngay, thêm vào danh sách yêu thích,
 * và kiểm tra trạng thái đăng nhập.
 */

// Tải tệp bootstrap để khởi tạo các thành phần cốt lõi
require_once __DIR__ . '/core/bootstrap.php';

// Thiết lập header để trả về dữ liệu dạng JSON
header('Content-Type: application/json');

// Lấy tham số action từ POST hoặc GET request
$action = $_POST['action'] ?? $_GET['action'] ?? null;
// Lấy ID món ăn từ POST hoặc GET request
$dish_id = $_POST['dish_id'] ?? $_GET['dish_id'] ?? null;

// Khởi tạo response mặc định
$response = ['success' => false, 'message' => 'Invalid action.'];

// Xử lý các action khác nhau dựa trên tham số action
switch ($action) {
    case 'add_to_cart':
        // Thêm món ăn vào giỏ hàng
        $response = add_to_cart($dish_id);
        break;
    case 'buy_now':
        // Mua ngay món ăn (thêm vào giỏ và chuyển đến trang thanh toán)
        $response = buy_now($dish_id);
        break;
    case 'add_to_wishlist':
        // Thêm món ăn vào danh sách yêu thích
        $response = add_to_wishlist($dish_id);
        break;
    case 'check_login_status':
        // Kiểm tra trạng thái đăng nhập của người dùng
        if (is_logged_in()) {
            // Nếu đã đăng nhập, trả về thông tin người dùng
            $response = [
                'success' => true,
                'logged_in' => true,
                'user' => [
                    'name' => get_user_name(),
                    'role' => get_user_role()
                ]
            ];
        } else {
            // Nếu chưa đăng nhập
            $response = ['success' => true, 'logged_in' => false];
        }
        break;
    case 'search':
        // Chức năng tìm kiếm (có thể được triển khai sau)
        break;
}

// Trả về kết quả dưới dạng JSON
echo json_encode($response);
