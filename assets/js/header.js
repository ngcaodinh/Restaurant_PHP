// Toggle hamburger menu
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const menuNav = document.querySelector('.menu-nav');

    if (hamburger && menuNav) {
        hamburger.addEventListener('click', () => {
            menuNav.classList.toggle('active');
        });
    }
});

// Toggle dropdown menu on mobile
document.addEventListener('DOMContentLoaded', () => {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const link = dropdown.querySelector('a');
        const content = dropdown.querySelector('.dropdown-content');
        if (link && content) {
            link.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    content.classList.toggle('active');
                }
            });
        }
    });
});

// Toggle user menu
function toggleUserMenu() {
    const userMenu = document.getElementById('userMenu');
    if (userMenu) {
        userMenu.classList.toggle('show');
    }
}

// Close user menu when clicking outside
document.addEventListener('click', (e) => {
    const userIcon = document.querySelector('.user-icon');
    const userMenu = document.getElementById('userMenu');
    if (userIcon && userMenu && !userIcon.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.remove('show');
    }
});

// Add click event to user icon
document.addEventListener('DOMContentLoaded', () => {
    const userIcon = document.querySelector('.user-icon');
    if (userIcon) {
        userIcon.addEventListener('click', toggleUserMenu);
    }
});

// Toggle cart
function toggleCart() {
    showNotification('Giỏ hàng đang trống!', 'info');
}

// Toggle wishlist
function toggleWishlist() {
    window.location.href = '/Restaurant_PHP/favorites.php';
}

// Show notification function
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

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

    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 10000;
        background: ${type === 'success' ? 'linear-gradient(45deg, #4ade80, #22c55e)' :
            type === 'warning' ? 'linear-gradient(45deg, #fbbf24, #f59e0b)' :
                'linear-gradient(45deg, #3b82f6, #1d4ed8)'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        animation: slideInRight 0.3s ease;
        max-width: 350px;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);
}