<?php

/**
 * Tệp điều khiển chính (Front Controller) cho ứng dụng MVC
 *
 * Tệp này là điểm vào duy nhất cho tất cả các yêu cầu HTTP đến ứng dụng.
 * Nó khởi tạo router và định nghĩa tất cả các routes (đường dẫn) cho ứng dụng.
 * Sử dụng mô hình MVC (Model-View-Controller) để tổ chức mã nguồn.
 */

// Tải tệp bootstrap để khởi tạo các thành phần cốt lõi của ứng dụng
require_once __DIR__ . '/core/bootstrap.php';

// Import các class cần thiết từ namespace
use Core\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\SearchController;
use App\Controllers\FavoritesController;
use App\Controllers\PurchaseHistory;

// Khởi tạo đối tượng Router để xử lý định tuyến
$router = new Router();

// ===== ĐỊNH TUYẾN TRANG CHỦ =====
// Giữ lại các URL cũ để tương thích ngược
$router->get('/', [HomeController::class, 'index']);
$router->get('/index.php', [HomeController::class, 'index']);

// ===== ĐỊNH TUYẾN XÁC THỰC (AUTHENTICATION) =====
// Route hiển thị trang đăng nhập
$router->get('/login', [AuthController::class, 'showLogin']);
// Route xử lý đăng nhập
$router->post('/login', [AuthController::class, 'login']);
// Route hiển thị trang đăng ký
$router->get('/register', [AuthController::class, 'showRegister']);
// Route xử lý đăng ký
$router->post('/register', [AuthController::class, 'register']);
// Route đăng xuất (hỗ trợ cả GET và POST)
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// ===== ĐỊNH TUYẾN ĐĂNG NHẬP QUẢN TRỊ VIÊN =====
// Route hiển thị trang đăng nhập admin
$router->get('/admin/login', [AuthController::class, 'showAdminLogin']);
// Route xử lý đăng nhập admin
$router->post('/admin/login', [AuthController::class, 'adminLogin']);

// ===== ĐỊNH TUYẾN NGƯỜI DÙNG =====
// Giữ lại các URL cũ để tương thích ngược
// Route trang chính của người dùng
$router->get('/user', [UserController::class, 'index']);
$router->get('/user/index.php', [UserController::class, 'index']);
// Route hiển thị trang hồ sơ người dùng
$router->get('/user/profile', [UserController::class, 'profile']);
// Route cập nhật thông tin hồ sơ
$router->post('/user/profile', [UserController::class, 'updateProfile']);
// Route thay đổi mật khẩu
$router->post('/user/change-password', [UserController::class, 'changePassword']);
// Route hiển thị menu
$router->get('/user/menu', [UserController::class, 'menu']);

// ===== ĐỊNH TUYẾN GIỎ HÀNG =====
// Route hiển thị trang giỏ hàng
$router->get('/cart', [CartController::class, 'index']);
// Route thêm sản phẩm vào giỏ hàng
$router->post('/api/cart/add', [CartController::class, 'add']);
// Route xóa sản phẩm khỏi giỏ hàng
$router->post('/api/cart/remove', [CartController::class, 'remove']);
// Route cập nhật số lượng sản phẩm trong giỏ hàng
$router->post('/api/cart/update-quantity', [CartController::class, 'updateQuantity']);
// Route xóa toàn bộ giỏ hàng
$router->post('/api/cart/clear', [CartController::class, 'clear']);
// Route mua ngay (thêm vào giỏ và chuyển đến thanh toán)
$router->post('/api/cart/buy-now', [CartController::class, 'buyNow']);
// Route lấy số lượng sản phẩm trong giỏ hàng
$router->get('/api/cart/count', [CartController::class, 'getCount']);
// Route xử lý các sản phẩm được chọn trong giỏ hàng
$router->post('/cart/process-selection', [CartController::class, 'processSelection']);

// ===== ĐỊNH TUYẾN YÊU THÍCH =====
// Route hiển thị trang danh sách món ăn yêu thích
$router->get('/favorites', [FavoritesController::class, 'index']);

