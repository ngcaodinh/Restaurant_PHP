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
            /** @var PDO $pdo */
            $pdo = Database::getInstance();
            $dishModel = new Dish($pdo);

            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
            $limit = 6; // Số món ăn mỗi trang
            $numBestSellers = 3;

            // Lấy top best sellers và ID của chúng
            $bestSellers = $dishModel->getTopBestSellers($numBestSellers);
            $bestSellerIds = array_map(fn($d) => $d['id'], $bestSellers);

            // Tính toán phân trang cho các món còn lại
            $totalOtherDishes = $dishModel->getTotalAvailableDishesExcluding($bestSellerIds);
            $totalPages = 1 + (int)ceil($totalOtherDishes / $limit);

            if ($page == 1) {
                // Trang 1: 3 best sellers + (limit - 3) món khác
                $otherDishesLimit = $limit - $numBestSellers;
                $otherDishes = $dishModel->getAvailableExcluding($otherDishesLimit, 0, $bestSellerIds);
                $dishes = array_merge($bestSellers, $otherDishes);
            } else {
                // Các trang khác: chỉ các món khác
                $offset = ($page - 2) * $limit + ($limit - $numBestSellers);
                $dishes = $dishModel->getAvailableExcluding($limit, $offset, $bestSellerIds);
            }

            // Đánh dấu is_top_best_seller cho JSON
            foreach ($dishes as &$dish) {
                $dish['is_top_best_seller'] = in_array($dish['id'], $bestSellerIds);
            }
            unset($dish);

            if (empty($dishes)) {
                $errors[] = "Không tìm thấy món ăn nào.";
            }

            // Chuyển đổi dữ liệu cho JavaScript
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

        // Dữ liệu debug (nếu cần)
        $debug_raw = '';
        $debug_processed = '';

        // Truyền dữ liệu vào view
        $this->view('home/index', compact('errors', 'dishes', 'products_json', 'debug_raw', 'debug_processed', 'page', 'totalPages'));
    }

    /**
     * API endpoint để lấy danh sách món ăn qua AJAX
     *
     * @return void
     */
    public function getDishes(): void
    {
        header('Content-Type: application/json');

        try {
            /** @var PDO $pdo */
            $pdo = Database::getInstance();
            $dishModel = new Dish($pdo);

            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
            $limit = 6; // Số món ăn mỗi trang
            $numBestSellers = 3;

            // Lấy top best sellers và ID của chúng
            $bestSellers = $dishModel->getTopBestSellers($numBestSellers);
            $bestSellerIds = array_map(fn($d) => $d['id'], $bestSellers);

            // Tính toán phân trang cho các món còn lại
            $totalOtherDishes = $dishModel->getTotalAvailableDishesExcluding($bestSellerIds);
            $totalPages = 1 + (int)ceil($totalOtherDishes / $limit);

            if ($page == 1) {
                // Trang 1: 3 best sellers + (limit - 3) món khác
                $otherDishesLimit = $limit - $numBestSellers;
                $otherDishes = $dishModel->getAvailableExcluding($otherDishesLimit, 0, $bestSellerIds);
                $dishes = array_merge($bestSellers, $otherDishes);
            } else {
                // Các trang khác: chỉ các món khác
                $offset = ($page - 2) * $limit + ($limit - $numBestSellers);
                $dishes = $dishModel->getAvailableExcluding($limit, $offset, $bestSellerIds);
            }

            // Đánh dấu is_top_best_seller cho JSON
            foreach ($dishes as &$dish) {
                $dish['is_top_best_seller'] = in_array($dish['id'], $bestSellerIds);
            }
            unset($dish);

            // Chuyển đổi dữ liệu cho JavaScript
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

            // Tạo HTML cho phân trang
            $paginationHtml = $this->generatePaginationHtml($page, $totalPages);

            // Trả về JSON
            echo json_encode([
                'success' => true,
                'dishes' => $products,
                'pagination' => $paginationHtml,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            // Xử lý lỗi
            error_log('API error (getDishes): ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải dữ liệu.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Tạo HTML cho phân trang
     *
     * @param int $page Trang hiện tại
     * @param int $totalPages Tổng số trang
     * @return string HTML của phân trang
     */
    private function generatePaginationHtml(int $page, int $totalPages): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Page navigation">';
        $html .= '<ul class="pagination custom-pagination justify-content-center mt-5">';

        // Previous Button
        $disabledPrev = $page <= 1 ? 'disabled' : '';
        $html .= '<li class="page-item ' . $disabledPrev . '">';
        $html .= '<a class="page-link" href="javascript:void(0);" onclick="loadPage(' . ($page - 1) . ')" aria-label="Previous">';
        $html .= '<i class="fas fa-arrow-left"></i>';
        $html .= '</a>';
        $html .= '</li>';

        // Page Numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i == $page ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '">';
            $html .= '<a class="page-link" href="javascript:void(0);" onclick="loadPage(' . $i . ')">' . $i . '</a>';
            $html .= '</li>';
        }

        // Next Button
        $disabledNext = $page >= $totalPages ? 'disabled' : '';
        $html .= '<li class="page-item ' . $disabledNext . '">';
        $html .= '<a class="page-link" href="javascript:void(0);" onclick="loadPage(' . ($page + 1) . ')" aria-label="Next">';
        $html .= '<i class="fas fa-arrow-right"></i>';
        $html .= '</a>';
        $html .= '</li>';

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
}
