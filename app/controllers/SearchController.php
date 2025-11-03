<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Dish;
use Database;

/**
 * Class SearchController - Controller xử lý tìm kiếm
 *
 * Xử lý các yêu cầu tìm kiếm món ăn theo từ khóa hoặc danh mục.
 */
class SearchController extends BaseController
{
    /** @var Dish Model Dish để thao tác với dữ liệu món ăn */
    private Dish $dishModel;

    /**
     * Constructor khởi tạo DishModel
     */
    public function __construct()
    {
        $this->dishModel = new Dish(Database::getInstance());
    }

    /**
     * Xử lý yêu cầu tìm kiếm (AJAX)
     *
     * Tìm kiếm món ăn dựa trên query `q` hoặc `category` và trả về kết quả dạng JSON.
     * @return void
     */
    public function search(): void
    {
        $query = trim($_GET['q'] ?? '');
        $category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

        // Yêu cầu phải có ít nhất một tham số tìm kiếm
        if (empty($query) && !$category) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập từ khóa tìm kiếm']);
            return;
        }

        try {
            // Lấy danh sách món ăn từ model
            if ($category) {
                $dishes = $this->dishModel->getDishesByCategory($category);
            } else {
                $dishes = $this->dishModel->searchDishes($query);
            }

            // Định dạng lại dữ liệu món ăn cho frontend
            $formattedDishes = array_map(function ($dish) {
                $categoryName = $dish['category_name'] ?? 'unknown';
                $categoryMap = [
                    'món chính' => 'mn-chnh',
                    'tráng miệng' => 'trng-ming',
                    'đồ uống' => '-ung',
                ];
                $dish['category'] = $categoryMap[$categoryName] ?? strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/u', '', $categoryName)));
                $dish['image_url'] = !empty($dish['image']) ? $dish['image'] : 'https://via.placeholder.com/300x250?text=No+Image';
                $dish['is_best_seller'] = $dish['sales_count'] > 100;

                return [
                    'id' => $dish['id'],
                    'name' => $dish['name'],
                    'price' => number_format($dish['price'], 0, ',', '.') . 'đ',
                    'description' => $dish['description'],
                    'image' => $dish['image_url'],
                    'salesCount' => $dish['sales_count'],
                    'isBestSeller' => $dish['is_best_seller'],
                    'category' => $dish['category'],
                    'categoryName' => $dish['category_name'] ?? 'Không xác định',
                ];
            }, $dishes);

            // Trả về kết quả thành công
            $this->jsonResponse([
                'success' => true,
                'data' => $formattedDishes,
                'count' => count($formattedDishes)
            ]);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            error_log("Search error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra khi tìm kiếm']);
        }
    }

    /**
     * Trả về phản hồi dạng JSON
     *
     * @param array $data Dữ liệu cần trả về
     * @return void
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
        exit();
    }
}
