<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Món ngon sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/style.css">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/responsive.css">
</head>

<body>
    <!-- Social Media Sidebar -->
    <div class="social-sidebar">
        <a href="https://facebook.com" class="social-item facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.messenger.com/" class="social-item messenger" title="Messenger"><i class="fab fa-facebook-messenger"></i></a>
        <a href="tel:0123456789" class="social-item phone" title="Gọi ngay"><i class="fas fa-phone"></i></a>
        <a href="https://zalo.me/" class="social-item zalo" title="Zalo"><i class="fas fa-comment"></i></a>
        <a href="https://www.instagram.com/" class="social-item instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="top-nav">
                <div class="logo">
                    <a href="#home" class="logo">
                        <span class="logo-icon">🍜</span>
                        <span class="logo-text">CTUT Restaurant</span>
                    </a>
                </div>
                <div class="search-bar">
                    <input type="text" placeholder="Tìm kiếm..." id="searchInput" />
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="hamburger">
                    <i class="fas fa-bars"></i>
                </div>
                <nav class="menu-nav">
                    <ul>
                        <li><a href="#home">Trang chủ</a></li>
                        <li><a href="#menu">Menu</a></li>
                        <li class="dropdown">
                            <a href="#about">Giới thiệu</a>
                            <div class="dropdown-content">
                                <a href="#about-us">Về chúng tôi</a>
                                <a href="#team">Đội ngũ</a>
                            </div>
                        </li>
                        <li class="dropdown">
                            <a href="#contact">Liên hệ</a>
                            <div class="dropdown-content">
                                <a href="#support">Hỗ trợ</a>
                                <a href="#feedback">Góp ý</a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="nav-icons">
                    <div class="icon-wrapper">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cart-count">0</span>
                    </div>
                    <div class="icon-wrapper">
                        <i class="nav-icon fas fa-heart"></i>
                        <span class="cart-count" id="wishlist-count">0</span>
                    </div>
                    <div class="icon-wrapper" onclick="toggleAccountDropdown()">
                        <i class="nav-icon fas fa-user"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Chào mừng đến với CTUT Restaurant</h1>
            <p>🎓 Món ngon dành riêng cho sinh viên - Giá cả phải chăng, chất lượng tuyệt vời!</p>
            <a href="#menu" class="cta-button"><i class="fas fa-utensils"></i> Đặt món ngay</a>
        </div>
    </section>

    <!-- Featured Dishes -->
    <section class="featured-section" id="menu">
        <div class="container">
            <h2 class="section-title">🍽️ Món ăn nổi bật</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterDishes('all')"><i class="fas fa-utensils"></i> Tất cả</button>
                <button class="filter-btn" onclick="filterDishes('bestseller')"><i class="fas fa-fire"></i> Bán chạy</button>
                <button class="filter-btn" onclick="filterDishes('new')"><i class="fas fa-star"></i> Mới nhất</button>
            </div>
            <div class="best-seller-section">
                <h2 class="best-seller-title">Top Món Bán Chạy</h2>
                <div class="dish-grid">
                    <?php
                    // Dữ liệu tĩnh cho món ăn
                    $dishes = [
                        [
                            'id' => 1,
                            'name' => 'Cơm Tấm Sườn Nướng',
                            'price' => '35.000đ',
                            'description' => 'Cơm tấm thơm ngon với sườn nướng đậm đà, kèm trứng ốp la và chả cá.',
                            'image' => '/Restaurant_CTUT_PHP/assets/images/dishes/com-tam.jpg',
                            'sales_count' => 156,
                            'is_best_seller' => true,
                            'category' => 'bestseller'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Phở Bò',
                            'price' => '40.000đ',
                            'description' => 'Phở bò truyền thống với nước dùng thơm ngon, thịt bò mềm.',
                            'image' => '/Restaurant_CTUT_PHP/assets/images/dishes/pho-bo.jpg',
                            'sales_count' => 89,
                            'is_best_seller' => false,
                            'category' => 'new'
                        ]
                    ];
                    foreach ($dishes as $dish):
                    ?>
                        <div class="dish-card fade-in <?php echo $dish['is_best_seller'] ? 'best-seller' : ''; ?>" data-category="<?php echo $dish['category']; ?>" data-sales="<?php echo $dish['sales_count']; ?>">
                            <?php if ($dish['is_best_seller']): ?>
                                <div class="best-seller-badge">BEST SELLER</div>
                                <div class="trending-effect"></div>
                            <?php endif; ?>
                            <?php if ($dish['sales_count'] > 100): ?>
                                <div class="popularity-indicator"><i class="fas fa-chart-line"></i> Hot</div>
                            <?php endif; ?>
                            <div class="dish-image" style="background-image: url('<?php echo $dish['image']; ?>');">
                                <div class="sales-stats"><i class="fas fa-shopping-cart"></i> <?php echo $dish['sales_count']; ?> đã bán</div>
                                <div class="dish-actions">
                                    <button class="action-btn" onclick="addToCart(<?php echo $dish['id']; ?>)"><i class="fas fa-shopping-cart"></i></button>
                                    <button class="action-btn wishlist" onclick="addToWishlist(<?php echo $dish['id']; ?>)"><i class="fas fa-heart"></i></button>
                                    <button class="action-btn" onclick="showDetails(<?php echo $dish['id']; ?>)"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="dish-info">
                                <h3><?php echo $dish['name']; ?> <span class="dish-price"><?php echo $dish['price']; ?></span></h3>
                                <p><?php echo $dish['description']; ?></p>
                                <div class="dish-actions-bottom">
                                    <button class="btn btn-buy-now" onclick="buyNow(<?php echo $dish['id']; ?>)"><i class="fas fa-bolt"></i> Mua ngay</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-info">
                <h3>🍜 CTUT Restaurant</h3>
                <p>📍 123 Đường ABC, Quận Ninh Kiều, TP. Cần Thơ</p>
                <p>📧 contact@ctutrestaurant.com</p>
                <p>📞 Hotline: 0123 456 789</p>
                <p>🕒 Mở cửa: 6:00 - 22:00 (Thứ 2 - Chủ nhật)</p>
            </div>
            <div style="margin-top: 20px;">
                <p>© 2024 CTUT Restaurant. Món ngon dành cho sinh viên!</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to top button -->
    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="/Restaurant_PHP/assets/js/main.js"></script>
    <script src="/Restaurant_PHP/assets/js/cart.js"></script>
</body>

</html>