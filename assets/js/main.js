/**
 * Tệp JavaScript chính cho trang chủ
 *
 * Tệp này chứa các hàm xử lý tương tác người dùng trên trang chủ,
 * bao gồm giỏ hàng, danh sách yêu thích, hiển thị chi tiết sản phẩm, v.v.
 */

// Biến toàn cục lưu trữ giỏ hàng
let cart = [];
// Biến toàn cục lưu trữ danh sách yêu thích
let wishlist = [];
// ID sản phẩm hiện tại đang được xem
let currentProductId = '';

/**
 * Hàm debounce để giảm số lần gọi hàm
 *
 * Hàm này trì hoãn việc thực thi một hàm cho đến khi người dùng ngừng gọi nó
 * trong một khoảng thời gian nhất định. Hữu ích cho các sự kiện như scroll, resize, input.
 *
 * @param {Function} func - Hàm cần debounce
 * @param {number} wait - Thời gian chờ (milliseconds)
 * @return {Function} Hàm đã được debounce
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Cập nhật số lượng hiển thị trên icon giỏ hàng và danh sách yêu thích
 *
 * Hàm này cập nhật số lượng sản phẩm hiển thị trên badge của icon giỏ hàng
 * và danh sách yêu thích ở header.
 *
 * @return {void}
 */
function updateCounters() {
    const cartCount = document.getElementById('cart-count');
    const wishlistCount = document.getElementById('wishlist-count');
    if (cartCount) cartCount.textContent = cart.length;
    if (wishlistCount) wishlistCount.textContent = wishlist.length;
}

/**
 * Gửi yêu cầu AJAX đến server
 *
 * Hàm này gửi yêu cầu POST đến ajax_handler.php để xử lý các action
 * như thêm vào giỏ hàng, thêm vào danh sách yêu thích, v.v.
 *
 * @param {string} action - Tên action cần thực hiện (add_to_cart, add_to_wishlist, etc.)
 * @param {number} dishId - ID của món ăn
 * @param {Function} callback - Hàm callback được gọi khi request thành công
 * @return {void}
 */
function sendAjaxRequest(action, dishId, callback) {
    // Tạo đối tượng XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('POST', BASE_URL + 'ajax_handler.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Xử lý khi nhận được response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    // Parse JSON response
                    const response = JSON.parse(xhr.responseText);
                    if (response.success && callback) {
                        callback(response);
                    } else {
                        // Hiển thị thông báo lỗi
                        showNotification(response.message || 'Vui lòng đăng nhập để tiếp tục', 'error');
                        // Nếu cần đăng nhập, chuyển hướng đến trang login
                        if (response.message.includes('đăng nhập')) {
                            setTimeout(() => {
                                window.location.href = BASE_URL + 'login.php';
                            }, 1000);
                        }
                    }
                } catch (e) {
                    showNotification('⚠️ Lỗi xử lý yêu cầu!', 'error');
                }
            } else {
                showNotification('⚠️ Lỗi kết nối server!', 'error');
            }
        }
    };

    // Xử lý lỗi kết nối
    xhr.onerror = function () {
        showNotification('⚠️ Lỗi kết nối server!', 'error');
    };

    // Gửi request với dữ liệu
    xhr.send(`action=${action}&dish_id=${dishId}`);
}

function addToWishlist(dishId) {
    const dish = products[dishId];
    if (!dish) {
        showNotification('⚠️ Món ăn không tồn tại!', 'error');
        return;
    }
    sendAjaxRequest('add_to_wishlist', dishId, (response) => {
        if (response.success) {
            // Hiển thị thông báo tùy theo action (added hoặc removed)
            if (response.action === 'added') {
                showNotification(`❤️ Đã thêm "${dish.name}" vào danh sách yêu thích!`, 'success');
            } else if (response.action === 'removed') {
                showNotification(`🗑️ Đã xóa "${dish.name}" khỏi danh sách yêu thích!`, 'info');
            }

            // Cập nhật số lượng wishlist từ server
            const wishlistCountEl = document.getElementById('wishlist-count');
            if (wishlistCountEl && response.wishlist_count !== undefined) {
                wishlistCountEl.textContent = response.wishlist_count;
            }
        } else {
            showNotification('Vui lòng đăng nhập để tiếp tục', 'error');
        }
    });
}

