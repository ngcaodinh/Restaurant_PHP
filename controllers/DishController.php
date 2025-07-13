<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Dish.php';

class DishController {
    private $db;
    private $dishModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->dishModel = new Dish();
    }

    public function getAllDishes() {
        // Giả lập dữ liệu món ăn từ database
        // Trong thực tế, bạn sẽ truy vấn từ bảng dishes
        return [
            [
                'id' => 1,
                'name' => 'Cơm Tấm Sườn Nướng',
                'price' => '35.000đ',
                'description' => 'Cơm tấm thơm ngon với sườn nướng đậm đà, kèm trứng ốp la và chả cá.',
                'image' => '/assets/images/dishes/com-tam.jpg',
                'sales_count' => 156,
                'is_best_seller' => true,
                'category' => 'bestseller'
            ],
            // Thêm các món ăn khác...
        ];
    }

    public function getDish($dishId) {
        // Lấy thông tin món ăn theo ID
        $dishes = $this->getAllDishes();
        foreach ($dishes as $dish) {
            if ($dish['id'] == $dishId) {
                header('Content-Type: application/json');
                echo json_encode($dish);
                return;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Dish not found']);
    }

    public function addToWishlist($dishId) {
        // Logic thêm vào wishlist
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'dish' => $this->getAllDishes()[0]]);
    }
}
?>