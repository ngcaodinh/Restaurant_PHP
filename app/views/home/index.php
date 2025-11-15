<?php
// This view preserves the original HTML/CSS and behavior from legacy index.php
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars('CTUT Restaurant - Món ngon sinh viên', ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
</head>

<style>
    .pagination.custom-pagination .page-item .page-link {
        color: #6c757d;
        border: none;
        border-radius: 50%;
        margin: 0 5px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .pagination.custom-pagination .page-item.active .page-link {
        background-color: #ff8c00;
        color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }

    .pagination.custom-pagination .page-item:not(.active) .page-link:hover {
        background-color: #f8f9fa;
        color: #ff8c00;
    }

    .pagination.custom-pagination .page-item.disabled .page-link {
        color: #ced4da;
        background-color: transparent;
    }
</style>


<body>
    <?php require_once __DIR__ . '/../../../templates/header.php'; ?>

    <!-- Debug comments preserved to match legacy -->
    <?php echo "<!-- Debug: Số món ăn: " . count($dishes) . " -->"; ?>
    <?php echo "<!-- Debug: Raw categories: " . $debug_raw . " -->"; ?>
    <?php echo "<!-- Debug: Processed categories: " . $debug_processed . " -->"; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red; padding: 20px; background: #ffe6e6; margin: 20px;">
            <h3>Lỗi:</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="social-sidebar">
        <a href="https://facebook.com" class="social-item facebook" title="Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://www.messenger.com/" class="social-item messenger" title="Messenger">
            <i class="fab fa-facebook-messenger"></i>
        </a>
        <a href="tel:0123456789" class="social-item phone" title="Gọi ngay">
            <i class="fas fa-phone"></i>
        </a>
        <a href="https://zalo.me/" class="social-item zalo" title="Zalo">
            <i class="fas fa-comment"></i>
        </a>
        <a href="https://www.instagram.com/" class="social-item instagram" title="Instagram">
            <i class="fab fa-instagram"></i>
        </a>
    </div>

    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Chào mừng đến với CTUT Restaurant</h1>
            <p>🎓 Món ngon dành riêng cho sinh viên - Giá cả phải chăng, chất lượng tuyệt vời!</p>
            <a href="#dishes" class="cta-button">
                <i class="fas fa-utensils"></i> Đặt món ngay
            </a>
        </div>
    </section>

    <section class="featured-section" id="dishes">
        <div class="container">
            <h2 class="section-title">🍽 Món ăn nổi bật</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterDishes('all')">
                    <i class="fas fa-utensils"></i> Tất cả
                </button>
                <button class="filter-btn" onclick="filterDishes('mn-chnh')">
                    <i class="fas fa-drumstick-bite"></i> Món chính
                </button>
                <button class="filter-btn" onclick="filterDishes('trng-ming')">
                    <i class="fas fa-ice-cream"></i> Tráng miệng
                </button>
                <button class="filter-btn" onclick="filterDishes('-ung')">
                    <i class="fas fa-glass-whiskey"></i> Đồ uống
                </button>
            </div>
            <div class="best-seller-section">
                <h2 class="best-seller-title">Menu</h2>
                <div class="dish-grid" id="dish-grid-container">
                    <?php foreach ($dishes as $dish): ?>
                        <div class="dish-card fade-in <?php echo $dish['is_best_seller'] ? 'best-seller' : ''; ?>"
                            data-dish-id="<?php echo htmlspecialchars($dish['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-category="<?php echo htmlspecialchars($dish['category'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-sales="<?php echo htmlspecialchars($dish['sales_count'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php if ($dish['is_best_seller']): ?>
                                <div class="best-seller-badge">BEST SELLER</div>
                                <div class="trending-effect"></div>
                            <?php endif; ?>
                            <?php if ($dish['sales_count'] > 100): ?>
                                <div class="popularity-indicator"><i class="fas fa-chart-line"></i> Hot</div>
                            <?php endif; ?>
                            <div class="dish-image">
                                <img src="<?php echo htmlspecialchars($dish['image_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="sales-stats">
                                    <i class="fas fa-shopping-cart"></i> <?php echo htmlspecialchars($dish['sales_count'], ENT_QUOTES, 'UTF-8'); ?> đã bán
                                </div>
                                <div class="dish-actions">
                                    <button class="action-btn" onclick="addToCart(<?php echo htmlspecialchars($dish['id'], ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <button class="action-btn wishlist" onclick="addToWishlist(<?php echo htmlspecialchars($dish['id'], ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="action-btn" onclick="showDetails(<?php echo htmlspecialchars($dish['id'], ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="dish-info">
                                <h3>
                                    <?php echo htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    <span class="dish-price"><?php echo number_format($dish['price'], 0, ',', '.'); ?>đ</span>
                                </h3>
                                <p><?php echo htmlspecialchars($dish['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="dish-actions-bottom">
                                    <button class="btn btn-buy-now" onclick="buyNow(<?php echo htmlspecialchars($dish['id'], ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="fas fa-bolt"></i> Mua ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div id="pagination-container">
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination custom-pagination justify-content-center mt-5">
                                <!-- Previous Button -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="javascript:void(0);" onclick="loadPage(<?php echo $page - 1; ?>)" aria-label="Previous">
                                        <i class="fas fa-arrow-left"></i>
                                    </a>
                                </li>

                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="javascript:void(0);" onclick="loadPage(<?php echo $i; ?>)"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next Button -->
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="javascript:void(0);" onclick="loadPage(<?php echo $page + 1; ?>)" aria-label="Next">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <div class="modal-image">
                <img id="modalImage" src="" alt="">
            </div>
            <div class="modal-info">
                <h2 class="modal-title" id="modalTitle"></h2>
                <p class="modal-description" id="modalDescription"></p>
                <div class="modal-price" id="modalPrice"></div>
                <div class="modal-actions">
                    <button class="modal-btn btn-buy-now" onclick="buyNow()">
                        <i class="fas fa-bolt"></i> Mua ngay
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="zoomModal" class="zoom-modal">
        <span class="zoom-close" onclick="closeZoomModal()">×</span>
        <div class="zoom-modal-content">
            <img id="zoomImage" src="" alt="">
        </div>
    </div>

    <script>
        const products = <?php echo $products_json; ?>.reduce((obj, item) => {
            obj[item.id] = item;
            return obj;
        }, {});
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>

    <?php require_once __DIR__ . '/../../../templates/footer.php'; ?>

    <script>
        function scrollToElement(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                const offset = 80;
                const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
                window.scrollTo({
                    top: elementPosition - offset,
                    behavior: 'smooth'
                });
            } else {
                console.error('Không tìm thấy phần tử với ID: ' + elementId);
            }
        }

        if (window.location.pathname.includes('index.php') || window.location.pathname === '<?php echo BASE_URL; ?>' || window.location.pathname === '<?php echo BASE_URL; ?>index.php') {
            const menuLinks = document.querySelectorAll('a[href="#dishes"]');
            const aboutLinks = document.querySelectorAll('a[href="#contact"]');
            const menuLink_dish = document.querySelectorAll('a[href="#dishes"]');

            menuLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    scrollToElement('dishes');
                });
            });
            menuLink_dish.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    scrollToElement('dishes');
                });
            });
            aboutLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    scrollToElement('contact');
                });
            });
        }
    </script>
</body>

</html>