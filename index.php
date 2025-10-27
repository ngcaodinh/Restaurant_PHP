<?php
// Single front controller for MVC routing
require_once __DIR__ . '/core/bootstrap.php';

use Core\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\SearchController;

$router = new Router();

// Home routes (preserve legacy URLs)
$router->get('/', [HomeController::class, 'index']);
$router->get('/index.php', [HomeController::class, 'index']);

// Authentication routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// User routes (preserve legacy URLs)
$router->get('/user', [UserController::class, 'index']);
$router->get('/user/index.php', [UserController::class, 'index']);
$router->get('/user/profile', [UserController::class, 'profile']);
$router->post('/user/profile', [UserController::class, 'updateProfile']);
$router->post('/user/change-password', [UserController::class, 'changePassword']);
$router->get('/user/menu', [UserController::class, 'menu']);

// Cart routes
$router->get('/cart', [CartController::class, 'index']);
$router->post('/api/cart/add', [CartController::class, 'add']);
$router->post('/api/cart/remove', [CartController::class, 'remove']);
$router->post('/api/cart/update-quantity', [CartController::class, 'updateQuantity']);
$router->post('/api/cart/clear', [CartController::class, 'clear']);
$router->post('/api/cart/buy-now', [CartController::class, 'buyNow']);
$router->get('/api/cart/count', [CartController::class, 'getCount']);

// Order routes
$router->get('/orders', [OrderController::class, 'index']);
$router->get('/order', [OrderController::class, 'show']); // with ?id parameter
$router->get('/checkout', [OrderController::class, 'checkout']);
$router->post('/checkout', [OrderController::class, 'create']);
$router->get('/order-confirmation', [OrderController::class, 'confirmation']); // with ?id parameter
$router->post('/api/order/cancel', [OrderController::class, 'cancel']);
$router->post('/api/order/update-status', [OrderController::class, 'updateStatus']);

// Admin routes
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/dishes', [AdminController::class, 'dishes']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->post('/api/admin/dish/create', [AdminController::class, 'createDish']);
$router->post('/api/admin/dish/update', [AdminController::class, 'updateDish']);
$router->post('/api/admin/dish/delete', [AdminController::class, 'deleteDish']);
$router->post('/api/admin/user/update', [AdminController::class, 'updateUser']);
$router->post('/api/admin/user/delete', [AdminController::class, 'deleteUser']);

// Search routes
$router->get('/api/search', [SearchController::class, 'search']);

// Legacy compatibility routes (preserve old URLs)
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
$router->get('/admin_dashboard.php', [AdminController::class, 'dashboard']);
$router->get('/admin_users.php', [AdminController::class, 'users']);
$router->get('/dish_manage.php', [AdminController::class, 'dishes']);

// Dispatch - Router also includes legacy .php files when no route matches
$router->dispatch();
