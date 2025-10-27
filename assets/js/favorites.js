document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    const removeFavoriteBtns = document.querySelectorAll('.remove-favorite-btn');
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-form input[name="search"]');
    const categorySelect = document.querySelector('.search-form select[name="category_id"]');

    // Hàm hiển thị toast
    function showToast(message, isError = false) {
        toastMessage.textContent = message;
        toast.classList.remove('error');
        if (isError) toast.classList.add('error');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Hàm xóa món với animation
    function removeFavoriteWithAnimation(dishCard, dishId) {
        dishCard.style.transform = 'scale(0.8) translateY(-50px)';
        dishCard.style.opacity = '0';
        dishCard.style.transition = 'all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';

        setTimeout(() => {
            dishCard.remove();
            const remainingDishes = document.querySelectorAll('.dish-card');
            if (remainingDishes.length === 0) {
                location.reload();
            } else {
                updateFavoriteCount();
            }
        }, 500);
    }

    // Hàm cập nhật số lượng yêu thích
    function updateFavoriteCount() {
        const countBadge = document.querySelector('.count-badge');
        if (countBadge) {
            const currentCount = document.querySelectorAll('.dish-card').length;
            countBadge.textContent = currentCount;
            countBadge.style.transform = 'scale(1.3)';
            setTimeout(() => countBadge.style.transform = 'scale(1)', 200);
        }
    }

    // Xử lý xóa khỏi yêu thích
    removeFavoriteBtns.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            const dishId = this.getAttribute('data-dish-id');
            const dishCard = this.closest('.dish-card');

            if (!confirm('Bạn có chắc muốn xóa món này khỏi danh sách yêu thích?')) return;

            this.disabled = true;
            this.style.opacity = '0.5';

            try {
                const formData = new FormData();
                formData.append('action', 'remove_favorite');
                formData.append('dish_id', dishId);

                const response = await fetch('favorites.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    removeFavoriteWithAnimation(dishCard, dishId);
                } else {
                    showToast(result.message || 'Có lỗi xảy ra!', true);
                    this.disabled = false;
                    this.style.opacity = '1';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Không thể kết nối đến máy chủ!', true);
                this.disabled = false;
                this.style.opacity = '1';
            }
        });
    });

    // Xử lý thêm vào giỏ hàng
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            if (this.disabled) return;

            const dishId = this.getAttribute('data-dish-id');
            const originalText = this.innerHTML;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';

            try {
                const formData = new FormData();
                formData.append('action', 'add_to_cart');
                formData.append('dish_id', dishId);
                formData.append('quantity', 1);

                const response = await fetch('favorites.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    this.innerHTML = '<i class="fas fa-check"></i> Đã thêm!';
                    this.style.background = 'linear-gradient(45deg, #4caf50, #66bb6a)';

                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.background = '';
                        this.disabled = false;
                    }, 2000);
                } else {
                    showToast(result.message || 'Có lỗi xảy ra!', true);
                    this.innerHTML = originalText;
                    this.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Không thể kết nối đến máy chủ!', true);
                this.innerHTML = originalText;
                this.disabled = false;
            }
        });
    });

    // Xử lý tìm kiếm và lọc
    if (searchForm) {
        searchInput.addEventListener('input', debounce(() => searchForm.submit(), 300));
        categorySelect.addEventListener('change', () => searchForm.submit());
    }

    // Hàm debounce
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Lazy loading hình ảnh
    const images = document.querySelectorAll('.dish-image');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.5s ease';
                img.onload = () => img.style.opacity = '1';
                observer.unobserve(img);
            }
        });
    }, { rootMargin: '50px' });

    images.forEach(img => imageObserver.observe(img));

    // Hiệu ứng parallax
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const scrolled = window.pageYOffset;
                const background = document.querySelector('.background-overlay');
                if (background) {
                    background.style.transform = `translateY(${scrolled * 0.3}px) scale(1.1)`;
                }
                ticking = false;
            });
            ticking = true;
        }
    });

    // Animation load trang
    const favoritesGrid = document.querySelector('.favorites-grid');
    if (favoritesGrid) {
        favoritesGrid.style.opacity = '0';
        setTimeout(() => {
            favoritesGrid.style.transition = 'opacity 0.8s ease';
            favoritesGrid.style.opacity = '1';
        }, 100);
    }

    // Xử lý lỗi hình ảnh
    images.forEach(img => {
        img.addEventListener('error', function () {
            const wrapper = this.closest('.dish-image-wrapper');
            if (wrapper) {
                this.style.display = 'none';
                const placeholder = document.createElement('div');
                placeholder.className = 'dish-image-placeholder';
                placeholder.innerHTML = '<i class="fas fa-utensils"></i>';
                wrapper.appendChild(placeholder);
            }
        });
    });

    // Skeleton loading
    const dishCards = document.querySelectorAll('.dish-card');
    dishCards.forEach((card, index) => {
        card.classList.add('skeleton');
        setTimeout(() => card.classList.remove('skeleton'), (index + 1) * 100);
    });

    console.log('✅ Favorites page loaded successfully!');
});

