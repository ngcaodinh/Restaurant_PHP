<?php

/**
 * Tệp xử lý callback từ Google OAuth 2.0
 *
 * Tệp này nhận mã ủy quyền từ Google sau khi người dùng đồng ý cấp quyền,
 * sau đó dùng mã này để lấy access token và thông tin người dùng.
 * Cuối cùng, tệp sẽ đồng bộ hóa thông tin người dùng với cơ sở dữ liệu và đăng nhập cho người dùng.
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tải các tệp cần thiết
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Cấu hình Google OAuth 2.0 (Nên được lưu trong file .env)
$client_id = '490954709937-j4gohbodbtb7kg2215oe63fbe21cn8oi.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://localhost/Restaurant_PHP/callback.php';
$token_uri = 'https://oauth2.googleapis.com/token';
$userinfo_uri = 'https://www.googleapis.com/oauth2/v3/userinfo';

$errors = [];

// === BƯỚC 1: XỬ LÝ PHẢN HỒI BAN ĐẦU TỪ GOOGLE ===

// Kiểm tra nếu Google trả về lỗi
if (isset($_GET['error'])) {
    $errors[] = 'Lỗi từ Google: ' . htmlspecialchars($_GET['error']) . '. Vui lòng thử lại.';
    error_log("Google OAuth error: " . $_GET['error']);
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

// Kiểm tra nếu không nhận được mã ủy quyền (code)
if (!isset($_GET['code'])) {
    $errors[] = 'Không nhận được mã ủy quyền từ Google. Vui lòng thử lại.';
    error_log("No code received in callback. URL: " . $_SERVER['REQUEST_URI']);
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

// === BƯỚC 2: DÙNG MÃ ỦY QUYỀN ĐỂ LẤY ACCESS TOKEN ===

// Khởi tạo cURL để gửi yêu cầu POST đến Google
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_uri);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code' // Loại yêu cầu là lấy token từ code
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Trả về kết quả dưới dạng chuỗi
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Kiểm tra kết quả trả về từ cURL
if ($http_code !== 200 || !$response) {
    $errors[] = 'Không thể lấy access token. Vui lòng thử lại.';
    error_log("Token request failed: HTTP $http_code, Response: " . ($response ?: 'No response'));
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

// Giải mã JSON response để lấy access token
$token_data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($token_data['access_token'])) {
    $errors[] = 'Dữ liệu token không hợp lệ. Vui lòng thử lại.';
    error_log("Token parse error: " . json_last_error_msg() . ", Response: $response");
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$access_token = $token_data['access_token'];

// === BƯỚC 3: DÙNG ACCESS TOKEN ĐỂ LẤY THÔNG TIN NGƯỜI DÙNG ===

// Gửi yêu cầu GET đến Google UserInfo API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userinfo_uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Kiểm tra kết quả trả về
if ($http_code !== 200 || !$user_response) {
    $errors[] = 'Không thể lấy thông tin người dùng. Vui lòng thử lại.';
    error_log("Userinfo request failed: HTTP $http_code, Response: " . ($user_response ?: 'No response'));
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

// Giải mã dữ liệu người dùng từ JSON
$user_data = json_decode($user_response, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($user_data['sub'], $user_data['email'], $user_data['name'])) {
    $errors[] = 'Dữ liệu người dùng không hợp lệ. Vui lòng thử lại.';
    error_log("Userinfo parse error: " . json_last_error_msg() . ", Response: $user_response");
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

// Lấy thông tin cần thiết
$google_id = $user_data['sub']; // ID duy nhất của người dùng Google
$email = strtolower($user_data['email']);
$name = $user_data['name'];

// === BƯỚC 4: ĐỒNG BỘ TÀI KHOẢN VÀ ĐĂNG NHẬP ===
try {
    // Tìm người dùng trong database bằng email
    $query = 'SELECT id, name, email, google_id, role FROM users
              WHERE LOWER(email) = ? AND status = "Active" AND deleted_at IS NULL';
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("User lookup: Email=$email, User=" . print_r($user, true));

    if ($user) {
        // Trường hợp 1: Email đã tồn tại trong hệ thống
        // Cập nhật google_id nếu chưa có và cập nhật thời gian đăng nhập cuối
        if (empty($user['google_id'])) {
            $stmt = $pdo->prepare('UPDATE users SET google_id = ?, last_login = NOW() WHERE id = ?');
            $stmt->execute([$google_id, $user['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);
        }
        // Tạo session đăng nhập cho người dùng
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
    } else {
        // Trường hợp 2: Email chưa tồn tại -> Tạo tài khoản mới
        $stmt = $pdo->prepare('INSERT INTO users (name, email, google_id, role, status, last_login)
                              VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $email, $google_id, 'User', 'Active']);
        $user_id = $pdo->lastInsertId();

        // Tạo session đăng nhập cho người dùng mới
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'User';
    }

    // Chuyển hướng về trang chủ sau khi đăng nhập thành công
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    // Xử lý lỗi nếu có vấn đề với database
    $errors[] = 'Lỗi hệ thống: Không thể đăng nhập. Vui lòng thử lại.';
    error_log("Database error: " . $e->getMessage());
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}
