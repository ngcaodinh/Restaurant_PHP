<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kiểm tra quyền Admin
check_permission(['Admin']);

// Khởi tạo kết nối cơ sở dữ liệu
$pdo = Database::getInstance();

// Xử lý tìm kiếm, lọc, sắp xếp và phân trang
$searchTerm = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$roleFilter = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';
$sortField = isset($_GET['sort']) && in_array($_GET['sort'], ['name', 'email', 'phone', 'role', 'status', 'order_count', 'created_at']) ? $_GET['sort'] : 'created_at';
$sortDirection = isset($_GET['direction']) && in_array($_GET['direction'], ['asc', 'desc']) ? $_GET['direction'] : 'asc';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;

// Xây dựng câu truy vấn và tham số
$where = "WHERE u.deleted_at IS NULL";
$params = [];

if ($searchTerm) {
    $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

if ($statusFilter) {
    $where .= " AND u.status = ?";
    $params[] = $statusFilter;
}

if ($roleFilter) {
    $where .= " AND u.role = ?";
    $params[] = $roleFilter;
}

$orderBy = "ORDER BY $sortField $sortDirection";

// Tính tổng số người dùng
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users u $where");
$stmt->execute($params);
$totalUsers = $stmt->fetch()['total'];
$totalPages = ceil($totalUsers / $itemsPerPage);
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $itemsPerPage;

// Lấy danh sách người dùng
$query = "
    SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_price), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.deleted_at IS NULL
    $where
    GROUP BY u.id
    $orderBy
    LIMIT ?, ?
";
$stmt = $pdo->prepare($query);
$params[] = $offset;
$params[] = $itemsPerPage;
$stmt->execute($params);
$users = $stmt->fetchAll();

// Thống kê
$stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE deleted_at IS NULL");
$stmt->execute();
$totalUsersCount = $stmt->fetch()['total_users'];

$stmt = $pdo->prepare("SELECT COUNT(*) as active_users FROM users WHERE status = 'Active' AND deleted_at IS NULL");
$stmt->execute();
$activeUsersCount = $stmt->fetch()['active_users'];

$stmt = $pdo->prepare("SELECT COUNT(*) as new_users FROM users WHERE deleted_at IS NULL AND created_at >= ?");
$stmt->execute([date('Y-m-01')]);
$newUsersCount = $stmt->fetch()['new_users'];