function showDetails(dishId) {
    const product = products[dishId];
    if (!product) {
        console.error(`Product with ID ${dishId} not found!`);
        showNotification(`⚠️ Không tìm thấy món ăn!`, 'warning');
        return;
    }

    currentProductId = dishId;
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalPrice = document.getElementById('modalPrice');
    const modalImage = document.getElementById('modalImage');
    const productModal = document.getElementById('productModal');
    const buyNowBtn = document.querySelector('#productModal .btn-buy-now');

    if (modalTitle && modalDescription && modalPrice && modalImage && productModal && buyNowBtn) {
        modalTitle.textContent = product.name;
        modalDescription.textContent = product.description;
        modalPrice.textContent = product.price;
        modalImage.src = product.image;
        // Cập nhật dishId cho nút "Mua ngay" trong modal
        buyNowBtn.setAttribute('onclick', `buyNow(${dishId})`);
        productModal.style.display = 'block';

        const modalImageContainer = modalImage.parentElement;
        modalImageContainer.addEventListener('mousemove', debouncedMouseMove);
        modalImageContainer.addEventListener('mouseleave', resetZoom);
        modalImageContainer.addEventListener('click', () => openZoomModal(dishId));
    } else {
        console.error('Modal elements not found!');
        showNotification(`⚠️ Lỗi hiển thị chi tiết món ăn!`, 'error');
    }
}

function openZoomModal(dishId) {
    const product = products[dishId];
    if (!product) {
        console.error(`Product with ID ${dishId} not found!`);
        showNotification(`⚠️ Không tìm thấy món ăn!`, 'warning');
        return;
    }

    const modalImage = document.getElementById('modalImage');
    const zoomImage = document.getElementById('zoomImage');
    const zoomModal = document.getElementById('zoomModal');
    if (modalImage && zoomImage && zoomModal) {
        zoomImage.src = product.image;
        zoomModal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        const zoomImageContainer = zoomImage.parentElement;
        zoomImageContainer.addEventListener('mousemove', debouncedMouseMove);
        zoomImageContainer.addEventListener('mouseleave', resetZoom);
        zoomImageContainer.addEventListener('touchmove', handleTouchMove);
        zoomImageContainer.addEventListener('touchend', resetZoom);
    } else {
        console.error('Zoom modal elements not found!');
        showNotification(`⚠️ Lỗi hiển thị zoom modal!`, 'error');
    }
}

function closeZoomModal() {
    const zoomModal = document.getElementById('zoomModal');
    const zoomImageContainer = document.getElementById('zoomImage')?.parentElement;
    if (zoomModal && zoomImageContainer) {
        zoomModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        zoomImageContainer.removeEventListener('mousemove', debouncedMouseMove);
        zoomImageContainer.removeEventListener('mouseleave', resetZoom);
        zoomImageContainer.removeEventListener('touchmove', handleTouchMove);
        zoomImageContainer.removeEventListener('touchend', resetZoom);
    }
}

function handleMouseMove(e) {
    const container = e.currentTarget;
    if (!container || container.offsetParent === null) {
        return;
    }

    const rect = container.getBoundingClientRect();
    if (!rect.width || !rect.height) {
        return;
    }

    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const clampedX = Math.max(0, Math.min(x, rect.width));
    const clampedY = Math.max(0, Math.min(y, rect.height));

    container.style.setProperty('--mouse-x', `${clampedX}px`);
    container.style.setProperty('--mouse-y', `${clampedY}px`);

    const img = container.querySelector('img');
    if (img) {
        const xPercent = (clampedX / rect.width) * 100;
        const yPercent = (clampedY / rect.height) * 100;
        img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        img.style.transform = container.classList.contains('zoom-modal-content') ? 'scale(2)' : 'scale(1.5)';
    }
}

