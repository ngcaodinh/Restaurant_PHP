<?php

/**
 * Tệp Bootstrap chung cho ứng dụng MVC
 *
 * Tệp này được tải đầu tiên khi ứng dụng khởi động.
 * Nó thực hiện các công việc khởi tạo cần thiết như:
 * - Tải biến môi trường từ file .env
 * - Khởi động session
 * - Tải các tệp cấu hình và thư viện cần thiết
 */

// Tải class DotEnv để xử lý biến môi trường
require_once __DIR__ . '/DotEnv.php';

// Tải biến môi trường từ file .env
try {
    DotEnv::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    // Ghi log lỗi nhưng không dừng ứng dụng
    // Sẽ sử dụng giá trị mặc định trong config.php nếu không tải được .env
    error_log('Warning: Could not load .env file: ' . $e->getMessage());
}

// Khởi động session nếu chưa được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tải các tệp cấu hình và thư viện cần thiết
require_once __DIR__ . '/../includes/config.php';      // Cấu hình ứng dụng
require_once __DIR__ . '/../includes/db_connect.php';  // Kết nối cơ sở dữ liệu
require_once __DIR__ . '/../includes/functions.php';   // Các hàm tiện ích chung
require_once __DIR__ . '/../includes/auth.php';        // Các hàm xác thực
require_once __DIR__ . '/Autoloader.php';              // Autoloader cho các class
