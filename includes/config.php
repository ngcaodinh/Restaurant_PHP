<?php

/**
 * Tệp cấu hình chính của ứng dụng
 *
 * Tệp này định nghĩa các hằng số cấu hình cho ứng dụng.
 * Các giá trị được lấy từ biến môi trường (.env) với giá trị mặc định dự phòng.
 */

// Tải class DotEnv để xử lý biến môi trường
require_once __DIR__ . '/../core/DotEnv.php';

// Định nghĩa các hằng số từ biến môi trường với giá trị mặc định dự phòng

// URL gốc của ứng dụng
define('BASE_URL', DotEnv::get('BASE_URL', 'http://localhost/Restaurant_PHP/'));

// Thông tin kết nối cơ sở dữ liệu
define('DB_HOST', DotEnv::get('DB_HOST', 'localhost'));      
define('DB_USER', DotEnv::get('DB_USER', 'root'));           
define('DB_PASS', DotEnv::get('DB_PASS', ''));              
define('DB_NAME', DotEnv::get('DB_NAME', 'Restaurant_CTUT')); 

// Thông tin xác thực Google OAuth
define('GOOGLE_CLIENT_ID', DotEnv::get('GOOGLE_CLIENT_ID', ''));          
define('GOOGLE_CLIENT_SECRET', DotEnv::get('GOOGLE_CLIENT_SECRET', ''));   //
define('GOOGLE_REDIRECT_URI', DotEnv::get('GOOGLE_REDIRECT_URI', 'http://localhost/Restaurant_PHP/google_callback.php')); // URL callback sau khi đăng nhập Google