function handleTouchMove(e) {
    e.preventDefault();
    const container = e.currentTarget;
    if (!container || container.offsetParent === null) {
        return;
    }

    const rect = container.getBoundingClientRect();
    if (!rect.width || !rect.height) {
        return;
    }

    const touch = e.touches[0];
    const x = touch.clientX - rect.left;
    const y = touch.clientY - rect.top;

    const clampedX = Math.max(0, Math.min(x, rect.width));
    const clampedY = Math.max(0, Math.min(y, rect.height));

    container.style.setProperty('--mouse-x', `${clampedX}px`);
    container.style.setProperty('--mouse-y', `${clampedY}px`);

    const img = container.querySelector('img');
    if (img) {
        const xPercent = (clampedX / rect.width) * 100;
        const yPercent = (clampedY / rect.height) * 100;
        img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        img.style.transform = container.classList.contains('zoom-modal-content') ? 'scale(2)' : 'scale(1.5)';
    }
}

function resetZoom(e) {
    const container = e.currentTarget;
    if (container) {
        const img = container.querySelector('img');
        if (img) {
            img.style.transformOrigin = 'center center';
            img.style.transform = 'scale(1)';
        }
        container.style.setProperty('--mouse-x', '50%');
        container.style.setProperty('--mouse-y', '50%');
    }
}

const debouncedMouseMove = debounce(handleMouseMove, 10);

function closeModal() {
    const productModal = document.getElementById('productModal');
    const modalImage = document.getElementById('modalImage');
    if (productModal && modalImage) {
        productModal.style.display = 'none';
        const modalImageContainer = modalImage.parentElement;
        modalImageContainer.removeEventListener('mousemove', debouncedMouseMove);
        modalImageContainer.removeEventListener('mouseleave', resetZoom);
        modalImageContainer.removeEventListener('click', openZoomModal);
        modalImageContainer.removeEventListener('touchmove', handleTouchMove);
        modalImageContainer.removeEventListener('touchend', resetZoom);
    }
    currentProductId = '';
}

function buyNow(dishId) {
    const id = dishId || currentProductId;
    if (!id) {
        showNotification('⚠️ Không xác định được món ăn!', 'error');
        return;
    }

    showNotification('Đang xử lý...', 'info');

    const xhr = new XMLHttpRequest();
    // Gọi đến route mới trong OrderController
    xhr.open('POST', `${BASE_URL}order/buyNow`, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success && response.redirect_url) {
                    showNotification('✅ Đã thêm vào giỏ! Đang chuyển đến trang thanh toán...', 'success');
                    // Chuyển hướng đến trang thanh toán
                    window.location.href = response.redirect_url;
                } else {
                    showNotification(response.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                }
            } catch (e) {
                showNotification('Lỗi xử lý phản hồi từ máy chủ.', 'error');
            }
        } else {
            showNotification('Lỗi máy chủ. Vui lòng thử lại sau.', 'error');
        }
    };

    xhr.onerror = function () {
        showNotification('Lỗi kết nối. Vui lòng kiểm tra lại mạng.', 'error');
    };

    xhr.send(`product_id=${id}`);
}

function toggleCart() {
    window.location.href = BASE_URL + 'cart.php';
}
function addToCart(dishId) {
    const dish = products[dishId];
    if (!dish) {
        showNotification('⚠️ Món ăn không tồn tại!', 'error');
        return;
    }
    sendAjaxRequest('add_to_cart', dishId, (response) => {
        if (response.success) {
            showNotification(`✅ Đã thêm "${dish.name}" vào giỏ hàng!`, 'success');

            // Cập nhật số lượng giỏ hàng từ server
            const cartCountEl = document.getElementById('cart-count');
            if (cartCountEl && response.cart_count !== undefined) {
                cartCountEl.textContent = response.cart_count;
            }

            // Không tự động tăng sales_count vì nó chỉ nên tăng khi đơn hàng hoàn thành
        } else {
            showNotification(response.message || '⚠️ Lỗi khi thêm món vào giỏ hàng!', 'error');
            if (response.message.includes('đăng nhập')) {
                setTimeout(() => {
                    window.location.href = BASE_URL + 'login.php';
                }, 1000);
            }
        }
    });
}

