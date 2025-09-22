<?php
require_once 'includes/auth.php';
check_permission(['Admin']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CTUT Restaurant - Quản lý Người dùng</title>
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <h1>Quản lý Người dùng</h1>
    <p>Danh sách người dùng (chỉ Admin).</p>
</body>
</html>