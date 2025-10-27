<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$client_id = '490954709937-j4gohbodbtb7kg2215oe63fbe21cn8oi.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://localhost/Restaurant_PHP/callback.php';
$token_uri = 'https://oauth2.googleapis.com/token';
$userinfo_uri = 'https://www.googleapis.com/oauth2/v3/userinfo';

$errors = [];

if (isset($_GET['error'])) {
    $errors[] = 'Lỗi từ Google: ' . htmlspecialchars($_GET['error']) . '. Vui lòng thử lại.';
    error_log("Google OAuth error: " . $_GET['error']);
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

if (!isset($_GET['code'])) {
    $errors[] = 'Không nhận được mã ủy quyền từ Google. Vui lòng thử lại.';
    error_log("No code received in callback. URL: " . $_SERVER['REQUEST_URI']);
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$code = $_GET['code'];

// Lấy access_token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_uri);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    $errors[] = 'Không thể lấy access token. Vui lòng thử lại.';
    error_log("Token request failed: HTTP $http_code, Response: " . ($response ?: 'No response'));
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$token_data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($token_data['access_token'])) {
    $errors[] = 'Dữ liệu token không hợp lệ. Vui lòng thử lại.';
    error_log("Token parse error: " . json_last_error_msg() . ", Response: $response");
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$access_token = $token_data['access_token'];

// Lấy thông tin người dùng
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userinfo_uri);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$user_response) {
    $errors[] = 'Không thể lấy thông tin người dùng. Vui lòng thử lại.';
    error_log("Userinfo request failed: HTTP $http_code, Response: " . ($user_response ?: 'No response'));
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$user_data = json_decode($user_response, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($user_data['sub'], $user_data['email'], $user_data['name'])) {
    $errors[] = 'Dữ liệu người dùng không hợp lệ. Vui lòng thử lại.';
    error_log("Userinfo parse error: " . json_last_error_msg() . ", Response: $user_response");
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$google_id = $user_data['sub'];
$email = strtolower($user_data['email']);
$name = $user_data['name'];

// Đồng bộ tài khoản
try {
    $query = 'SELECT id, name, email, google_id, role FROM users 
              WHERE LOWER(email) = ? AND status = "Active" AND deleted_at IS NULL';
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("User lookup: Email=$email, User=" . print_r($user, true));

    if ($user) {
        // Case 1: Email đã tồn tại
        if (empty($user['google_id'])) {
            $stmt = $pdo->prepare('UPDATE users SET google_id = ?, last_login = NOW() WHERE id = ?');
            $stmt->execute([$google_id, $user['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
    } else {
        // Case 2: Email chưa tồn tại
        $stmt = $pdo->prepare('INSERT INTO users (name, email, google_id, role, status, last_login) 
                              VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $email, $google_id, 'User', 'Active']);
        $user_id = $pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'User';
    }

    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    $errors[] = 'Lỗi hệ thống: Không thể đăng nhập. Vui lòng thử lại.';
    error_log("Database error: " . $e->getMessage());
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}
?>