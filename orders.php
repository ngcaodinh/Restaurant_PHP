<?php
require_once 'includes/auth.php';
check_permission(['Admin', 'User', 'PremiumUser']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CTUT Restaurant - Đơn hàng</title>
    <link rel="stylesheet" href="/Restaurant_PHP/assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <h1>Đơn hàng</h1>
    <p>Xem lịch sử đơn hàng.</p>
</body>
</html>