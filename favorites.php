<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Xử lý xóa món khỏi yêu thích (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        if ($_POST['action'] === 'remove_favorite') {
            $dish_id = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);

            if ($dish_id) {
                $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND dish_id = ?');
                $stmt->execute([$user_id, $dish_id]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa khỏi danh sách yêu thích!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID món ăn không hợp lệ!'
                ]);
            }
            exit();
        }

        if ($_POST['action'] === 'add_to_cart') {
            $dish_id = filter_input(INPUT_POST, 'dish_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;

            if ($dish_id && $quantity > 0) {
                $stmt = $pdo->prepare('SELECT id FROM carts WHERE user_id = ?');
                $stmt->execute([$user_id]);
                $cart = $stmt->fetch();

                if (!$cart) {
                    $stmt = $pdo->prepare('INSERT INTO carts (user_id) VALUES (?)');
                    $stmt->execute([$user_id]);
                    $cart_id = $pdo->lastInsertId();
                } else {
                    $cart_id = $cart['id'];
                }

                $stmt = $pdo->prepare('SELECT quantity FROM cart_items WHERE cart_id = ? AND dish_id = ?');
                $stmt->execute([$cart_id, $dish_id]);
                $existing_item = $stmt->fetch();

                if ($existing_item) {
                    $new_quantity = $existing_item['quantity'] + $quantity;
                    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND dish_id = ?');
                    $stmt->execute([$new_quantity, $cart_id, $dish_id]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO cart_items (cart_id, dish_id, quantity) VALUES (?, ?, ?)');
                    $stmt->execute([$cart_id, $dish_id, $quantity]);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Đã thêm vào giỏ hàng!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ!'
                ]);
            }
            exit();
        }
    } catch (PDOException $e) {
        error_log("Favorites error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra, vui lòng thử lại!'
        ]);
        exit();
    }
}

// Lấy danh sách danh mục
try {
    $stmt = $pdo->query('SELECT id, name FROM categories WHERE deleted_at IS NULL ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Categories error: " . $e->getMessage());
    $categories = [];
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT) : 0;

// Lấy danh sách món ăn yêu thích
try {
    $query = 'SELECT 
                f.id as favorite_id,
                f.created_at as favorited_at,
                d.id,
                d.name,
                d.price,
                d.description,
                d.image,
                d.status,
                c.name as category_name
              FROM favorites f
              INNER JOIN dishes d ON f.dish_id = d.id
              LEFT JOIN categories c ON d.category_id = c.id
              WHERE f.user_id = ? 
                AND d.deleted_at IS NULL';

    $params = [$user_id];

    if ($search) {
        $query .= ' AND d.name LIKE ?';
        $params[] = '%' . $search . '%';
    }

    if ($category_id > 0) {
        $query .= ' AND d.category_id = ?';
        $params[] = $category_id;
    }

    $query .= ' ORDER BY f.created_at DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_favorites = count($favorites);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $favorites = [];
    $total_favorites = 0;
    $error_message = 'Không thể tải danh sách yêu thích!';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Món Ăn Yêu Thích</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/header.css">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/favorites.css">
</head>

<body>
    <div class="background-overlay"></div>

    <?php
    try {
        include 'templates/header.php';
    } catch (Exception $e) {
        error_log("Header error: " . $e->getMessage());
    }
    ?>

    <div class="favorites-container">
        <div class="favorites-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-heart"></i>
                    Món Ăn Yêu Thích
                </h1>
                <p class="page-subtitle">
                    Bạn có <span class="count-badge"><?php echo $total_favorites; ?></span> món ăn yêu thích
                </p>
            </div>
        </div>

        <!-- Thanh tìm kiếm và bộ lọc -->
        <div class="search-filter-container">
            <form class="search-form" method="GET" action="favorites.php">
                <div class="form-group">
                    <input type="text" name="search" class="form-input" placeholder="Tìm kiếm món ăn..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="form-group">
                    <select name="category_id" class="form-input">
                        <option value="0">Tất cả danh mục</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Toast thông báo -->
        <div id="toast" class="toast">
            <i class="fas fa-check-circle"></i>
            <span id="toast-message"></span>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message show">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($favorites)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h2>Chưa có món ăn yêu thích</h2>
                <p>Hãy khám phá thực đơn và thêm những món ăn bạn yêu thích nhé!</p>
                <a href="index.php" class="explore-btn">
                    <i class="fas fa-utensils"></i>
                    Khám phá thực đơn
                </a>
            </div>
        <?php else: ?>
            <div class="favorites-grid">
                <?php foreach ($favorites as $index => $dish): ?>
                    <div class="dish-card" data-dish-id="<?php echo $dish['id']; ?>" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                        <div class="dish-image-wrapper">
                            <?php if ($dish['image']): ?>
                                <img src="<?php echo htmlspecialchars($dish['image']); ?>"
                                    alt="<?php echo htmlspecialchars($dish['name']); ?>"
                                    class="dish-image"
                                    loading="lazy">
                            <?php else: ?>
                                <div class="dish-image-placeholder">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            <?php endif; ?>

                            <button class="remove-favorite-btn"
                                data-dish-id="<?php echo $dish['id']; ?>"
                                title="Xóa khỏi yêu thích">
                                <i class="fas fa-trash"></i>
                            </button>

                            <?php if ($dish['status'] === 'Unavailable'): ?>
                                <div class="unavailable-badge">Hết hàng</div>
                            <?php endif; ?>
                        </div>

                        <div class="dish-content">
                            <div class="dish-category">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($dish['category_name'] ?? 'Chưa phân loại'); ?>
                            </div>

                            <h3 class="dish-name">
                                <?php echo htmlspecialchars($dish['name']); ?>
                            </h3>

                            <p class="dish-description">
                                <?php
                                $desc = $dish['description'] ?? 'Món ăn ngon đặc biệt tại CTUT Restaurant';
                                echo htmlspecialchars(mb_substr($desc, 0, 80)) . (mb_strlen($desc) > 80 ? '...' : '');
                                ?>
                            </p>

                            <div class="dish-footer">
                                <div class="dish-price">
                                    <?php echo format_currency($dish['price']); ?>
                                </div>

                                <?php if ($dish['status'] === 'Available'): ?>
                                    <button class="add-to-cart-btn"
                                        data-dish-id="<?php echo $dish['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i>
                                        Thêm vào giỏ
                                    </button>
                                <?php else: ?>
                                    <button class="add-to-cart-btn" disabled>
                                        <i class="fas fa-ban"></i>
                                        Hết hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="/Restaurant_PHP/assets/js/header.js"></script>
    <script src="/Restaurant_PHP/assets/js/favorites.js"></script>
</body>

</html>