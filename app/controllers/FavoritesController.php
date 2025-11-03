<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Favorite;

/**
 * Class FavoritesController - Controller xử lý trang món ăn yêu thích
 *
 * Quản lý việc hiển thị danh sách các món ăn yêu thích của người dùng.
 */
class FavoritesController extends BaseController
{
    /**
     * Hiển thị trang danh sách yêu thích
     *
     * Lấy danh sách các món ăn yêu thích của người dùng hiện tại từ database
     * và hiển thị chúng ra view.
     *
     * @return void
     */
    public function index()
    {
        // Đảm bảo session đã được khởi tạo
        if (!isset($_SESSION)) {
            session_start();
        }

        // Yêu cầu người dùng phải đăng nhập
        if (!is_logged_in()) {
            header('Location: /login.php');
            exit();
        }

        // Lấy ID người dùng từ session
        $user_id = $_SESSION['user_id'];

        // Khởi tạo model và lấy danh sách yêu thích
        $favoriteModel = new Favorite();
        $favorite_items = $favoriteModel->getFavoritesByUserId($user_id);

        // Hiển thị view và truyền dữ liệu vào
        $this->view('favorites_view', ['favorite_items' => $favorite_items]);
    }
}
