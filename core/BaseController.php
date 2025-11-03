<?php

namespace Core;

/**
 * Class BaseController - Controller cơ sở cho tất cả các controller
 *
 * Class này cung cấp các phương thức chung cho tất cả các controller trong ứng dụng.
 * Các controller khác sẽ kế thừa từ class này để sử dụng các chức năng chung.
 */
class BaseController
{
    /**
     * Tải và hiển thị một view
     *
     * Phương thức này tải một tệp view từ thư mục app/views và truyền dữ liệu vào view.
     * Dữ liệu được truyền vào sẽ được extract thành các biến riêng lẻ trong view.
     *
     * @param string $view Đường dẫn đến view (không bao gồm đuôi .php)
     * @param array $data Mảng dữ liệu truyền vào view (key => value)
     * @return void
     */
    protected function view(string $view, array $data = []): void
    {
        // Extract mảng data thành các biến riêng lẻ
        // Ví dụ: ['title' => 'Home'] sẽ tạo biến $title = 'Home'
        extract($data, EXTR_OVERWRITE);

        // Tạo đường dẫn đầy đủ đến tệp view
        $viewFile = __DIR__ . '/../app/views/' . trim($view, '/') . '.php';

        // Kiểm tra xem tệp view có tồn tại không
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }

        // Include tệp view để hiển thị
        include $viewFile;
    }
}
