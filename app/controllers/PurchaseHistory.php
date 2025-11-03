<?php

namespace App\Controllers;

use Core\BaseController;

/**
 * Class PurchaseHistory - Controller xử lý trang lịch sử mua hàng
 *
 * Quản lý việc hiển thị lịch sử các đơn hàng đã mua của người dùng.
 */
class PurchaseHistory extends BaseController
{
    /**
     * Hiển thị trang lịch sử mua hàng
     *
     * Lấy danh sách các đơn hàng đã hoàn thành của người dùng và hiển thị ra view.
     * @return void
     */
    public function index()
    {
        // Yêu cầu người dùng phải đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $orders = [];
        $error = null;

        try {
            // Lấy kết nối DB và khởi tạo model
            $db = \Database::getInstance();
            $purchaseHistoryModel = new \App\Models\PurchaseHistoryModel($db);
            $userId = $_SESSION['user_id'];

            // Lấy lịch sử mua hàng từ model
            $orders = $purchaseHistoryModel->getPurchaseHistoryByUserId($userId);
        } catch (\Exception $e) {
            // Xử lý lỗi nếu có
            error_log('Error fetching purchase history: ' . $e->getMessage());
            $error = 'Đã có lỗi xảy ra khi tải lịch sử mua hàng. Vui lòng thử lại sau.';
        }

        // Chuẩn bị dữ liệu để truyền vào view
        $data = [
            'title' => 'Lịch sử mua hàng',
            'orders' => $orders,
            'error' => $error
        ];

        // Hiển thị view
        $this->view('purchase_history', $data);
    }
}
