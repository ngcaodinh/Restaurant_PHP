<?php
/**
 * Trang test chức năng Premium User
 * Truy cập: http://localhost/Restaurant_PHP/test_premium.php
 */

require_once __DIR__ . '/core/bootstrap.php';

// Kiểm tra kết nối database
try {
    $db = Database::getInstance();
    $dbStatus = "✅ Kết nối database thành công";
} catch (Exception $e) {
    $dbStatus = "❌ Lỗi kết nối database: " . $e->getMessage();
}

// Lấy thông tin Premium User từ database
$premiumUsers = [];
try {
    $stmt = $db->query("SELECT id, email, name, role, status, last_login FROM users WHERE role = 'PremiumUser'");
    $premiumUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $premiumUsersError = $e->getMessage();
}

// Kiểm tra routes
$routes = [
    'Dashboard' => BASE_URL . 'premium/dashboard',
    'Quản lý món ăn' => BASE_URL . 'premium/dishes',
    'Quản lý đơn hàng' => BASE_URL . 'premium/orders',
];

// Kiểm tra files
$files = [
    'Controller' => 'app/controllers/PremiumController.php',
    'Dashboard View' => 'app/views/premium/dashboard.php',
    'Sidebar View' => 'app/views/premium/sidebar.php',
    'Dishes View' => 'app/views/premium/manage_dishes.php',
    'Orders View' => 'app/views/premium/orders.php',
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Premium User - CTUT Restaurant</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .status {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        
        table tr:hover {
            background: #f5f5f5;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .check {
            color: #28a745;
            font-weight: bold;
        }
        
        .cross {
            color: #dc3545;
            font-weight: bold;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-box p {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>[object Object]</h1>
            <p>Kiểm tra chức năng Premium User cho CTUT Restaurant</p>
        </div>

        <!-- Database Status -->
        <div class="card">
            <h2>📊 Trạng thái hệ thống</h2>
            <div class="status <?php echo strpos($dbStatus, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $dbStatus; ?>
            </div>
        </div>

        <!-- Premium Users -->
        <div class="card">
            <h2>👥 Tài khoản Premium User</h2>
            <?php if (!empty($premiumUsers)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Tên</th>
                            <th>Trạng thái</th>
                            <th>Đăng nhập lần cuối</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($premiumUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td>
                                    <span class="<?php echo $user['status'] === 'Active' ? 'check' : 'cross'; ?>">
                                        <?php echo $user['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['last_login'] ?? 'Chưa đăng nhập'; ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>login" class="btn btn-success">Đăng nhập</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="status error">
                    ❌ Không tìm thấy tài khoản Premium User nào!
                    <br><br>
                    <strong>Hướng dẫn:</strong> Chạy file <code>config/create_premium_user.sql</code> để tạo tài khoản.
                </div>
            <?php endif; ?>
        </div>

        <!-- Routes -->
        <div class="card">
            <h2>🔗 Các route Premium User</h2>
            <div class="info-grid">
                <?php foreach ($routes as $name => $url): ?>
                    <div class="info-box">
                        <h3><?php echo $name; ?></h3>
                        <p><a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Files Check -->
        <div class="card">
            <h2>📁 Kiểm tra files</h2>
            <table>
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Đường dẫn</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $name => $path): ?>
                        <tr>
                            <td><?php echo $name; ?></td>
                            <td><code><?php echo $path; ?></code></td>
                            <td>
                                <?php if (file_exists($path)): ?>
                                    <span class="check">✅ Tồn tại</span>
                                <?php else: ?>
                                    <span class="cross">❌ Không tìm thấy</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Instructions -->
        <div class="card">
            <h2>📖 Hướng dẫn sử dụng</h2>
            <ol style="line-height: 2; color: #666;">
                <li>Đảm bảo có tài khoản Premium User trong database (xem bảng trên)</li>
                <li>Đăng nhập với tài khoản Premium User tại <a href="<?php echo BASE_URL; ?>login"><?php echo BASE_URL; ?>login</a></li>
                <li>Sau khi đăng nhập, bạn sẽ được chuyển đến <strong>Premium Dashboard</strong></li>
                <li>Khám phá các chức năng: Dashboard, Quản lý món ăn, Quản lý đơn hàng</li>
            </ol>
            
            <div style="margin-top: 20px;">
                <a href="<?php echo BASE_URL; ?>login" class="btn btn-success">🚀 Đăng nhập ngay</a>
                <a href="<?php echo BASE_URL; ?>" class="btn">🏠 Về trang chủ</a>
                <a href="<?php echo BASE_URL; ?>PREMIUM_USER_GUIDE.md" class="btn">📚 Xem hướng dẫn</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="card" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <p style="margin: 0;">
                <strong>Premium User System</strong> - Created for CTUT Restaurant
            </p>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">
                © 2025 - All rights reserved
            </p>
        </div>
    </div>
</body>
</html>

