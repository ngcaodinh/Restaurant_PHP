<!-- Header -->
<header>
    <div class="header-container">
        <div class="logo">🍜 CTUT Restaurant</div>
        <nav>
            <ul>
                <li><a href="#home">Trang chủ</a></li>
                <li><a href="#menu">Menu</a></li>
                <li><a href="#tables">Đặt bàn</a></li>
                <li><a href="#contact">Liên hệ</a></li>
                <li><a href="#login">Đăng nhập</a></li>
            </ul>
            <div class="nav-icons">
                <div class="nav-icon" onclick="toggleWishlist()">
                    <i class="fas fa-heart"></i>
                    <span class="cart-count" id="wishlist-count">0</span>
                </div>
                <div class="nav-icon" onclick="toggleCart()">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </div>
                <div class="nav-icon" onclick="login()">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Content will be injected here by specific views -->

<!-- Footer -->
<footer id="contact">
    <div class="footer-content">
        <div class="footer-info">
            <h3>🍜 CTUT Restaurant</h3>
            <p>📍 123 Đường ABC, Quận Ninh Kiều, TP. Cần Thơ</p>
            <p>📧 contact@ctutrestaurant.com</p>
            <p>📞 Hotline: 0123 456 789</p>
            <p>🕒 Mở cửa: 6:00 - 22:00 (Thứ 2 - Chủ nhật)</p>
        </div>
        <div style="margin-top: 20px;">
            <p>© 2024 CTUT Restaurant. Món ngon dành cho sinh viên!</p>
        </div>
    </div>
</footer>

<!-- Scroll to top button -->
<button class="scroll-top" id="scrollTop">
    <i class="fas fa-arrow-up"></i>
</button>