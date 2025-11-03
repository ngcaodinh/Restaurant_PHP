<?php

/**
 * Tệp chứa các hàm tiện ích chung
 *
 * Tệp này chứa các hàm helper được sử dụng trong toàn bộ ứng dụng.
 * Bao gồm các hàm xử lý dữ liệu, định dạng, và các tiện ích khác.
 */

/**
 * Làm sạch dữ liệu đầu vào từ người dùng
 *
 * Hàm này loại bỏ khoảng trắng thừa, các thẻ HTML và ký tự xuống dòng
 * để bảo vệ ứng dụng khỏi các cuộc tấn công XSS.
 *
 * @param mixed $input Dữ liệu đầu vào cần làm sạch
 * @param bool $preserve_newlines Có giữ lại ký tự xuống dòng hay không (mặc định: false)
 * @return string|null Chuỗi đã được làm sạch, hoặc null nếu input không phải chuỗi
 */
function sanitize_input($input, $preserve_newlines = false)
{
    // Chỉ xử lý nếu input là chuỗi
    if (!is_string($input)) {
        return null;
    }

    // Loại bỏ khoảng trắng ở đầu và cuối
    $input = trim($input);

    // Loại bỏ ký tự xuống dòng nếu không cần giữ lại
    if (!$preserve_newlines) {
        $input = preg_replace("/[\r\n]+/", ' ', $input);
    }

    // Loại bỏ tất cả các thẻ HTML
    $input = strip_tags($input);

    return $input;
}

/**
 * Định dạng số tiền theo chuẩn Việt Nam
 *
 * Hàm này chuyển đổi số tiền thành chuỗi có định dạng VNĐ.
 * Ví dụ: 1000000 -> "1.000.000 VNĐ"
 *
 * @param float|int $amount Số tiền cần định dạng
 * @return string Chuỗi tiền tệ đã được định dạng
 */
function format_currency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

/**
 * Chuyển hướng người dùng đến một URL khác
 *
 * Hàm này gửi header Location để chuyển hướng trình duyệt
 * và dừng thực thi script hiện tại.
 *
 * @param string $url URL đích cần chuyển hướng đến
 * @return void
 */
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Định dạng ngày tháng theo chuẩn Việt Nam
 *
 * Hàm này chuyển đổi chuỗi ngày tháng thành định dạng dd/mm/yyyy.
 * Ví dụ: "2024-01-15" -> "15/01/2024"
 *
 * @param string $date Chuỗi ngày tháng cần định dạng
 * @return string Chuỗi ngày tháng đã được định dạng
 */
function format_date($date)
{
    return date('d/m/Y', strtotime($date));
}

/**
 * Yêu cầu người dùng phải đăng nhập
 *
 * Hàm này kiểm tra xem người dùng đã đăng nhập chưa.
 * Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập.
 *
 * @return void
 */
function require_login()
{
    // Kiểm tra session user_id
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = 'Vui lòng đăng nhập để truy cập.';
        header('Location: /login');
        exit;
    }
}

/**
 * Lấy ID của người dùng hiện tại
 *
 * @return int|null ID người dùng nếu đã đăng nhập, null nếu chưa
 */
function get_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Lấy tên của người dùng hiện tại
 *
 * @return string|null Tên người dùng nếu đã đăng nhập, null nếu chưa
 */
function get_user_name()
{
    return $_SESSION['user_name'] ?? null;
}
