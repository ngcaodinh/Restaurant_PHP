<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Favorite;

class FavoritesController extends BaseController
{
    public function index()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        // Check if user is logged in
        if (!is_logged_in()) {
            header('Location: /login.php');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $favoriteModel = new Favorite();
        $favorite_items = $favoriteModel->getFavoritesByUserId($user_id);

        $this->view('favorites_view', ['favorite_items' => $favorite_items]);
    }
}

