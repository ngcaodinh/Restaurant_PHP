<?php
// Hàm làm sạch dữ liệu đầu vào
function sanitize_input($input, $preserve_newlines = false)
{
    if (!is_string($input)) {
        return null;
    }
    $input = trim($input);
    if (!$preserve_newlines) {
        $input = preg_replace("/[\r\n]+/", ' ', $input);
    }
    $input = strip_tags($input);
    return $input;
}

// Hàm định dạng tiền tệ
function format_currency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

// Hàm chuyển hướng
function redirect($url)
{
    header("Location: $url");
    exit;
}
// Hàm định dạng ngày tháng
function format_date($date)
{
    return date('d/m/Y', strtotime($date));
}

// Helper functions for MVC compatibility
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = 'Vui lòng đăng nhập để truy cập.';
        header('Location: /login');
        exit;
    }
}

function get_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function get_user_name()
{
    return $_SESSION['user_name'] ?? null;
}
