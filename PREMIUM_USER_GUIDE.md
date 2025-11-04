# Hướng dẫn sử dụng chức năng Premium User

## 📋 Tổng quan

Chức năng Premium User đã được tạo thành công! Đây là một hệ thống quản lý giống như Admin nhưng **không có quyền quản lý người dùng**.

## 🎯 Các chức năng chính

### 1. Dashboard
- Xem tổng quan về:
  - Tổng số món ăn
  - Tổng số đơn hàng
  - Tổng doanh thu
  - Biểu đồ doanh thu theo tháng
  - Biểu đồ trạng thái đơn hàng
  - Danh sách đơn hàng gần đây

### 2. Quản lý món ăn
- Xem danh sách tất cả món ăn
- Thêm món ăn mới
- Chỉnh sửa thông tin món ăn
- Xóa món ăn
- Tìm kiếm và lọc món ăn theo danh mục

### 3. Quản lý đơn hàng
- Xem danh sách tất cả đơn hàng
- Xem chi tiết từng đơn hàng
- Cập nhật trạng thái đơn hàng:
  - Pending (Chờ xác nhận)
  - Confirmed (Đã xác nhận)
  - Preparing (Đang chuẩn bị)
  - Ready (Sẵn sàng)
  - Delivered (Đã giao)
  - Cancelled (Đã hủy)

## 🚀 Cách sử dụng

### Bước 1: Đăng nhập
1. Truy cập trang đăng nhập: `http://localhost/Restaurant_PHP/login`
2. Đăng nhập với tài khoản Premium User:
   - Email: `PremiumUser@gmail.com`
   - Password: (kiểm tra trong database hoặc đặt lại)

### Bước 2: Truy cập Dashboard
Sau khi đăng nhập thành công, bạn sẽ được tự động chuyển đến:
```
http://localhost/Restaurant_PHP/premium/dashboard
```

### Bước 3: Sử dụng các chức năng

#### Quản lý món ăn
1. Click vào "Quản lý món ăn" trong sidebar
2. Để thêm món mới: Click nút "Thêm món mới"
3. Để chỉnh sửa: Click icon edit ở mỗi món
4. Để xóa: Click icon delete ở mỗi món

#### Quản lý đơn hàng
1. Click vào "Quản lý đơn hàng" trong sidebar
2. Xem danh sách đơn hàng
3. Click vào đơn hàng để xem chi tiết
4. Cập nhật trạng thái đơn hàng bằng dropdown

## 📁 Cấu trúc file đã tạo

```
Restaurant_PHP/
├── app/
│   ├── controllers/
│   │   └── PremiumController.php          # Controller xử lý logic
│   └── views/
│       └── premium/
│           ├── dashboard.php              # Trang dashboard
│           ├── sidebar.php                # Thanh điều hướng
│           ├── manage_dishes.php          # Quản lý món ăn
│           ├── orders.php                 # Quản lý đơn hàng
│           └── README.md                  # Tài liệu chi tiết
├── index.php                              # Đã thêm routes cho premium
└── PREMIUM_USER_GUIDE.md                  # File này
```

## 🔗 Các URL quan trọng

### Dashboard
- `/premium` hoặc `/premium/dashboard`

### Quản lý món ăn
- `/premium/dishes` - Danh sách món ăn
- `/api/premium/dish/create` - API tạo món (POST)
- `/api/premium/dish/update` - API cập nhật món (POST)
- `/api/premium/dish/delete` - API xóa món (POST)

### Quản lý đơn hàng
- `/premium/orders` - Danh sách đơn hàng
- `/premium/order/{id}` - Chi tiết đơn hàng
- `/api/premium/order/update-status` - API cập nhật trạng thái (POST)

## 🔐 Bảo mật

- Tất cả các route premium đều được bảo vệ bởi `checkPremiumAuth()`
- Chỉ user có role = 'PremiumUser' mới truy cập được
- Nếu không có quyền, sẽ chuyển về trang chủ với thông báo lỗi

## 🎨 Giao diện

- Sử dụng Material Dashboard template
- Màu sắc và layout giống Admin
- Responsive, hoạt động tốt trên mobile
- Có biểu đồ tương tác (Chart.js)

## 📊 So sánh với Admin

| Tính năng | Admin | Premium User |
|-----------|-------|--------------|
| Dashboard | ✅ Đầy đủ | ✅ Không có thống kê user |
| Quản lý người dùng | ✅ | ❌ |
| Quản lý món ăn | ✅ | ✅ |
| Quản lý đơn hàng | ✅ | ✅ |
| Xem báo cáo | ✅ | ✅ |

## 🧪 Test tài khoản

Trong database có sẵn tài khoản Premium User:
```sql
Email: PremiumUser@gmail.com
Role: PremiumUser
Status: Active
```

Để đặt lại mật khẩu (nếu cần):
```sql
UPDATE users 
SET password = '$2y$10$nb785C5CpejgMKnaBBsrt.hnE345zn4tFvGb7G5S0/aGloItfOsf2' 
WHERE email = 'PremiumUser@gmail.com';
-- Password: PremiumUser123
```

## [object Object]eshooting

### Lỗi 404 khi truy cập /premium
- Kiểm tra file `.htaccess` có đúng không
- Kiểm tra Apache mod_rewrite đã bật chưa

### Không thể đăng nhập
- Kiểm tra role trong database phải là 'PremiumUser'
- Kiểm tra status phải là 'Active'

### Không thấy menu Premium trong header
- Đảm bảo đã đăng nhập với tài khoản PremiumUser
- Kiểm tra session có lưu đúng role không

## [object Object]hi chú

- Code đã được tổ chức theo mô hình MVC
- Sử dụng namespace và autoloading
- Tương thích với cấu trúc hiện tại của dự án
- Dễ dàng mở rộng thêm chức năng trong tương lai

## 🔄 Cập nhật trong tương lai

Có thể thêm các chức năng:
- Quản lý danh mục món ăn
- Báo cáo chi tiết hơn
- Xuất báo cáo PDF/Excel
- Quản lý khuyến mãi
- Thông báo real-time

## 📞 Hỗ trợ

Nếu có vấn đề, kiểm tra:
1. File log của Apache/PHP
2. Console trong trình duyệt (F12)
3. Database connection
4. Session configuration

---

**Chúc bạn sử dụng thành công! [object Object]

