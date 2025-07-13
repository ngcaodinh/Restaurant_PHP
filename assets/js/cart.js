let cart = [];
let wishlist = [];

function updateCounters() {
    document.getElementById('cart-count').textContent = cart.length;
    document.getElementById('wishlist-count').textContent = wishlist.length;
}

function addToCart(dishId) {
    const dishes = [
        { id: 1, name: 'Cơm Tấm Sườn Nướng', price: '35.000đ', description: 'Cơm tấm thơm ngon với sườn nướng đậm đà.', image: '/assets/images/dishes/com-tam.jpg' },
        { id: 2, name: 'Phở Bò', price: '40.000đ', description: 'Phở bò truyền thống với nước dùng thơm ngon.', image: '/assets/images/dishes/pho-bo.jpg' }
    ];
    const dish = dishes.find(d => d.id === dishId);
    if (dish) {
        cart.push(dish);
        showNotification(`✅ Đã thêm "${dish.name}" vào giỏ hàng!`, 'success');
        updateCounters();
    } else {
        showNotification('⚠️ Món ăn không tồn tại!', 'warning');
    }
}

function addToWishlist(dishId) {
    const dishes = [
        { id: 1, name: 'Cơm Tấm Sườn Nướng', price: '35.000đ', description: 'Cơm tấm thơm ngon với sườn nướng đậm đà.', image: '/assets/images/dishes/com-tam.jpg' },
        { id: 2, name: 'Phở Bò', price: '40.000đ', description: 'Phở bò truyền thống với nước dùng thơm ngon.', image: '/assets/images/dishes/pho-bo.jpg' }
    ];
    const dish = dishes.find(d => d.id === dishId);
    if (dish) {
        wishlist.push(dish);
        showNotification(`❤️ Đã thêm "${dish.name}" vào danh sách yêu thích!`, 'success');
        updateCounters();
    } else {
        showNotification('⚠️ Món ăn không tồn tại!', 'warning');
    }
}

function toggleCart() {
    if (cart.length === 0) {
        showNotification('Giỏ hàng đang trống!', 'info');
    } else {
        showNotification('Chuyển đến trang giỏ hàng...', 'info');
        // Tạm thời chỉ hiển thị thông báo, vì chưa có trang cart.php
    }
}

function toggleWishlist() {
    if (wishlist.length === 0) {
        showNotification('Danh sách yêu thích đang trống!', 'info');
    } else {
        showNotification('Chuyển đến trang danh sách yêu thích...', 'info');
        // Tạm thời chỉ hiển thị thông báo, vì chưa có trang wishlist.php
    }
}

function buyNow(dishId) {
    addToCart(dishId);
    showNotification('Đang chuyển đến trang thanh toán...', 'info');
}