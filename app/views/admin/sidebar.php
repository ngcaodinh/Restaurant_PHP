<?php

/**
 * Template cho Sidebar (thanh điều hướng bên) của trang Admin
 *
 * Chứa các liên kết đến các trang quản lý chính của admin.
 */

// Lấy trang hiện tại để active menu item
$current_page = basename($_SERVER['REQUEST_URI']);
?>

<style>
    /* Custom style to ensure text is white on active sidebar item */
    .sidenav .navbar-nav .nav-item .nav-link.active {
        color: white !important;
    }

    .sidenav .navbar-nav .nav-item .nav-link.active .nav-link-text {
        color: white !important;
    }
</style>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 my-2 bg-white" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="<?php echo BASE_URL; ?>admin/dashboard">
            <span class="ms-1 text-sm text-dark font-weight-bolder">CTUT Restaurant </span>
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link text-dark <?php echo ($current_page == 'dashboard') ? 'active bg-gradient-dark' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard">
                    <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-symbols-rounded opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Thống kê</span>
                </a>
            </li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                <!-- Quản lý người dùng -->
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo ($current_page == 'users') ? 'active bg-gradient-dark' : ''; ?>" href="<?php echo BASE_URL; ?>admin/users">
                        <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-symbols-rounded opacity-10">group</i>
                        </div>
                        <span class="nav-link-text ms-1">Quản lý tài khoản</span>
                    </a>
                </li>
            <?php endif; ?>
            <!-- Quản lý món ăn -->
            <li class="nav-item">
                <a class="nav-link text-dark <?php echo ($current_page == 'dishes' || $current_page == 'manage_dishes') ? 'active bg-gradient-dark' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dishes">
                    <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-symbols-rounded opacity-10">restaurant_menu</i>
                    </div>
                    <span class="nav-link-text ms-1">Quản lý món ăn</span>
                </a>
            </li>
            <!-- Quản lý đơn hàng -->
            <li class="nav-item">
                <a class="nav-link text-dark <?php echo ($current_page == 'orders') ? 'active bg-gradient-dark' : ''; ?>" href="<?php echo BASE_URL; ?>admin/orders">
                    <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-symbols-rounded opacity-10">receipt_long</i>
                    </div>
                    <span class="nav-link-text ms-1">Quản lý đơn hàng</span>
                </a>
            </li>
            <!-- Các trang tài khoản -->
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-6">Account pages</h6>
            </li>
            <!-- Quay về trang chủ -->
            <li class="nav-item">
                <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>">
                    <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-symbols-rounded opacity-10">home</i>
                    </div>
                    <span class="nav-link-text ms-1">Trang chủ</span>
                </a>
            </li>
            <!-- Đăng xuất -->
            <li class="nav-item">
                <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>logout">
                    <div class="text-dark text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-symbols-rounded opacity-10">logout</i>
                    </div>
                    <span class="nav-link-text ms-1">Đăng xuất</span>
                </a>
            </li>
        </ul>
    </div>
</aside>