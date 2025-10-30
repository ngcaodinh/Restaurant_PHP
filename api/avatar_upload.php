<?php
require_once __DIR__ . '/../core/bootstrap.php';

use App\Models\User;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện hành động này.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit();
}

$userId = $_SESSION['user_id'];
$userModel = new User(Database::getInstance());
$user = $userModel->findById($userId);
$response = ['success' => false, 'message' => 'Đã có lỗi xảy ra.'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'D:/CODE/Project/image_retaurant_php/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) { // 2MB limit
        $response['message'] = 'Kích thước ảnh không được vượt quá 2MB.';
    } elseif (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
        $response['message'] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF.';
    } else {
        $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            // Delete old avatar if it exists
            if (!empty($user['avatar_url']) && file_exists(ltrim($user['avatar_url'], '/'))) {
                unlink(ltrim($user['avatar_url'], '/'));
            }

            $avatarUrl = '/image_retaurant_php/' . $newFileName;
            if ($userModel->update($userId, ['avatar_url' => $avatarUrl])) {
                $_SESSION['user_avatar'] = $avatarUrl;
                $response = [
                    'success' => true,
                    'message' => 'Cập nhật ảnh đại diện thành công!',
                    'avatar_url' => $avatarUrl // Send the root-relative path
                ];
            } else {
                $response['message'] = 'Không thể cập nhật cơ sở dữ liệu.';
            }
        } else {
            $response['message'] = 'Không thể tải lên ảnh đại diện.';
        }
    }
} else {
    $response['message'] = 'Không có file nào được tải lên hoặc có lỗi xảy ra.';
}

echo json_encode($response);
