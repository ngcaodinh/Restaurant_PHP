# Premium User Dashboard

## Giới thiệu

Chức năng Premium User cung cấp một giao diện quản lý tương tự như Admin nhưng **không có quyền quản lý người dùng**. Premium User có thể:

- ✅ Xem dashboard với thống kê tổng quan
- ✅ Quản lý món ăn (thêm, sửa, xóa)
- ✅ Quản lý đơn hàng (xem, cập nhật trạng thái)
- ❌ **KHÔNG** có quyền quản lý người dùng

## Cấu trúc File

```
app/
├── controllers/
│   └── PremiumController.php      # Controller xử lý logic cho Premium User
└── views/
    └── premium/
        ├── dashboard.php          # Trang dashboard (không có thống kê người dùng)
        ├── sidebar.php            # Sidebar (không có menu quản lý người dùng)
        ├── manage_dishes.php      # Trang quản lý món ăn
        ├── orders.php             # Trang quản lý đơn hàng
        └── README.md              # File này
```

## Routes

### Dashboard
- `GET /premium` - Trang dashboard chính
- `GET /premium/dashboard` - Trang dashboard chính (alias)

### Quản lý món ăn
- `GET /premium/dishes` - Danh sách món ăn
- `POST /api/premium/dish/create` - Tạo món ăn mới
- `POST /api/premium/dish/update` - Cập nhật món ăn
- `POST /api/premium/dish/delete` - Xóa món ăn

### Quản lý đơn hàng
- `GET /premium/orders` - Danh sách đơn hàng
- `GET /premium/order/{id}` - Chi tiết đơn hàng
- `POST /api/premium/order/update-status` - Cập nhật trạng thái đơn hàng

## Đăng nhập

Premium User đăng nhập qua trang login thông thường (`/login`). Sau khi đăng nhập thành công, hệ thống sẽ tự động chuyển hướng đến:
- Admin → `/admin/dashboard`
- PremiumUser → `/premium/dashboard`
- User → `/` (trang chủ)

## Tài khoản Test

Theo database hiện tại, có sẵn tài khoản Premium User:
- Email: `PremiumUser@gmail.com`
- Password: (cần kiểm tra trong database hoặc đặt lại)

## Khác biệt giữa Admin và Premium User

| Chức năng | Admin | Premium User |
|-----------|-------|--------------|
| Dashboard với thống kê | ✅ Đầy đủ (bao gồm user stats) | ✅ Giới hạn (không có user stats) |
| Quản lý người dùng | ✅ | ❌ |
| Quản lý món ăn | ✅ | ✅ |
| Quản lý đơn hàng | ✅ | ✅ |
| Xem báo cáo doanh thu | ✅ | ✅ |

## Bảo mật

PremiumController có phương thức `checkPremiumAuth()` để đảm bảo:
1. Người dùng đã đăng nhập
2. Role của người dùng là 'PremiumUser'
3. Nếu không đủ điều kiện, chuyển hướng về trang chủ với thông báo lỗi

## Giao diện

Giao diện Premium User sử dụng:
- Material Dashboard template (giống Admin)
- Màu sắc và layout tương tự Admin
- Sidebar riêng không có menu "Quản lý người dùng"

## Cập nhật trong tương lai

Có thể mở rộng thêm các chức năng cho Premium User như:
- Quản lý danh mục món ăn
- Xem báo cáo chi tiết hơn
- Quản lý khuyến mãi
- Và nhiều tính năng khác...

