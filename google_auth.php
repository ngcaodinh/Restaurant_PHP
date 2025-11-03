<?php

/**
 * Tệp xử lý chuyển hướng đến trang xác thực Google OAuth 2.0
 *
 * Tệp này xây dựng URL xác thực của Google với các tham số cần thiết
 * và chuyển hướng người dùng đến đó để bắt đầu quá trình đăng nhập bằng Google.
 */

session_start();

// Tải các tệp cấu hình và hàm cần thiết
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cấu hình Google OAuth 2.0 (Nên được lưu trong file .env)
$client_id = '490954709937-j4gohbodbtb7kg2215oe63fbe21cn8oi.apps.googleusercontent.com';
$redirect_uri = 'http://localhost/Restaurant_PHP/callback.php'; // URL sẽ nhận callback từ Google
$auth_uri = 'https://accounts.google.com/o/oauth2/auth'; // URL xác thực của Google

// Phạm vi (scope) quyền truy cập cần yêu cầu từ người dùng
$scope = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';

// Xây dựng URL xác thực đầy đủ
$auth_url = $auth_uri . '?' . http_build_query([
    'client_id' => $client_id, // ID của ứng dụng
    'redirect_uri' => $redirect_uri, // URL callback
    'response_type' => 'code', // Yêu cầu mã ủy quyền
    'scope' => $scope, // Phạm vi quyền truy cập
    'access_type' => 'online', // Loại truy cập
    'prompt' => 'consent' // Luôn hỏi người dùng sự đồng ý
]);

// Chuyển hướng người dùng đến URL xác thực của Google
header('Location: ' . $auth_url);
exit;
