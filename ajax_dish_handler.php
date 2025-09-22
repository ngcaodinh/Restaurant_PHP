<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

check_permission(['Admin', 'PremiumUser']);

$errors = [];
$success = '';
$upload_dir = 'D:/CODE/Project/image_retaurant_php/';
$web_path = '/image_retaurant_php/';

$response = ['success' => false, 'message' => '', 'errors' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không hợp lệ.');
    }

    $dish_id = isset($_POST['dish_id']) ? (int)$_POST['dish_id'] : 0;
    $name = sanitize_input($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = sanitize_input($_POST['description'] ?? '', true);
    $category_id = (int)$_POST['category_id'] ?? 0;
    $status = isset($_POST['status']) && in_array($_POST['status'], ['Available', 'Unavailable']) ? $_POST['status'] : 'Active';

    // Validate inputs
    if (empty($name)) $errors[] = 'Tên món ăn không được để trống.';
    if ($price <= 0) $errors[] = 'Giá phải lớn hơn 0.';
    if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục.';

    // Xử lý upload hình ảnh
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 16 * 1024 * 1024; // 16MB
        $file_name = uniqid('dish_') . '_' . basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        $web_file_path = $web_path . $file_name;

        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = 'Chỉ chấp nhận file JPEG, PNG, GIF hoặc WebP.';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = 'Kích thước file không được vượt quá 16MB.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Lỗi upload file.';
        } elseif (!is_writable($upload_dir)) {
            $errors[] = 'Thư mục lưu ảnh không có quyền ghi.';
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $errors[] = 'Lỗi lưu file ảnh.';
        } else {
            $image_path = $web_file_path;
        }
    }

    if (empty($errors)) {
        $pdo = Database::getInstance();
        if ($dish_id > 0) {
            // Cập nhật món ăn
            $query = 'UPDATE dishes SET name = ?, price = ?, description = ?, category_id = ?, status = ?, updated_at = NOW()';
            $params = [$name, $price, $description, $category_id, $status];
            if ($image_path !== null) {
                $query .= ', image = ?';
                $params[] = $image_path;
            }
            $query .= ' WHERE id = ? AND deleted_at IS NULL';
            $params[] = $dish_id;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $success = 'Cập nhật món ăn thành công!';
        } else {
            // Thêm món mới
            $query = 'INSERT INTO dishes (name, price, description, category_id, image, status, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())';
            $params = [$name, $price, $description, $category_id, $image_path, $status];
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $success = 'Thêm món ăn thành công!';
        }

        $response['success'] = true;
        $response['message'] = $success;
    } else {
        $response['errors'] = $errors;
    }
} catch (Exception $e) {
    $response['errors'] = ['Lỗi hệ thống: ' . $e->getMessage()];
    error_log("AJAX error: " . $e->getMessage());
}

echo json_encode($response);
?>