<?php
require_once __DIR__ . '/../core/bootstrap.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện hành động này.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

$response = ['success' => false, 'message' => 'Đã có lỗi xảy ra.'];

if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'D:/CODE/Project/image_retaurant_php/'; // Make sure this path is correct and writable
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $response['message'] = 'Không thể tạo thư mục tải lên.';
            echo json_encode($response);
            exit();
        }
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if ($_FILES['dish_image']['size'] > 5 * 1024 * 1024) { // 5MB limit
        $response['message'] = 'Kích thước ảnh không được vượt quá 5MB.';
    } elseif (!in_array($_FILES['dish_image']['type'], $allowedTypes)) {
        $response['message'] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF.';
    } else {
        $fileExtension = pathinfo($_FILES['dish_image']['name'], PATHINFO_EXTENSION);
        $newFileName = 'dish_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['dish_image']['tmp_name'], $uploadPath)) {
            $imageUrl = '/image_retaurant_php/' . $newFileName; // Root-relative path to be stored in DB
            $response = [
                'success' => true,
                'message' => 'Tải ảnh lên thành công!',
                'image_url' => $imageUrl
            ];
        } else {
            $response['message'] = 'Không thể tải lên ảnh.';
        }
    }
} else {
    $response['message'] = 'Không có file nào được tải lên hoặc có lỗi xảy ra.';
}

echo json_encode($response);

