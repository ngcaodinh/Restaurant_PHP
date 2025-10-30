<?php

namespace App\Controllers;

use Core\BaseController;

class PurchaseHistory extends BaseController
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login page if user is not logged in
            header('Location: /login');
            exit;
        }

        $orders = [];
        $error = null;

        try {
            $db = \Database::getInstance();
            $purchaseHistoryModel = new \App\Models\PurchaseHistoryModel($db);
            $userId = $_SESSION['user_id'];
            $orders = $purchaseHistoryModel->getPurchaseHistoryByUserId($userId);
        } catch (\Exception $e) {
            error_log('Error fetching purchase history: ' . $e->getMessage());
            $error = 'Đã có lỗi xảy ra khi tải lịch sử mua hàng. Vui lòng thử lại sau.';
        }

        $data = [
            'title' => 'Lịch sử mua hàng',
            'orders' => $orders,
            'error' => $error
        ];

        $this->view('purchase_history', $data);
    }
}
