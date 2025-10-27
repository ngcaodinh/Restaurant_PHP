<?php
namespace App\Controllers;

use Core\BaseController;
use App\Models\Cart;
use App\Models\Dish;
use Database;

class CartController extends BaseController
{
    private Cart $cartModel;
    private Dish $dishModel;

    public function __construct()
    {
        $this->cartModel = new Cart(Database::getInstance());
        $this->dishModel = new Dish(Database::getInstance());
    }

    public function index(): void
    {
        $this->checkAuth(['Admin', 'User', 'PremiumUser']);
        
        $userId = $_SESSION['user_id'];
        $cartItems = $this->cartModel->getCartItems($userId);
        $cartTotal = $this->cartModel->getCartTotal($userId);
        $cartCount = $this->cartModel->getCartItemCount($userId);

        $this->view('cart/index', compact('cartItems', 'cartTotal', 'cartCount'));
    }

    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
            return;
        }

        $dishId = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);

        if (!$dishId) {
            $this->jsonResponse(['success' => false, 'message' => 'Món ăn không hợp lệ']);
            return;
        }

        // Check if dish exists
        $dish = $this->dishModel->getDishById($dishId);
        if (!$dish) {
            $this->jsonResponse(['success' => false, 'message' => 'Món ăn không tồn tại']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $cartId = $this->cartModel->getOrCreateCart($userId);
        
        if ($this->cartModel->addItem($cartId, $dishId, $quantity)) {
            // Update session cart
            $_SESSION['cart'] = $this->cartModel->getCartItems($userId);
            $this->jsonResponse(['success' => true, 'message' => 'Đã thêm món vào giỏ hàng']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi thêm món vào giỏ hàng']);
        }
    }

    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
            return;
        }

        $dishId = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);

        if (!$dishId) {
            $this->jsonResponse(['success' => false, 'message' => 'Món ăn không hợp lệ']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $cartId = $this->cartModel->getOrCreateCart($userId);
        
        if ($this->cartModel->removeItem($cartId, $dishId)) {
            // Update session cart
            $_SESSION['cart'] = $this->cartModel->getCartItems($userId);
            $this->jsonResponse(['success' => true, 'message' => 'Đã xóa món khỏi giỏ hàng']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi xóa món khỏi giỏ hàng']);
        }
    }

    public function updateQuantity(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
            return;
        }

        $dishId = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if (!$dishId || $quantity === false) {
            $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $cartId = $this->cartModel->getOrCreateCart($userId);
        
        if ($this->cartModel->updateItemQuantity($cartId, $dishId, $quantity)) {
            // Update session cart
            $_SESSION['cart'] = $this->cartModel->getCartItems($userId);
            $this->jsonResponse(['success' => true, 'message' => 'Đã cập nhật số lượng']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi cập nhật số lượng']);
        }
    }

    public function clear(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        if ($this->cartModel->clearCart($userId)) {
            // Update session cart
            $_SESSION['cart'] = [];
            $this->jsonResponse(['success' => true, 'message' => 'Đã xóa tất cả món trong giỏ hàng']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi xóa giỏ hàng']);
        }
    }

    public function buyNow(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
            return;
        }

        $dishId = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);

        if (!$dishId) {
            $this->jsonResponse(['success' => false, 'message' => 'Món ăn không hợp lệ']);
            return;
        }

        // Check if dish exists
        $dish = $this->dishModel->getDishById($dishId);
        if (!$dish) {
            $this->jsonResponse(['success' => false, 'message' => 'Món ăn không tồn tại']);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        if ($this->cartModel->replaceCartWithSingleItem($userId, $dishId)) {
            // Update session cart
            $_SESSION['cart'] = $this->cartModel->getCartItems($userId);
            $this->jsonResponse(['success' => true, 'message' => 'Đã thêm món để thanh toán', 'redirect' => '/checkout']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi thêm món vào giỏ hàng']);
        }
    }

    public function getCount(): void
    {
        if (!$this->isLoggedIn()) {
            $this->jsonResponse(['count' => 0]);
            return;
        }

        $userId = $_SESSION['user_id'];
        $count = $this->cartModel->getCartItemCount($userId);
        $this->jsonResponse(['count' => $count]);
    }

    private function checkAuth(array $allowedRoles): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit();
        }

        $userRole = $_SESSION['user_role'] ?? null;
        if (!in_array($userRole, $allowedRoles)) {
            $_SESSION['error_message'] = 'Bạn không có quyền truy cập.';
            header('Location: /');
            exit();
        }
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
