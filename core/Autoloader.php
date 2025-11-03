<?php

/**
 * Autoloader cho ứng dụng
 *
 * Tệp này đăng ký một hàm autoload để tự động tải các class khi chúng được sử dụng.
 * Sử dụng PSR-4 autoloading standard để ánh xạ namespace đến thư mục tương ứng.
 */

/**
 * Đăng ký hàm autoload
 *
 * Hàm này sẽ được gọi tự động khi một class chưa được định nghĩa được sử dụng.
 * Nó sẽ tìm và tải tệp chứa class đó dựa trên namespace.
 *
 * @param string $class Tên đầy đủ của class (bao gồm namespace)
 * @return bool Trả về true nếu tải thành công, false nếu không tìm thấy
 */
spl_autoload_register(function ($class) {
    // Định nghĩa ánh xạ giữa namespace prefix và thư mục tương ứng
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/../app/controllers/',  // Controllers
        'App\\Models\\'      => __DIR__ . '/../app/models/',       // Models
        'Core\\'              => __DIR__ . '/'                      // Core classes
    ];

    // Duyệt qua từng prefix để tìm class phù hợp
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        // Kiểm tra xem class có bắt đầu với prefix này không
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        // Lấy phần tên class sau prefix
        $relative_class = substr($class, $len);
        // Tạo đường dẫn đến tệp class
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        // Nếu tệp tồn tại, tải nó
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});
