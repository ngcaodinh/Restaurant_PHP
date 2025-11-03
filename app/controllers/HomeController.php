<?php

/**
 * Controller xử lý trang chủ
 */

namespace App\Controllers;

use Core\BaseController;
use App\Models\Dish;
use Database; // Singleton database từ includes/db_connect.php
use PDO;

/**
 * Class HomeController - Controller cho trang chủ
 *
 * Controller này xử lý hiển thị trang chủ với danh sách món ăn,
 * đánh dấu món bán chạy và chuẩn bị dữ liệu cho frontend.
 */
class HomeController extends BaseController
{
    /**
     * Hiển thị trang chủ
     *
     * Phương thức này lấy danh sách món ăn từ database, xử lý dữ liệu
     * (đánh dấu best seller, format giá...) và truyền vào view để hiển thị.
     *
     * @return void
     */
    public function index(): void
    {
        // Khởi tạo các biến
        $errors = [];
        $dishes = [];
        $products_json = '[]';

        try {
            // Lấy instance PDO từ Database singleton
            /** @var PDO $pdo */
            $pdo = Database::getInstance();

            // Khởi tạo model Dish và lấy danh sách món ăn có sẵn
            $dishModel = new Dish($pdo);
            $dishes = $dishModel->getAvailableWithCategory();

            // Đánh dấu top 3 món bán chạy nhất (best sellers)
            // Danh sách đã được sắp xếp theo số lượng bán giảm dần
            $best_seller_count = min(3, count($dishes));
            foreach ($dishes as $index => &$dish) {
                $dish['is_best_seller'] = $index < $best_seller_count;
                $dish['is_top_best_seller'] = $index < $best_seller_count;
            }
            unset($dish); // Hủy reference để tránh lỗi

            // Kiểm tra có món ăn nào không
            if (empty($dishes)) {
                $errors[] = "Không tìm thấy món ăn nào với status = 'Available'.";
            }

            // Chuyển đổi dữ liệu món ăn sang định dạng cho JavaScript
            $products = array_map(function ($dish) {
                return [
                    'id' => $dish['id'],
                    'name' => $dish['name'],
                    'price' => number_format($dish['price'], 0, ',', '.') . 'đ',
                    'description' => $dish['description'],
                    'image' => $dish['image_url'],
                    'salesCount' => $dish['sales_count'],
                    'isBestSeller' => $dish['is_best_seller'],
                    'isTopBestSeller' => $dish['is_top_best_seller'],
                    'category' => $dish['category'],
                    'categoryName' => $dish['category_name'] ?? 'Không xác định',
                ];
            }, $dishes);

            // Encode thành JSON để truyền cho JavaScript
            $products_json = json_encode($products, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            // Xử lý lỗi và ghi log
            $errors[] = 'Lỗi truy vấn món ăn: ' . $e->getMessage();
            error_log('Query error: ' . $e->getMessage());
        }

        // Tạo chuỗi debug để kiểm tra dữ liệu (tương thích với phiên bản cũ)
        $debug_raw = '';
        $debug_processed = '';
        foreach ($dishes as $dish) {
            $debug_raw .= htmlspecialchars($dish['name'] . ' -> ' . ($dish['category_name'] ?? '') . ', ');
            $debug_processed .= htmlspecialchars($dish['name'] . ' -> ' . ($dish['category'] ?? '') . ', ');
        }

        // Truyền dữ liệu vào view để hiển thị
        $this->view('home/index', compact('errors', 'dishes', 'products_json', 'debug_raw', 'debug_processed'));
    }
}
