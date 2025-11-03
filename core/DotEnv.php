<?php

/**
 * Class DotEnv - Xử lý biến môi trường từ file .env
 *
 * Class này đọc và tải các biến môi trường từ file .env vào ứng dụng.
 * Hỗ trợ đọc các cặp key=value và xử lý các trường hợp đặc biệt như comments và quotes.
 */
class DotEnv
{
    /**
     * Tải các biến môi trường từ file .env
     *
     * Phương thức này đọc file .env và tải tất cả các biến môi trường vào $_ENV và putenv().
     * Hỗ trợ comments (dòng bắt đầu bằng #) và giá trị có dấu ngoặc kép.
     *
     * @param string $path Đường dẫn đến file .env
     * @return void
     * @throws Exception Nếu file .env không tồn tại
     */
    public static function load($path)
    {
        // Kiểm tra file .env có tồn tại không
        if (!file_exists($path)) {
            throw new Exception(".env file not found at: " . $path);
        }

        // Đọc tất cả các dòng trong file, bỏ qua dòng trống
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Xử lý từng dòng
        foreach ($lines as $line) {
            // Bỏ qua các dòng comment (bắt đầu bằng #)
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Phân tích các cặp key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);

                // Loại bỏ khoảng trắng thừa
                $key = trim($key);
                $value = trim($value);

                // Loại bỏ dấu ngoặc kép nếu có
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }

                // Thiết lập biến môi trường nếu chưa được thiết lập
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Lấy giá trị của biến môi trường
     *
     * Phương thức này lấy giá trị của một biến môi trường.
     * Nếu biến không tồn tại, trả về giá trị mặc định.
     *
     * @param string $key Tên biến môi trường
     * @param mixed $default Giá trị mặc định nếu biến không tồn tại
     * @return mixed Giá trị của biến môi trường hoặc giá trị mặc định
     */
    public static function get($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Kiểm tra xem biến môi trường có tồn tại không
     *
     * @param string $key Tên biến môi trường cần kiểm tra
     * @return bool Trả về true nếu biến tồn tại, false nếu không
     */
    public static function has($key)
    {
        return array_key_exists($key, $_ENV) || getenv($key) !== false;
    }
}
