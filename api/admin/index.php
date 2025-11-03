<?php
require_once __DIR__ . '/../../core/bootstrap.php';

use App\Controllers\AdminController;

// Basic routing
$action = $_GET['action'] ?? '';

$adminController = new AdminController();

switch ($action) {
    case 'dish/create':
        $adminController->createDish();
        break;
    case 'dish/update':
        $adminController->updateDish();
        break;
    case 'dish/delete':
        // This action is intentionally disabled as per requirements.
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Chức năng xóa đã bị vô hiệu hóa.']);
        break;
    default:
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API endpoint không tồn tại.']);
        break;
}

