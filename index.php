<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$page_title = 'CTUT Restaurant - Món ngon sinh viên';

$errors = [];
$dishes = [];

try {
    $pdo = Database::getInstance();
    // Truy vấn món ăn với status = 'Available', deleted_at IS NULL và lấy tên danh mục
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.price, d.description, d.image, d.sales_count, d.category_id, c.name AS category_name
        FROM dishes d
        LEFT JOIN categories c ON d.category_id = c.id
        WHERE d.status = 'Available' AND d.deleted_at IS NULL
        ORDER BY d.sales_count DESC
    ");
    $stmt->execute();
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: In số lượng món ăn và danh mục
    echo "<!-- Debug: Số món ăn: " . count($dishes) . " -->";
    echo "<!-- Debug: Raw categories: ";
    foreach ($dishes as $dish) {
        echo htmlspecialchars($dish['name'] . ' -> ' . $dish['category_name'] . ', ');
    }
    echo " -->";
    echo "<!-- Debug: Processed categories: ";
    foreach ($dishes as $dish) {
        echo htmlspecialchars($dish['name'] . ' -> ' . $dish['category'] . ', ');
    }
    echo " -->";
    if (empty($dishes)) {
        $errors[] = "Không tìm thấy món ăn nào với status = 'Available'.";
    }

    // Xác định món bán chạy (top 3 dựa trên sales_count)
    $best_seller_count = min(3, count($dishes));
    foreach ($dishes as $index => &$dish) {
        $dish['is_best_seller'] = $index < $best_seller_count;
        $dish['is_top_best_seller'] = $index < $best_seller_count;
        // Chuyển tên danh mục thành định dạng không dấu, viết thường
        $category_name = $dish['category_name'] ?? 'unknown';
        $categoryMap = [
            'món chính' => 'mn-chnh',
            'tráng miệng' => 'trng-ming',
            'đồ uống' => '-ung',
        ];
        $dish['category'] = $categoryMap[$category_name] ?? strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/u', '', $category_name)));
        // Xử lý hình ảnh
        $dish['image_url'] = !empty($dish['image']) ? $dish['image'] : 'https://via.placeholder.com/300x250?text=No+Image';
    }
    unset($dish); // Ngắt tham chiếu
} catch (PDOException $e) {
    $errors[] = 'Lỗi truy vấn món ăn: ' . $e->getMessage();
    error_log("Query error: " . $e->getMessage());
}

// Hiển thị lỗi nếu có
if (!empty($errors)) {
    echo '<div style="color: red; padding: 20px; background: #ffe6e6; margin: 20px;">';
    echo '<h3>Lỗi:</h3><ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul></div>';
}

// Tạo JSON cho JavaScript
$products_json = json_encode(array_map(function ($dish) {
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
        'categoryName' => $dish['category_name'] ?? 'Không xác định'
    ];
}, $dishes), JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">

</head>

<body>
    <?php require_once 'templates/header.php'; ?>

    <!-- Social Media Sidebar -->
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

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Chào mừng đến với CTUT Restaurant</h1>
            <p>🎓 Món ngon dành riêng cho sinh viên - Giá cả phải chăng, chất lượng tuyệt vời!</p>
            <a href="#dishes" class="cta-button">
                <i class="fas fa-utensils"></i> Đặt món ngay
            </a>
        </div>
    </section>

    <!-- Featured Dishes -->
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
                <h2 class="best-seller-title">Top Món Bán Chạy</h2>
                <div class="dish-grid">
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
            </div>
        </div>
    </section>

    <!-- Scroll to top button -->
    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Modal -->
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

    <!-- Zoom Modal -->
    <div id="zoomModal" class="zoom-modal">
        <span class="zoom-close" onclick="closeZoomModal()">×</span>
        <div class="zoom-modal-content">
            <img id="zoomImage" src="" alt="">
        </div>
    </div>

    <!-- JSON data for JavaScript -->
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

    <?php require_once 'templates/footer.php'; ?>

    <script>
        // Hàm cuộn mượt
        function scrollToElement(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                const offset = 80; // Khoảng cách từ đầu trang (px)
                const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
                window.scrollTo({
                    top: elementPosition - offset,
                    behavior: 'smooth'
                });
            } else {
                console.error('Không tìm thấy phần tử với ID: ' + elementId);
            }
        }

        // Kiểm tra trang index.php
        if (window.location.pathname.includes('index.php') || window.location.pathname === '<?php echo BASE_URL; ?>' || window.location.pathname === '<?php echo BASE_URL; ?>index.php') {
            // Tìm liên kết Menu và Giới thiệu
            const menuLinks = document.querySelectorAll('a[href="#dishes"]');
            const aboutLinks = document.querySelectorAll('a[href="#contact"]');
            const menuLink_dish = document.querySelectorAll('a[href="#dishes"]');
            
            console.log('Menu Links:', menuLinks);
            console.log('About Links:', aboutLinks);
            console.log('Menu Links:', menuLink_dish);
            // Gắn sự kiện cho Menu
            menuLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    console.log('Nhấn vào Menu, cuộn đến phần món ăn');
                    scrollToElement('dishes');
                });
            });
            // gắn sự kiện cho đặt món ngay
            menuLink_dish.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    console.log('Nhấn vào đặt món ngay, cuộn đến phần món ăn');
                    scrollToElement('dishes');
                });
            });
            // Gắn sự kiện cho Giới thiệu
            aboutLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    console.log('Nhấn vào Giới thiệu, cuộn đến footer');
                    scrollToElement('contact');
                });
            });
        }
    </script>
</body>

</html>