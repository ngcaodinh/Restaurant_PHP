/**
 * Tệp JavaScript xử lý trang yêu thích
 * Xử lý thêm vào giỏ hàng và xóa khỏi danh sách yêu thích
 */

document.addEventListener('DOMContentLoaded', function () {
    const favoriteItemsContainer = document.getElementById('favorite-items');

    /** Xử lý thêm món vào giỏ hàng từ danh sách yêu thích */
    favoriteItemsContainer.addEventListener('click', function (event) {
        const addToCartButton = event.target.closest('.add-to-cart-btn');
        if (addToCartButton) {
            const dishId = addToCartButton.getAttribute('data-dish-id');
            addToCart(dishId, 1);
        }
    });

    /** Xử lý xóa món khỏi danh sách yêu thích qua AJAX */
    favoriteItemsContainer.addEventListener('submit', function (event) {
        if (event.target.matches('.remove-form')) {
            event.preventDefault();
            const form = event.target;
            const dishId = form.querySelector('input[name="dish_id"]').value;
            const favoriteItemCard = form.closest('.favorite-item');

            // Send AJAX request (using the toggle function)
            sendRemoveRequest(dishId, (response) => {
                if (response.success && response.action === 'removed') {
                    // Trigger animation
                    favoriteItemCard.classList.add('removing');

                    // Remove from DOM after animation
                    setTimeout(() => {
                        favoriteItemCard.remove();
                        // Check if the list is empty
                        if (favoriteItemsContainer.querySelectorAll('.favorite-item').length === 0) {
                            showEmptyMessage();
                        }
                    }, 500); // Corresponds to CSS transition time
                }
            });
        }
    });

    function addToCart(dishId, quantity) {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('dish_id', dishId);
        formData.append('quantity', quantity);

        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Use the notification function from main.js
                    showNotification('Đã thêm vào giỏ hàng!', 'success');

                    // Update cart count in header without reloading
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        const currentCount = parseInt(cartCountElement.textContent, 10) || 0;
                        cartCountElement.textContent = currentCount + parseInt(quantity, 10);
                    }
                } else {
                    showNotification('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra!', 'error');
            });
    }

    function sendRemoveRequest(dishId, callback) {
        const formData = new FormData();
        formData.append('action', 'add_to_wishlist'); // This is the toggle action
        formData.append('dish_id', dishId);

        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => console.error('Error:', error));
    }

    function showEmptyMessage() {
        const emptyMessageHTML = `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-heart-broken empty-icon"></i>
                <h3>Bạn chưa có sản phẩm yêu thích nào.</h3>
                <p class="mb-4">Hãy khám phá thực đơn và thêm những món bạn thích nhé!</p>
                <a href="index.php" class="btn btn-primary btn-view-menu">
                    <i class="fas fa-utensils me-2"></i>Xem thực đơn
                </a>
            </div>`;
        favoriteItemsContainer.innerHTML = emptyMessageHTML;
    }
});

function showNotification(message, type = 'info') {
    // Remove any existing notification to prevent overlap
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    // Create the notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;

    // Set the inner HTML
    notification.innerHTML = `
        <div class="notification-content">
            <span>${message}</span>
            <button>&times;</button>
        </div>
    `;

    // Append to the body
    document.body.appendChild(notification);

    // Auto-dismissal logic
    const lifeTime = 5000; // 5 seconds
    const animationTime = 500; // 0.5 seconds

    // 1. Start the timer to slide out
    const slideOutTimer = setTimeout(() => {
        notification.classList.add('slide-out');

        // 2. Remove the element after the slide-out animation completes
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, animationTime);
    }, lifeTime - animationTime);

    // Manual close button
    notification.querySelector('button').addEventListener('click', () => {
        clearTimeout(slideOutTimer); // Stop the auto-dismiss timer
        notification.classList.add('slide-out');

        // Remove after animation
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, animationTime);
    });
}


