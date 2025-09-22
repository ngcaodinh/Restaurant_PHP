<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

check_permission(['Admin', 'PremiumUser']);

$errors = [];

// Xử lý xóa mềm
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE dishes SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        header('Location: dish_manage.php');
        exit;
    } catch (PDOException $e) {
        $errors[] = 'Lỗi xóa món ăn: ' . $e->getMessage();
        error_log("Delete error: " . $e->getMessage());
    }
}

// Lấy danh mục
$categories = [];
try {
    $pdo = Database::getInstance();
    $stmt = $pdo->query('SELECT id, name FROM categories WHERE deleted_at IS NULL ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Lỗi lấy danh mục: ' . $e->getMessage();
    error_log("Category error: " . $e->getMessage());
}

// Lọc và tìm kiếm với phân trang
$filter_category = (int)($_GET['category_id'] ?? 0);
$filter_price = sanitize_input($_GET['price_range'] ?? 'all');
$filter_status = isset($_GET['status']) && in_array($_GET['status'], ['Available', 'Unavailable', 'any']) ? $_GET['status'] : 'any';
$search_query = sanitize_input($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_conditions = ['d.deleted_at IS NULL', 'c.deleted_at IS NULL'];
$params = [];
if ($filter_category > 0) {
    $where_conditions[] = 'd.category_id = ?';
    $params[] = $filter_category;
}
if ($filter_price !== 'all') {
    switch ($filter_price) {
        case 'under_50':
            $where_conditions[] = 'd.price < 50000';
            break;
        case '50_to_100':
            $where_conditions[] = 'd.price BETWEEN 50000 AND 100000';
            break;
        case 'above_100':
            $where_conditions[] = 'd.price > 100000';
            break;
    }
}
if ($filter_status !== 'any') {
    $where_conditions[] = 'd.status = ?';
    $params[] = $filter_status;
}
if ($search_query) {
    $where_conditions[] = 'LOWER(d.name) LIKE ?';
    $params[] = '%' . strtolower($search_query) . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
$count_query = "SELECT COUNT(*) 
                FROM dishes d 
                LEFT JOIN categories c ON d.category_id = c.id 
                $where_clause";
try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_dishes = $stmt->fetchColumn();
    $total_pages = ceil($total_dishes / $per_page);
} catch (PDOException $e) {
    $errors[] = 'Lỗi đếm món ăn: ' . $e->getMessage();
    error_log("Count query error: " . $e->getMessage());
    $total_dishes = 0;
    $total_pages = 1;
}

$query = "SELECT d.id, d.name, d.price, d.description, d.category_id, d.image, d.status, c.name AS category_name 
          FROM dishes d 
          LEFT JOIN categories c ON d.category_id = c.id 
          $where_clause 
          ORDER BY d.updated_at DESC 
          LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Lỗi truy vấn danh sách món ăn: ' . $e->getMessage();
    error_log("Query error: " . $e->getMessage());
    $dishes = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Quản lý Món ăn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/dish_manage.css">
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/header.css">

</head>
<body>
    <?php include 'templates/header.php'; ?>
    <div class="background-overlay"></div>

    <div class="container">
        <h1 class="page-title">Quản lý Món ăn</h1>

        <div id="message-area"></div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <div class="filter-group">
                <label for="category_id">Danh mục</label>
                <select id="category_id" name="category_id" onchange="applyFilters()">
                    <option value="0" <?php echo $filter_category == 0 ? 'selected' : ''; ?>>Tất cả</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $filter_category == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="price_range">Giá</label>
                <select id="price_range" name="price_range" onchange="applyFilters()">
                    <option value="all" <?php echo $filter_price == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="under_50" <?php echo $filter_price == 'under_50' ? 'selected' : ''; ?>>Dưới 50,000 VNĐ</option>
                    <option value="50_to_100" <?php echo $filter_price == '50_to_100' ? 'selected' : ''; ?>>50,000 - 100,000 VNĐ</option>
                    <option value="above_100" <?php echo $filter_price == 'above_100' ? 'selected' : ''; ?>>Trên 100,000 VNĐ</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" onchange="applyFilters()">
                    <option value="any" <?php echo $filter_status == 'any' ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="Available" <?php echo $filter_status == 'Available' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="Unavailable" <?php echo $filter_status == 'Unavailable' ? 'selected' : ''; ?>>Không hoạt động</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="search">Tìm kiếm</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm kiếm món ăn" onkeyup="applyFilters()">
            </div>
        </div>

        <button class="add-btn" onclick="openModal()">Thêm món ăn</button>

        <table class="table" id="dish-table">
            <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tên món ăn</th>
                    <th>Giá</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="dish-tbody">
                <?php foreach ($dishes as $dish): ?>
                    <tr data-tooltip="<?php echo htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <td>
                            <?php if ($dish['image']): ?>
                                <img src="<?php echo htmlspecialchars($dish['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 50px; max-height: 50px;">
                            <?php else: ?>
                                <span>Không có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo number_format($dish['price'], 0, ',', '.') ?> VNĐ</td>
                        <td><?php echo htmlspecialchars($dish['category_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $dish['status'] == 'Available' ? 'Hoạt động' : 'Không hoạt động'; ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick='openModal(<?php echo json_encode($dish, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>)'>Sửa</button>
                            <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $dish['id']; ?>)">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($total_pages > 1): ?>
                <p>Trang <?php echo $page; ?> của <?php echo $total_pages; ?> | Tổng: <?php echo $total_dishes; ?> món ăn</p>
                <ul>
                    <?php if ($page > 1): ?>
                        <li><a href="?page=<?php echo $page - 1; ?>&category_id=<?php echo $filter_category; ?>&price_range=<?php echo $filter_price; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_query); ?>">Trước</a></li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++): ?>
                        <li><a href="?page=<?php echo $i; ?>&category_id=<?php echo $filter_category; ?>&price_range=<?php echo $filter_price; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_query); ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li><a href="?page=<?php echo $page + 1; ?>&category_id=<?php echo $filter_category; ?>&price_range=<?php echo $filter_price; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search_query); ?>">Sau</a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal" id="dishModal">
        <div class="modal-content">
            <h2 id="modalTitle">Thêm món ăn</h2>
            <div id="modal-message"></div>
            <form id="dishForm" enctype="multipart/form-data">
                <input type="hidden" id="dish_id" name="dish_id" value="0">
                <div class="form-group">
                    <label for="name">Tên món ăn</label>
                    <input type="text" id="name" name="name" required aria-required="true">
                </div>
                <div class="form-group">
                    <label for="price">Giá (VNĐ)</label>
                    <input type="number" id="price" name="price" step="1000" min="1000" required aria-required="true">
                </div>
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="category_id_form">Danh mục</label>
                    <select id="category_id_form" name="category_id" required aria-required="true">
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Hình ảnh</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small>Chấp nhận JPEG, PNG, GIF, WebP, tối đa 16MB</small>
                </div>
                <div class="form-group">
                    <label for="status_form">Trạng thái</label>
                    <select id="status_form" name="status" required aria-required="true">
                        <option value="Available">Hoạt động</option>
                        <option value="Unavailable">Không hoạt động</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="save-btn">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/Restaurant_PHP/assets/js/dish_manage.js"></script>
</body>
</html>