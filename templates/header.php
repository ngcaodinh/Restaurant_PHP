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
?>

<header>
    <div class="header-container">
        <div class="top-nav">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>" class="logo">
                    <span class="logo-icon">🍜</span>
                    <span class="logo-text">CTUT Restaurant</span>
                </a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Tìm kiếm món ăn" id="searchInput" onkeypress="if(event.key === 'Enter') searchDishes();" />
                <i class="fas fa-search search-icon" onclick="searchDishes();"></i>
            </div>
            <nav class="menu-nav">
                <ul>
                    <li><a href="/Restaurant_PHP/index.php">Trang chủ</a></li>
                    <li><a href="#dishes">Menu</a></li>
                    <li><a href="#contact">Giới thiệu</a></li>

                    <?php if (is_admin()): ?>
                        <li><a href="/Restaurant_PHP/admin_dashboard.php">Quản lý người dùng</a></li>
                    <?php endif; ?>
                    <?php if (is_premium_or_admin()): ?>
                        <li><a href="/Restaurant_PHP/dish_manage.php">Quản lý món ăn</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="nav-icons">
                <div class="icon-wrapper">
                    <a href="<?php echo BASE_URL; ?>cart" style="text-decoration: none; color: inherit;">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cart-count"><?php echo htmlspecialchars($cart_count); ?></span>
                    </a>
                </div>
                <div class="icon-wrapper">
                    <a href="<?php echo BASE_URL; ?>favorites.php" style="text-decoration: none; color: inherit;">
                        <i class="nav-icon fas fa-heart"></i>
                        <span class="cart-count" id="wishlist-count"><?php echo htmlspecialchars($wishlist_count); ?></span>
                    </a>
                </div>
                <div class="icon-wrapper user-icon">
                    <i class="nav-icon fas fa-user"></i>
                    <div class="user-menu" id="userMenu">
                        <?php if (is_logged_in()): ?>
                            <a href="/Restaurant_PHP/my_orders.php"><i class="fas fa-user-circle"></i> Hồ sơ (<?php echo htmlspecialchars($user_name); ?>)</a>
                            <?php if (is_admin()): ?>
                                <a href="/Restaurant_PHP/admin_dashboard.php"><i class="fas fa-cog"></i> Bảng điều khiển Quản trị</a>
                                <a href="/Restaurant_PHP/admin_users.php"><i class="fas fa-users"></i> Quản lý người dùng</a>
                            <?php endif; ?>
                            <?php if (is_premium_or_admin()): ?>
                                <a href="/Restaurant_PHP/dish_manage.php"><i class="fas fa-utensils"></i> Quản lý món ăn</a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL . 'cart'; ?>"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
                            <a href="/Restaurant_PHP/checkout.php"><i class="fas fa-credit-card"></i> Thanh toán</a>
                            <a href="/Restaurant_PHP/orders.php"><i class="fas fa-list-alt"></i> Đơn hàng</a>
                            <hr>
                            <a href="/Restaurant_PHP/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        <?php else: ?>
                            <a href="/Restaurant_PHP/login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                            <a href="/Restaurant_PHP/register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script src="<?php echo BASE_URL; ?>assets/js/header.js"></script>