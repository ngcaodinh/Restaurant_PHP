# CTUT Restaurant 🍽️

**CTUT Restaurant** là ứng dụng web thương mại điện tử nhà hàng được phát triển bởi sinh viên Đại học Công nghệ Cần Thơ. Hệ thống hỗ trợ quản lý món ăn, khách hàng, giỏ hàng và đơn hàng với giao diện thân thiện, tích hợp Google OAuth 2.0 và phân quyền rõ ràng.

## 📌 Mô tả hệ thống

Hệ thống được thiết kế theo mô hình **client-server**, sử dụng:

- **Backend:** PHP thuần, xử lý logic nghiệp vụ và tương tác cơ sở dữ liệu
- **Frontend:** HTML, CSS, JavaScript, responsive với Bootstrap
- **Database:** MySQL, tối ưu với xóa mềm, khóa ngoại 
- **Tích hợp:** Google OAuth 2.0 cho đăng nhập nhanh

## 👤 Nhóm người dùng và chức năng

### 🔧 Admin (Quản trị viên)
- Quản lý khách hàng: thêm, sửa, xóa mềm, lọc, thống kê
- Quản lý món ăn: thêm, sửa, xóa mềm, lọc, báo cáo doanh thu

### 👤 User (Khách hàng đăng nhập)
- Quản lý giỏ hàng: thêm, xóa, cập nhật món
- Thanh toán: xem chi tiết, nhập thông tin giao hàng, chọn COD/Online
- Lọc/tìm kiếm món ăn
- Đăng nhập bằng Google

### 💎 PremiumUser (Khách hàng mua phần mềm)
- Tương tự User
- Tiềm năng mở rộng: ưu tiên đơn hàng, ưu đãi độc quyền

### 🔍 Khách không đăng nhập
- Xem, lọc, tìm kiếm món ăn (không đặt hàng)

## 🏗️ Kiến trúc hệ thống

- **Client:** Trình duyệt hiển thị giao diện, gửi yêu cầu HTTP
- **Server:** PHP xử lý yêu cầu, trả về JSON/HTML
- **Database:** MySQL với 7 bảng:
  - `users`
  - `categories`
  - `dishes`
  - `carts`
  - `cart_items`
  - `orders`
  - `order_items`
- **Phân quyền:** ENUM (`Admin`, `User`, `PremiumUser`)
- **Xóa mềm:** Trường `deleted_at` giữ lịch sử

## 🗂️ Cấu trúc thư mục (MVC)
## ⚙️ Đặc điểm kỹ thuật

### Database
- 7 bảng với khóa ngoại
- Xóa mềm (soft delete)
- Chỉ mục tối ưu
- ENUM cho phân quyền
- `google_id` hỗ trợ đăng nhập Google

### Hiệu suất
- Tối ưu chỉ mục cho các trường truy vấn thường xuyên

### Bảo mật
- Phân quyền
- Mã hóa mật khẩu
- Google OAuth 2.0

### Khả năng mở rộng
- Hỗ trợ tính năng cho PremiumUser
- Phân vùng bảng `orders`

## 🎯 Mục tiêu

- Giao diện thân thiện, dễ sử dụng
- Truy vấn nhanh, dễ quản lý
- Phân quyền rõ ràng, bảo mật cao
- Hạ tầng mở rộng cho các tính năng nâng cao như ưu đãi, thống kê

## 🚀 Cách sử dụng

### Cài đặt

1. **Cấu hình database:**
   ```php
   // config/database.php
   <?php
   return [
       'host' => 'localhost',
       'username' => 'your_username',
       'password' => 'your_password',
       'database' => 'ctut_restaurant'
   ];
   ```

2. **Cấu hình Google OAuth:**
   ```php
   // config/google_oauth.php
   <?php
   return [
       'client_id' => 'your_google_client_id',
       'client_secret' => 'your_google_client_secret',
       'redirect_uri' => 'your_redirect_uri'
   ];
   ```

3. **Import database:**
   ```bash
   mysql -u username -p ctut_restaurant < database.sql
   ```

### Chạy ứng dụng

1. Đặt dự án trong web server (Apache/Nginx)
2. Truy cập qua `index.php`
3. Đảm bảo mod_rewrite được kích hoạt cho Apache

### Quản lý

- **Admin:** `/admin/*` (yêu cầu `AdminMiddleware`)
- **User/PremiumUser:** `/user/*` (yêu cầu `AuthMiddleware`)
- **Guest:** Trang chủ `/`

## 📋 Yêu cầu hệ thống

- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx với mod_rewrite
- Extension: mysqli, json, curl (cho Google OAuth)

## 🔧 Công nghệ sử dụng

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript ES6+, Bootstrap 5
- **Database:** MySQL 5.7+
- **Authentication:** Google OAuth 2.0
- **Architecture:** MVC Pattern

## 📝 License

Dự án được phát triển cho mục đích học tập tại Đại học Công nghệ Cần Thơ.

## 🤝 Đóng góp

Mọi góp ý và đóng góp xin liên hệ qua email hoặc tạo issue trên GitHub.

---

*Phát triển bởi sinh viên Đại học Công nghệ Cần Thơ - CTUT*