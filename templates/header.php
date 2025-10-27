<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Đếm số lượng giỏ hàng và danh sách yêu thích
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlist_count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;

// Lấy thông tin người dùng
$user_name = is_logged_in() ? ($_SESSION['user_name'] ?? 'Khách') : '';

// Đếm giỏ hàng từ database nếu đã đăng nhập
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ? AND ci.deleted_at IS NULL");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch (Exception $e) {
        error_log('Error fetching cart count: ' . $e->getMessage());
    }
}
?>

<header class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <div class="header-container w-100">
            <div class="top-nav d-flex align-items-center justify-content-between w-100">
                <!-- Logo -->
                <a href="/Restaurant_PHP/index.php" class="navbar-brand logo d-flex align-items-center">
                    <span class="logo-icon me-2">🍜</span>
                    <span class="logo-text">CTUT Restaurant</span>
                </a>

                <!-- Desktop Navigation & Search -->
                <!-- Navigation, Search, Icons, and Toggler -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a class="nav-link" href="/Restaurant_PHP/index.php">Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link" href="#dishes">Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">Giới thiệu</a></li>
                        <?php if (is_admin()): ?>
                            <li class="nav-item"><a class="nav-link" href="/Restaurant_PHP/admin_dashboard.php">Quản lý người dùng</a></li>
                        <?php endif; ?>
                        <?php if (is_premium_or_admin()): ?>
                            <li class="nav-item"><a class="nav-link" href="/Restaurant_PHP/dish_manage.php">Quản lý món ăn</a></li>
                        <?php endif; ?>
                    </ul>
                    <div class="d-flex align-items-center">
                        <div class="search-bar me-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control border-start-0" placeholder="Tìm kiếm..." id="searchInput" onkeypress="if(event.key === 'Enter') searchDishes();" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Icons (visible on all pages) -->
                <div class="nav-icons d-flex align-items-center">
                    <div class="icon-wrapper">
                        <button type="button" class="btn nav-icon position-relative p-2" onclick="toggleCart()">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-count" id="cart-count"><?php echo htmlspecialchars($cart_count); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div class="icon-wrapper">
                        <button type="button" class="btn nav-icon position-relative p-2" onclick="toggleWishlist()">
                            <i class="fas fa-heart"></i>
                            <?php if ($wishlist_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-count" id="wishlist-count"><?php echo htmlspecialchars($wishlist_count); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div class="icon-wrapper user-icon dropdown">
                        <button type="button" class="btn nav-icon dropdown-toggle p-2" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
                            <i class="fas fa-user user-icon-fix"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="userDropdown">
                            <?php if (is_logged_in()): ?>
                                <a class="dropdown-item" href="/Restaurant_PHP/my_orders.php"><i class="fas fa-user-circle me-2"></i>Hồ sơ (<?php echo htmlspecialchars($user_name); ?>)</a>
                                <?php if (is_admin()): ?>
                                    <a class="dropdown-item" href="/Restaurant_PHP/admin_dashboard.php"><i class="fas fa-cog me-2"></i>Bảng điều khiển</a>
                                    <a class="dropdown-item" href="/Restaurant_PHP/admin_users.php"><i class="fas fa-users me-2"></i>Quản lý người dùng</a>
                                <?php endif; ?>
                                <?php if (is_premium_or_admin()): ?>
                                    <a class="dropdown-item" href="/Restaurant_PHP/dish_manage.php"><i class="fas fa-utensils me-2"></i>Quản lý món ăn</a>
                                <?php endif; ?>
                                <a class="dropdown-item" href="<?php echo BASE_URL . 'cart.php'; ?>"><i class="fas fa-shopping-cart me-2"></i>Giỏ hàng</a>
                                <a class="dropdown-item" href="/Restaurant_PHP/checkout.php"><i class="fas fa-credit-card me-2"></i>Thanh toán</a>
                                <a class="dropdown-item" href="/Restaurant_PHP/orders.php"><i class="fas fa-list-alt me-2"></i>Đơn hàng</a>
                                <hr class="dropdown-divider">
                                <a class="dropdown-item" href="/Restaurant_PHP/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a>
                            <?php else: ?>
                                <a class="dropdown-item" href="/Restaurant_PHP/login.php"><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</a>
                                <a class="dropdown-item" href="/Restaurant_PHP/register.php"><i class="fas fa-user-plus me-2"></i>Đăng ký</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Mobile Toggle Button -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </div>
</header>