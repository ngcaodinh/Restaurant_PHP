# Hệ thống Quản lý Nhà hàng CTUT Restaurant

Đây là một dự án website quản lý và đặt món ăn cho nhà hàng, được xây dựng trên nền tảng PHP thuần theo mô hình MVC (Model-View-Controller). Dự án cung cấp một giao diện hiện đại, thân thiện cho người dùng và một trang quản trị mạnh mẽ cho quản trị viên.

## ✨ Tính năng chính

### Dành cho Khách hàng

- **Xác thực người dùng**: Đăng ký, đăng nhập tài khoản (thông thường và qua Google).
- **Quản lý tài khoản**: Cập nhật thông tin cá nhân, thay đổi mật khẩu, và chức năng "Quên mật khẩu".
- **Trải nghiệm mua sắm**: 
    - Xem thực đơn chi tiết, tìm kiếm món ăn.
    - Thêm món ăn vào giỏ hàng, cập nhật số lượng hoặc xóa món ăn.
    - Thêm món ăn vào danh sách yêu thích.
- **Thanh toán**: Giao diện thanh toán trực quan, cho phép người dùng nhập thông tin giao hàng và xác nhận đơn hàng.
- **Lịch sử mua hàng**: Xem lại danh sách các đơn hàng đã đặt và chi tiết của từng đơn.

### Dành cho Quản trị viên (Admin)

- **Bảng điều khiển (Dashboard)**: Cung cấp cái nhìn tổng quan về hoạt động của nhà hàng.
- **Quản lý Người dùng**: Xem danh sách, chỉnh sửa thông tin, thay đổi vai trò, và xóa người dùng.
- **Quản lý Món ăn**: Thêm, sửa, xóa món ăn, cập nhật hình ảnh và thông tin chi tiết.
- **Quản lý Đơn hàng**: Theo dõi trạng thái các đơn hàng, xem chi tiết và cập nhật trạng thái (ví dụ: đang xử lý, đã giao, đã hủy).

## 🚀 Công nghệ sử dụng

- **Backend**: PHP 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL (kết nối thông qua PDO)
- **Web Server**: Apache (khuyến nghị sử dụng XAMPP)

## ⚙️ Hướng dẫn cài đặt

Để chạy dự án trên máy cục bộ của bạn, hãy làm theo các bước sau:

1.  **Clone Repository**:
    ```bash
    git clone <URL_CUA_REPOSITORY>
    ```

2.  **Di chuyển vào thư mục `htdocs`**:
    - Sao chép toàn bộ thư mục dự án vào thư mục `htdocs` của XAMPP (ví dụ: `D:/xampp/htdocs/Restaurant_PHP`).

3.  **Khởi động XAMPP**:
    - Mở XAMPP Control Panel và khởi động module **Apache** và **MySQL**.

4.  **Tạo Database**:
    - Truy cập `http://localhost/phpmyadmin`.
    - Tạo một database mới với tên là `restaurant_ctut` (khuyến nghị sử dụng bảng mã `utf8mb4_unicode_ci`).

5.  **Import Database**:
    - Chọn database `restaurant_ctut` vừa tạo.
    - Nhấn vào tab `Import`.
    - Chọn tệp `config/restaurant_ctut.sql` từ thư mục dự án và nhấn `Go` (hoặc `Import`).

6.  **Cấu hình kết nối Database**:
    - Mở file `config/db.php`.
    - Cập nhật các thông tin `DB_HOST`, `DB_NAME`, `DB_USER`, và `DB_PASS` cho phù hợp với môi trường của bạn (thường thì cấu hình mặc định của XAMPP là đủ).

7.  **Cấu hình URL gốc**:
    - Mở file `config/settings.php`.
    - Đảm bảo hằng số `BASE_URL` được định nghĩa chính xác. Ví dụ:
      ```php
      define('BASE_URL', '/Restaurant_PHP/');
      ```

8.  **Truy cập dự án**:
    - Mở trình duyệt và truy cập vào địa chỉ: `http://localhost/Restaurant_PHP/`.

## 📂 Cấu trúc thư mục

Dự án được tổ chức theo mô hình MVC:

```
/Restaurant_PHP
├── app/
│   ├── controllers/  # Xử lý logic và request
│   ├── models/       # Tương tác với database
│   └── views/        # Hiển thị giao diện người dùng
├── assets/
│   ├── css/          # Chứa các file CSS
│   ├── js/           # Chứa các file JavaScript
│   └── images/       # Chứa hình ảnh
├── config/           # Chứa các file cấu hình (db, settings)
├── core/             # Chứa các lớp lõi của framework (Router, Controller, Autoloader)
├── templates/        # Chứa các template tái sử dụng (header, footer)
├── .htaccess         # Cấu hình rewrite URL cho Apache
└── index.php         # Front Controller (điểm vào duy nhất)
```

---
*Dự án được xây dựng với mục tiêu học tập và thực hành kỹ năng lập trình PHP theo mô hình MVC hiện đại.*

