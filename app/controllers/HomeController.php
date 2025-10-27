<?php
namespace App\Controllers;

use Core\BaseController;
use App\Models\Dish;
use Database; // legacy DB singleton from includes/db_connect.php
use PDO;

class HomeController extends BaseController
{
    public function index(): void
    {
        $errors = [];
        $dishes = [];
        $products_json = '[]';

        try {
            /** @var PDO $pdo */
            $pdo = Database::getInstance();
            $dishModel = new Dish($pdo);
            $dishes = $dishModel->getAvailableWithCategory();

            // Mark best sellers (top 3 by order already sorted DESC)
            $best_seller_count = min(3, count($dishes));
            foreach ($dishes as $index => &$dish) {
                $dish['is_best_seller'] = $index < $best_seller_count;
                $dish['is_top_best_seller'] = $index < $best_seller_count;
            }
            unset($dish);

            if (empty($dishes)) {
                $errors[] = "Không tìm thấy món ăn nào với status = 'Available'.";
            }

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

            $products_json = json_encode($products, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            $errors[] = 'Lỗi truy vấn món ăn: ' . $e->getMessage();
            error_log('Query error: ' . $e->getMessage());
        }

        // Build debug strings identical to legacy
        $debug_raw = '';
        $debug_processed = '';
        foreach ($dishes as $dish) {
            $debug_raw .= htmlspecialchars($dish['name'] . ' -> ' . ($dish['category_name'] ?? '' ) . ', ');
            $debug_processed .= htmlspecialchars($dish['name'] . ' -> ' . ($dish['category'] ?? '' ) . ', ');
        }

        $this->view('home/index', compact('errors', 'dishes', 'products_json', 'debug_raw', 'debug_processed'));
    }
}