$stmt = $pdo->prepare("
    SELECT COALESCE(AVG(order_count), 0) as avg_orders
    FROM (
        SELECT COUNT(o.id) as order_count
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id AND o.deleted_at IS NULL
        WHERE u.deleted_at IS NULL
        GROUP BY u.id
    ) as subquery
");
$stmt->execute();
$avgOrders = round($stmt->fetch()['avg_orders'], 1);

// Xử lý thêm/sửa/xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        $pdo->beginTransaction();
        if ($action === 'add') {
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $address = sanitize_input($_POST['address'], true);
            $role = in_array($_POST['role'], ['Admin', 'User', 'PremiumUser']) ? $_POST['role'] : 'User';
            $status = in_array($_POST['status'], ['Active', 'Inactive']) ? $_POST['status'] : 'Active';
            $password = $_POST['password'] ?? '';

            if (!$name) {
                throw new Exception('Họ và tên là bắt buộc khi thêm người dùng');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ');
            }
            if ($phone && !preg_match('/^[0-9]{10,15}$/', $phone)) {
                throw new Exception('Số điện thoại không hợp lệ');
            }
            if (!$password) {
                throw new Exception('Mật khẩu là bắt buộc khi thêm người dùng');
            }
            if (strlen($password) < 8) {
                throw new Exception('Mật khẩu phải có ít nhất 8 ký tự');
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email đã tồn tại');
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, address, role, status, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone ?: null, $address ?: null, $role, $status, $hashedPassword]);
            $message = 'Thêm người dùng thành công!';
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $name = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $phone = sanitize_input($_POST['phone']);
            $address = sanitize_input($_POST['address'], true);
            $role = in_array($_POST['role'], ['Admin', 'User', 'PremiumUser']) ? $_POST['role'] : 'User';
            $status = in_array($_POST['status'], ['Active', 'Inactive']) ? $_POST['status'] : 'Active';
            $password = $_POST['password'] ?? '';

            // Lấy thông tin người dùng hiện tại
            $stmt = $pdo->prepare("SELECT name, email, phone, address, role, status FROM users WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $currentUser = $stmt->fetch();

            if (!$currentUser) {
                throw new Exception('Không tìm thấy người dùng');
            }

            // Chuẩn bị câu truy vấn động
            $updateFields = [];
            $params = [];

            if ($name !== '' && $name !== $currentUser['name']) {
                $updateFields[] = 'name = ?';
                $params[] = $name;
            }

            if ($email !== '' && $email !== $currentUser['email']) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email không hợp lệ');
                }
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL");
                $stmt->execute([$email, $id]);
                if ($stmt->fetch()) {
                    throw new Exception('Email đã tồn tại');
                }
                $updateFields[] = 'email = ?';
                $params[] = $email;
            }

            if ($phone !== '' && $phone !== $currentUser['phone']) {
                if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
                    throw new Exception('Số điện thoại không hợp lệ');
                }
                $updateFields[] = 'phone = ?';
                $params[] = $phone;
            } elseif ($phone === '' && $currentUser['phone'] !== null) {
                $updateFields[] = 'phone = NULL';
            }

            if ($address !== '' && $address !== $currentUser['address']) {
                $updateFields[] = 'address = ?';
                $params[] = $address;
            } elseif ($address === '' && $currentUser['address'] !== null) {
                $updateFields[] = 'address = NULL';
            }

            if ($role !== $currentUser['role']) {
                $updateFields[] = 'role = ?';
                $params[] = $role;
            }

            if ($status !== $currentUser['status']) {
                $updateFields[] = 'status = ?';
                $params[] = $status;
            }

            if ($password !== '') {
                if (strlen($password) < 8) {
                    throw new Exception('Mật khẩu phải có ít nhất 8 ký tự');
                }
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $updateFields[] = 'password = ?';
                $params[] = $hashedPassword;
            }

            // Nếu có trường cần cập nhật
            if (!empty($updateFields)) {
                $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
                $params[] = $id;
                $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ? AND deleted_at IS NULL";
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
            }
            $message = 'Cập nhật người dùng thành công!';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $message = 'Xóa người dùng thành công!';
        }

        $pdo->commit();
        redirect("admin_dashboard.php?message=" . urlencode($message));
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Handle AJAX request for getting user details
if (isset($_GET['action']) && $_GET['action'] === 'get_user' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng']);
    }
    exit;
}
// Xử lý AJAX tìm kiếm người dùng
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json');

    // Lấy tham số tìm kiếm
    $searchTerm = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
    $statusFilter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
    $roleFilter = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';
    $sortField = isset($_GET['sort']) && in_array($_GET['sort'], ['id', 'name', 'email', 'phone', 'role', 'status', 'order_count', 'created_at']) ? $_GET['sort'] : 'created_at';
    $sortDirection = isset($_GET['direction']) && in_array($_GET['direction'], ['asc', 'desc']) ? $_GET['direction'] : 'asc';
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 10;

    // Xây dựng câu truy vấn
    $where = "WHERE u.deleted_at IS NULL";
    $params = [];

    if ($searchTerm) {
        $where .= " AND (LOWER(u.name) LIKE LOWER(?) OR LOWER(u.email) LIKE LOWER(?) OR u.phone LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
    }

    if ($statusFilter) {
        $where .= " AND u.status = ?";
        $params[] = $statusFilter;
    }

    if ($roleFilter) {
        $where .= " AND u.role = ?";
        $params[] = $roleFilter;
    }

    $orderBy = "ORDER BY $sortField $sortDirection";

    // Tính tổng số người dùng
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users u $where");
    $stmt->execute($params);
    $totalUsers = $stmt->fetch()['total'];
    $totalPages = ceil($totalUsers / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Lấy danh sách người dùng
    $query = "
        SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_price), 0) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id AND o.deleted_at IS NULL
        $where
        GROUP BY u.id
        $orderBy
        LIMIT ?, ?
    ";
    $params[] = $offset;
    $params[] = $itemsPerPage;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Trả về dữ liệu JSON
    echo json_encode([
        'success' => true,
        'users' => $users,
        'totalUsers' => $totalUsers,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Quản Lý Người Dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="assets/css/header.css">

</head>

<body>
    <div class="background-overlay"></div>
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-spinner"></div>
    </div>

    <?php include 'templates/header.php'; ?>

    <div class="main-container">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header fade-in">
                <div class="page-title">
                    <div class="icon"><i class="fas fa-users-cog"></i></div>
                    <h1>Quản Lý Người Dùng</h1>
                </div>
                <div class="page-subtitle">Quản lý thông tin và theo dõi hoạt động của người dùng trong hệ thống CTUT Restaurant</div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalUsersCount, 0, ',', '.'); ?></div>
                    <div class="stat-label">Tổng người dùng</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                    </div>
                    <div class="stat-value"><?php echo number_format($activeUsersCount, 0, ',', '.'); ?></div>
                    <div class="stat-label">Người dùng hoạt động</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    </div>
                    <div class="stat-value"><?php echo number_format($newUsersCount, 0, ',', '.'); ?></div>
                    <div class="stat-label">Đăng ký mới (tháng này)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $avgOrders; ?></div>
                    <div class="stat-label">Đơn hàng TB/người</div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="content-card fade-in">
                <div class="content-header">
                    <h2 class="content-title">Danh Sách Người Dùng</h2>
                    <div class="content-controls">
                        <div class="search-container">
                            <i class="fas fa-search search-icon-user"></i>
                            <input type="text" id="userSearchInput" class="search-input" placeholder="Tìm kiếm theo tên, email, SĐT..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        </div>
                        <div class="filter-group">
                            <select id="statusFilter" class="filter-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                            </select>
                            <select id="roleFilter" class="filter-select">
                                <option value="">Tất cả vai trò</option>
                                <option value="Admin" <?php echo $roleFilter === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="User" <?php echo $roleFilter === 'User' ? 'selected' : ''; ?>>User</option>
                                <option value="PremiumUser" <?php echo $roleFilter === 'PremiumUser' ? 'selected' : ''; ?>>PremiumUser</option>
                            </select>
                            <button class="btn btn-primary" id="addCustomerBtn">
                                <i class="fas fa-plus"></i> Thêm người dùng
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container" id="tableContainer">
                    <div class="table-wrapper">
                        <table class="table" id="customersTable">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="name">Tên người dùng <i class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" data-sort="email">Email <i class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" data-sort="phone">Số điện thoại <i class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" data-sort="role">Vai trò <i class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" data-sort="status">Trạng thái <i class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" data-sort="order_count">Đơn hàng <i class="fas fa-sort sort-icon"></i></th>
                                    <th class="sortable" data-sort="created_at">Ngày đăng ký <i class="fas fa-sort sort-icon"></i></th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="customersTableBody">
                                <?php foreach ($users as $user): ?>
                                    <tr class="fade-in">
                                        <td>
                                            <div class="customer-info">
                                                <div class="customer-avatar"><?php echo htmlspecialchars(substr($user['name'], 0, 1)); ?></div>
                                                <div class="customer-details">
                                                    <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                                                    <p>ID: <?php echo $user['id']; ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></td>
                                        <td>
                                            <span class="type-badge type-<?php echo strtolower($user['role']); ?>">
                                                <i class="<?php
                                                            $icons = ['Admin' => 'fas fa-user-shield', 'User' => 'fas fa-user', 'PremiumUser' => 'fas fa-crown'];
                                                            echo $icons[$user['role']] ?? 'fas fa-user';
                                                            ?>"></i>
                                                <?php
                                                $labels = ['Admin' => 'Quản trị', 'User' => 'Thường', 'PremiumUser' => 'Premium'];
                                                echo $labels[$user['role']] ?? $user['role'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($user['status']); ?>">
                                                <i class="<?php
                                                            $icons = ['Active' => 'fas fa-check-circle', 'Inactive' => 'fas fa-times-circle'];
                                                            echo $icons[$user['status']] ?? 'fas fa-question-circle';
                                                            ?>"></i>
                                                <?php
                                                $labels = ['Active' => 'Hoạt động', 'Inactive' => 'Không hoạt động'];
                                                echo $labels[$user['status']] ?? $user['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo $user['order_count']; ?></div>
                                            <div style="font-size: 0.875rem; color: var(--text-secondary);">
                                                <?php echo format_currency($user['total_spent']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div><?php echo format_date($user['created_at']); ?></div>
                                            <div style="font-size: 0.875rem; color: var(--text-secondary);">
                                                Lần cuối: <?php echo $user['last_login'] ? format_date($user['last_login']) : 'Chưa đăng nhập'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-icon btn-primary" onclick="viewCustomer(<?php echo $user['id']; ?>)" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-icon btn-warning" onclick="editCustomer(<?php echo $user['id']; ?>)" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-icon btn-danger" onclick="deleteCustomer(<?php echo $user['id']; ?>)" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div class="empty-state" id="emptyState" <?php echo $totalUsers > 0 ? 'style="display: none;"' : ''; ?>>
                        <i class="fas fa-users"></i>
                        <h3>Không tìm thấy người dùng</h3>
                        <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Hiển thị <?php echo min($offset + 1, $totalUsers); ?>-<?php echo min($offset + $itemsPerPage, $totalUsers); ?> của <?php echo number_format($totalUsers, 0, ',', '.'); ?> người dùng
                        </div>
                        <div class="pagination" id="pagination">
                            <?php if ($totalPages > 1): ?>
                                <button class="pagination-btn" onclick="changePage(<?php echo $currentPage - 1; ?>)" <?php echo $currentPage === 1 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                if ($startPage > 1): ?>
                                    <button class="pagination-btn" onclick="changePage(1)">1</button>
                                    <?php if ($startPage > 2): ?>
                                        <span style="padding: 0 var(--space-sm);">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <button class="pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>" onclick="changePage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                                <?php endfor; ?>
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <span style="padding: 0 var(--space-sm);">...</span>
                                    <?php endif; ?>
                                    <button class="pagination-btn" onclick="changePage(<?php echo $totalPages; ?>)"><?php echo $totalPages; ?></button>
                                <?php endif; ?>
                                <button class="pagination-btn" onclick="changePage(<?php echo $currentPage + 1; ?>)" <?php echo $currentPage === $totalPages ? 'disabled' : ''; ?>>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Customer Modal -->
    <div class="modal" id="customerModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h3 id="modalTitle">Thêm người dùng mới</h3>
                    <button class="close-btn" id="closeModal">×</button>
                </div>
            </div>
            <form id="customerForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="customerId">
                <div class="form-group">
                    <label for="customerName">Họ và tên *</label>
                    <input type="text" id="customerName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="customerEmail">Email *</label>
                    <input type="email" id="customerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="customerPhone">Số điện thoại</label>
                    <input type="tel" id="customerPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="customerPassword">Mật khẩu<?php echo isset($_GET['action']) && $_GET['action'] === 'edit' ? '' : ' *'; ?></label>
                    <div class="password-wrapper">
                        <input type="password" id="customerPassword" name="password" <?php echo isset($_GET['action']) && $_GET['action'] === 'edit' ? '' : 'required'; ?>>
                        <span class="toggle-password" onclick="togglePasswordVisibility('customerPassword')"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customerAddress">Địa chỉ</label>
                    <textarea id="customerAddress" name="address" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="customerRole">Vai trò</label>
                    <select id="customerRole" name="role">
                        <option value="User">Thường</option>
                        <option value="PremiumUser">Premium</option>
                        <option value="Admin">Quản trị</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="customerStatus">Trạng thái</label>
                    <select id="customerStatus" name="status">
                        <option value="Active">Hoạt động</option>
                        <option value="Inactive">Không hoạt động</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="cancelBtn">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Toast Notification -->
    <div class="toast <?php echo isset($message) ? 'success show' : (isset($error) ? 'error show' : ''); ?>" id="toast">
        <div class="toast-content">
            <span id="toastMessage"><?php echo isset($message) ? htmlspecialchars($message) : (isset($error) ? htmlspecialchars($error) : ''); ?></span>
            <button onclick="this.parentElement.parentElement.classList.remove('show')"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <!-- Pass PHP variables to JavaScript -->
    <script>
        window.appConfig = {
            searchTerm: '<?php echo addslashes(urlencode($searchTerm)); ?>',
            statusFilter: '<?php echo addslashes(urlencode($statusFilter)); ?>',
            roleFilter: '<?php echo addslashes(urlencode($roleFilter)); ?>',
            sortField: '<?php echo addslashes($sortField); ?>',
            sortDirection: '<?php echo addslashes($sortDirection); ?>'
        };
    </script>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="assets/js/admin_dashboard.js"></script>
</body>

</html>