// ===== ĐỊNH TUYẾN ĐỚN HÀNG =====
// Route hiển thị danh sách đơn hàng
$router->get('/orders', [OrderController::class, 'index']);
// Route hiển thị chi tiết đơn hàng (với tham số ?id)
$router->get('/order', [OrderController::class, 'show']);
// Route hiển thị trang thanh toán
$router->get('/checkout', [OrderController::class, 'checkout']);
// Route xử lý tạo đơn hàng mới
$router->post('/checkout', [OrderController::class, 'create']);
// Route hiển thị trang xác nhận đơn hàng (với tham số ?id)
$router->get('/order-confirmation', [OrderController::class, 'confirmation']);
// Route hủy đơn hàng
$router->post('/api/order/cancel', [OrderController::class, 'cancel']);
// Route cập nhật trạng thái đơn hàng
$router->post('/api/order/update-status', [OrderController::class, 'updateStatus']);

// ===== ĐỊNH TUYẾN LỊCH SỬ MUA HÀNG =====
// Route hiển thị lịch sử mua hàng của người dùng
$router->get('/purchase-history', [PurchaseHistory::class, 'index']);


// ===== ĐỊNH TUYẾN QUẢN TRỊ VIÊN =====
// Route trang chủ admin (dashboard)
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
// Route quản lý người dùng
$router->get('/admin/users', [AdminController::class, 'users']);
// Route quản lý món ăn
$router->get('/admin/dishes', [AdminController::class, 'dishes']);
$router->get('/admin/manage_dishes', [AdminController::class, 'dishes']); // Bí danh để rõ nghĩa
// Route quản lý đơn hàng
$router->get('/admin/orders', [AdminController::class, 'orders']);
// Route lấy chi tiết đơn hàng
$router->get('/admin/get-order-details', [AdminController::class, 'getOrderDetails']);
// Route tạo món ăn mới
$router->post('/api/admin/dish/create', [AdminController::class, 'createDish']);
// Route cập nhật thông tin món ăn
$router->post('/api/admin/dish/update', [AdminController::class, 'updateDish']);
// Route xóa món ăn
$router->post('/api/admin/dish/delete', [AdminController::class, 'deleteDish']);
// Route cập nhật thông tin người dùng
$router->post('/api/admin/user/update', [AdminController::class, 'updateUser']);
// Route xóa người dùng
$router->post('/api/admin/user/delete', [AdminController::class, 'deleteUser']);

// ===== ĐỊNH TUYẾN TÌM KIẾM =====
// Route tìm kiếm món ăn
$router->get('/api/search', [SearchController::class, 'search']);

// ===== ĐỊNH TUYẾN TƯƠNG THÍCH NGƯỢC (Legacy URLs) =====
// Giữ lại các URL cũ với đuôi .php để tương thích với phiên bản cũ
$router->get('/login.php', [AuthController::class, 'showLogin']);
$router->post('/login.php', [AuthController::class, 'login']);
$router->get('/register.php', [AuthController::class, 'showRegister']);
$router->post('/register.php', [AuthController::class, 'register']);
$router->get('/logout.php', [AuthController::class, 'logout']);
$router->get('/cart.php', [CartController::class, 'index']);
$router->get('/checkout.php', [OrderController::class, 'checkout']);
$router->post('/checkout.php', [OrderController::class, 'create']);
$router->get('/orders.php', [OrderController::class, 'index']);
$router->get('/order_confirmation.php', [OrderController::class, 'confirmation']);
$router->get('/favorites.php', [FavoritesController::class, 'index']);
$router->get('/admin_dashboard.php', [AdminController::class, 'dashboard']);
$router->get('/admin_users.php', [AdminController::class, 'users']);

// ===== ĐIỀU PHỐI YÊU CẦU =====

// Route cho chức năng "Mua ngay"
$router->post('/order/buyNow', [App\Controllers\OrderController::class, 'buyNow']);

$router->post('/order/create', [App\Controllers\OrderController::class, 'create']);

// Gọi phương thức dispatch để xử lý yêu cầu HTTP
// Router cũng tự động tìm và include các tệp .php cũ nếu không tìm thấy route phù hợp
$router->dispatch();