function toggleWishlist() {
    if (wishlist.length === 0) {
        showNotification('Danh sách yêu thích đang trống!', 'info');
    } else {
        let wishlistItems = wishlist.map(item => `${item.name} - ${item.price}`).join('\n');
        alert(`Danh sách yêu thích của bạn:\n\n${wishlistItems}`);
    }
}

function toggleUserMenu() {
    const userMenu = document.getElementById('userMenu');
    if (userMenu) {
        userMenu.classList.toggle('show');
    } else {
        console.error('User menu element not found!');
    }
}

function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, products:', products);
    // Lấy số lượng giỏ hàng và wishlist ban đầu
    const xhr = new XMLHttpRequest();
    xhr.open('POST', BASE_URL + 'ajax_handler.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const cartCountEl = document.getElementById('cart-count');
                    const wishlistCountEl = document.getElementById('wishlist-count');
                    if (cartCountEl) cartCountEl.textContent = response.cart_count || 0;
                    if (wishlistCountEl) wishlistCountEl.textContent = response.wishlist_count || 0;
                }
            } catch (e) {
                console.error('Error parsing counts response:', e);
            }
        }
    };
    xhr.send('action=get_counts');
    filterDishes('all');

    const userIcon = document.querySelector('.user-icon');
    if (userIcon) {
        userIcon.addEventListener('click', toggleUserMenu);
    } else {
        console.error('User icon element not found!');
    }

    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            const menuNav = document.querySelector('.menu-nav');
            if (menuNav) menuNav.classList.toggle('active');
        });
    }

    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            const category = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            filterDishes(category, e);
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                transform: scale(0);
                left: ${x}px;
                top: ${y}px;
                width: ${size}px;
                height: ${size}px;
                animation: ripple 0.6s ease;
                pointer-events: none;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });

    document.addEventListener('click', (e) => {
        const userIcon = document.querySelector('.user-icon');
        const userMenu = document.getElementById('userMenu');
        if (userMenu && !userIcon.contains(e.target) && !userMenu.contains(e.target)) {
            userMenu.classList.remove('show');
        }
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const scrollTopBtn = document.getElementById('scrollTop');
    if (scrollTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    window.addEventListener('load', () => {
        setTimeout(() => {
            showNotification('🎉 Chào mừng bạn đến với CTUT Restaurant! Khuyến mãi 20% cho sinh viên!', 'success');
        }, 1000);
    });

    document.querySelectorAll('.btn, .action-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                transform: scale(0);
                left: ${x}px;
                top: ${y}px;
                width: ${size}px;
                height: ${size}px;
                animation: ripple 0.6s ease;
                pointer-events: none;
            `;

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });
});

window.onclick = function (event) {
    const modal = document.getElementById('productModal');
    const zoomModal = document.getElementById('zoomModal');
    if (event.target === modal) {
        closeModal();
    }
    if (event.target === zoomModal) {
        closeZoomModal();
    }
};

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal();
        closeZoomModal();
        const userMenu = document.getElementById('userMenu');
        if (userMenu) userMenu.classList.remove('show');
    }
});

// Hàm tìm kiếm món ăn
function searchDishes() {
    const searchInput = document.getElementById('searchInput');
    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const activeFilter = document.querySelector('.filter-btn.active');
    const category = activeFilter ? activeFilter.getAttribute('onclick').match(/'([^']+)'/)[1] : 'all';

    console.log('Search triggered with query:', query, 'and category:', category);
    filterDishes(category, null, query);
}

// Hàm lọc món ăn
function filterDishes(category, event = null, searchQuery = '') {
    console.log('Filtering category:', category, 'Search query:', searchQuery);
    console.log('Products object:', products);

    const dishGrid = document.querySelector('.dish-grid');
    if (!dishGrid) {
        console.error('Dish grid element not found!');
        showNotification('⚠️ Lỗi: Không tìm thấy danh sách món ăn!', 'error');
        return;
    }

    const filterButtons = document.querySelectorAll('.filter-btn');
    if (!filterButtons.length) {
        console.error('Filter buttons not found!');
        showNotification('⚠️ Lỗi: Không tìm thấy nút lọc danh mục!', 'error');
        return;
    }

    // Cập nhật trạng thái nút lọc
    filterButtons.forEach(btn => btn.classList.remove('active'));
    if (event && event.target) {
        event.target.classList.add('active');
    } else {
        const targetButton = Array.from(filterButtons).find(btn => btn.getAttribute('onclick').includes(`'${category}'`));
        if (targetButton) {
            targetButton.classList.add('active');
        } else {
            const allButton = document.querySelector('.filter-btn[onclick="filterDishes(\'all\')"]');
            if (allButton) allButton.classList.add('active');
        }
    }

    // Lọc và sắp xếp món ăn
    let filteredProducts = Object.values(products);
    console.log('All products:', filteredProducts);

    // Lọc theo danh mục
    if (category !== 'all') {
        filteredProducts = filteredProducts.filter(product => {
            const match = product.category === category;
            console.log(`Checking product ${product.name}: category ${product.category} === ${category} ? ${match}`);
            return match;
        });
        console.log(`Filtered products for category ${category}:`, filteredProducts);
    }

    // Lọc theo từ khóa tìm kiếm
    if (searchQuery) {
        filteredProducts = filteredProducts.filter(product => {
            const nameMatch = product.name.toLowerCase().includes(searchQuery);
            const descMatch = product.description.toLowerCase().includes(searchQuery);
            console.log(`Checking product ${product.name}: nameMatch=${nameMatch}, descMatch=${descMatch}`);
            return nameMatch || descMatch;
        });
        console.log(`Filtered products for query "${searchQuery}":`, filteredProducts);
    }

    // Hiển thị thông báo nếu không có món ăn
    if (filteredProducts.length === 0) {
        dishGrid.innerHTML = '<p style="text-align: center; color: #666;">Không tìm thấy món ăn nào.</p>';
        showNotification('Không tìm thấy món ăn phù hợp!', 'info');
        return;
    }

    // Sắp xếp theo salesCount
    filteredProducts.sort((a, b) => b.salesCount - a.salesCount);

    // Xóa nội dung hiện tại của dish-grid
    dishGrid.innerHTML = '';

    // Tạo thẻ dish-card
    filteredProducts.forEach((product) => {
        const isBestSeller = product.isBestSeller;
        const dishCard = document.createElement('div');
        dishCard.className = `dish-card fade-in ${isBestSeller ? 'best-seller' : ''}`;
        dishCard.setAttribute('data-dish-id', product.id);
        dishCard.setAttribute('data-category', product.category);
        dishCard.setAttribute('data-sales', product.salesCount);

        dishCard.innerHTML = `
            ${isBestSeller ? `
                <div class="best-seller-badge">BEST SELLER</div>
                <div class="trending-effect"></div>
            ` : ''}
            ${product.salesCount > 100 ? `
                <div class="popularity-indicator"><i class="fas fa-chart-line"></i> Hot</div>
            ` : ''}
            <div class="dish-image">
                <img src="${product.image}" alt="${product.name}">
                <div class="sales-stats">
                    <i class="fas fa-shopping-cart"></i> ${product.salesCount} đã bán
                </div>
                <div class="dish-actions">
                    <button class="action-btn" onclick="addToCart(${product.id})">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                    <button class="action-btn wishlist" onclick="addToWishlist(${product.id})">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="action-btn" onclick="showDetails(${product.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="dish-info">
                <h3>
                    ${product.name}
                    <span class="dish-price">${product.price}</span>
                </h3>
                <p>${product.description}</p>
                <div class="dish-actions-bottom">
                    <button class="btn btn-buy-now" onclick="buyNow(${product.id})">
                        <i class="fas fa-bolt"></i> Mua ngay
                    </button>
                </div>
            </div>
        `;

        dishGrid.appendChild(dishCard);
    });

    // Điều chỉnh layout của dish-grid
    if (filteredProducts.length === 1) {
        dishGrid.style.display = 'flex';
        dishGrid.style.justifyContent = 'center';
    } else {
        dishGrid.style.display = 'grid';
    }

    // Áp dụng hiệu ứng fade-in
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.dish-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // Cuộn mượt đến phần món ăn
    scrollToElement('dishes');
}

