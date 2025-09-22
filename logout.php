<?php
// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require file config để lấy BASE_URL
require_once 'includes/config.php';

// Xóa tất cả dữ liệu session
$_SESSION = [];

// Xóa cookie session phía client
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hủy session phía server
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: " . BASE_URL . "login.php");
exit;
?>