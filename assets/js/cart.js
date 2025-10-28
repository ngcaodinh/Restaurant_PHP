document.addEventListener('DOMContentLoaded', () => {
    // Base URL for AJAX requests
    const BASE_URL = window.location.origin + '/Restaurant_PHP/';

    // Cart state
    let isLoading = false;
    let map, marker;
    let isMapInitialized = false;

    // Format price function
    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    };

    // Animate number counting
    const animateNumber = (element, startValue, endValue, duration = 500) => {
        if (!element) {
            console.warn('Element not found for animation');
            return;
        }

        // If values are the same, no animation needed
        if (startValue === endValue) {
            element.textContent = formatPrice(endValue);
            return;
        }

        const startTime = performance.now();
        const difference = endValue - startValue;

        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Use easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.round(startValue + (difference * easeOutQuart));

            element.textContent = formatPrice(currentValue);

            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                // Ensure final value is exact
                element.textContent = formatPrice(endValue);
            }
        };

        requestAnimationFrame(updateNumber);
    };

    // Debounce function for search
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    // Show toast notification
    const showAlert = (message, type = 'success') => {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;

        let icon = '';
        if (type === 'success') {
            icon = '<i class="fas fa-check-circle"></i>';
        } else if (type === 'danger') {
            icon = '<i class="fas fa-times-circle"></i>';
        } else if (type === 'warning') {
            icon = '<i class="fas fa-exclamation-triangle"></i>';
        }

        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-message">${message}</div>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => toast.remove());
        }, 3000);
    };

    // Update cart summary
    const updateCartSummary = () => {
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        const selectedCount = selectedItems.length;
        const subtotalElement = document.getElementById('subtotal');
        const deliveryFeeElement = document.getElementById('delivery-fee');
        const discountElement = document.getElementById('discount');
        const totalElement = document.getElementById('total');
        const checkoutCountElement = document.getElementById('checkout-count');
        const selectedCountElement = document.getElementById('selected-count');

        // Add null checks to prevent errors
        if (!subtotalElement || !totalElement) {
            console.warn('Required price elements not found in DOM');
            return;
        }
        const checkoutBtn = document.getElementById('checkout-btn');

        if (selectedCountElement) selectedCountElement.textContent = selectedCount;
        if (checkoutCountElement) checkoutCountElement.textContent = selectedCount;

        let subtotal = 0;
        selectedItems.forEach(checkbox => {
            const cartItem = checkbox.closest('.cart-item');
            if (!cartItem) return;

            const priceElement = cartItem.querySelector('.item-price');
            const quantityElement = cartItem.querySelector('.quantity-display');

            if (!priceElement || !quantityElement) {
                console.warn('Price or quantity element not found in cart item');
                return;
            }

            const priceText = priceElement.textContent;
            const price = parseFloat(priceText.replace(/[^0-9]/g, ''));
            const quantity = parseInt(quantityElement.textContent);

            if (!isNaN(price) && !isNaN(quantity)) {
                subtotal += price * quantity;
            }
        });

        const deliveryOption = document.querySelector('input[name="delivery"]:checked');
        const deliveryFee = deliveryOption && deliveryOption.value === 'delivery' ? 25000 : 0;
        const discount = 0; // No discount for now
        const total = subtotal + deliveryFee - discount;

        // Animate price changes
        if (subtotalElement) {
            const currentSubtotal = parseFloat(subtotalElement.textContent.replace(/[^0-9]/g, '')) || 0;
            if (currentSubtotal !== subtotal) {
                animateNumber(subtotalElement, currentSubtotal, subtotal);
            }
        }

        if (deliveryFeeElement) {
            const currentDeliveryFee = parseFloat(deliveryFeeElement.textContent.replace(/[^0-9]/g, '')) || 0;
            if (currentDeliveryFee !== deliveryFee) {
                animateNumber(deliveryFeeElement, currentDeliveryFee, deliveryFee);
            }
        }

        if (discountElement) {
            const currentDiscount = parseFloat(discountElement.textContent.replace(/[^0-9]/g, '')) || 0;
            if (currentDiscount !== discount) {
                animateNumber(discountElement, currentDiscount, discount);
            }
        }

        if (totalElement) {
            const currentTotal = parseFloat(totalElement.textContent.replace(/[^0-9]/g, '')) || 0;
            if (currentTotal !== total) {
                animateNumber(totalElement, currentTotal, total);
            }
        }

        if (checkoutBtn) {
            checkoutBtn.disabled = selectedCount === 0;
        }
    };


    // Handle quantity changes and item removal
    const handleCartAction = async (action, cartItemId, quantity, buttonElement) => {
        if (isLoading) return;
        isLoading = true;

        const formData = new URLSearchParams();
        formData.append('action', action);
        formData.append('cart_item_id', cartItemId);
        if (quantity) formData.append('quantity', quantity);

        try {
            console.log(`Sending ${action} request for cart item ${cartItemId}`, { quantity });

            let url = '';
            if (action === 'update_quantity') {
                url = `${BASE_URL}api/cart/update-quantity`;
            } else if (action === 'remove_item') {
                url = `${BASE_URL}api/cart/remove`;
            } else {
                showAlert('Hành động không hợp lệ.', 'danger');
                isLoading = false;
                return;
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            console.log('Response status:', response.status);

            if (!response.ok) throw new Error(`Network error: ${response.statusText}`);

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', responseText);
                throw new Error('Invalid JSON response from server');
            }

            if (data.success) {
                if (action === 'remove_item') {
                    showAlert('Đã xóa món ăn khỏi giỏ hàng!', 'success');
                } else if (action === 'update_quantity') {
                    showAlert('Đã cập nhật số lượng thành công!', 'success');
                } else {
                    showAlert(data.message, 'success');
                }

                if (action === 'remove_item') {
                    // Find the cart item by looking for the button that was clicked
                    const cartItem = buttonElement.closest('.cart-item');
                    if (cartItem) {
                        cartItem.classList.add('removing');
                        setTimeout(() => {
                            cartItem.remove();
                            updateCartSummary();
                            checkEmptyCart();

                            // Update select all checkbox state
                            const selectAllCheckbox = document.getElementById('select-all');
                            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
                            if (selectAllCheckbox && itemCheckboxes.length > 0) {
                                selectAllCheckbox.checked = document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length;
                            } else if (selectAllCheckbox) {
                                selectAllCheckbox.checked = false;
                            }
                        }, 300);
                    }
                } else if (action === 'update_quantity') {
                    updateCartSummary();
                }
            } else {
                showAlert(data.message, 'danger');
                // Reset button state on error
                if (action === 'remove_item' && buttonElement) {
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Xóa';
                }
            }
        } catch (error) {
            showAlert('Lỗi không xác định. Vui lòng thử lại.', 'danger');
            console.error('Cart action error:', error);

            // Reset button state on error
            if (action === 'remove_item' && buttonElement) {
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Xóa';
            }
        } finally {
            isLoading = false;
        }
    };

    // Add event listeners for cart actions
    document.addEventListener('click', (e) => {
        const button = e.target.closest('.quantity-btn, .remove-btn');
        if (!button) return;

        const cartItem = button.closest('.cart-item');
        if (!cartItem) return;

        const cartItemId = button.getAttribute('data-cart-item-id') || cartItem.dataset.id;

        if (button.classList.contains('quantity-btn')) {
            e.preventDefault();
            const quantityDisplay = cartItem.querySelector('.quantity-display');
            if (!quantityDisplay) {
                console.error('Quantity display element not found');
                return;
            }
            let currentQuantity = parseInt(quantityDisplay.textContent) || 1;
            const action = button.dataset.action;

            if (action === 'increase') {
                currentQuantity++;
            } else if (action === 'decrease') {
                currentQuantity = Math.max(1, currentQuantity - 1);
            } else {
                console.error('Unknown action:', action);
                return;
            }

            // Update display immediately for better UX
            quantityDisplay.textContent = currentQuantity;

            // Disable/enable buttons
            const decreaseBtn = cartItem.querySelector('.quantity-btn[data-action="decrease"]');
            const increaseBtn = cartItem.querySelector('.quantity-btn[data-action="increase"]');
            if (decreaseBtn) decreaseBtn.disabled = currentQuantity <= 1;

            // Send AJAX request
            handleCartAction('update_quantity', cartItemId, currentQuantity, button);

        } else if (button.classList.contains('remove-btn')) {
            e.preventDefault();
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
            handleCartAction('remove_item', cartItemId, null, button);
        }
    });

    // Check if cart is empty and show empty state
    const checkEmptyCart = () => {
        const cartItems = document.querySelectorAll('.cart-item');
        const cartItemsContainer = document.getElementById('cart-items');

        if (cartItems.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart text-center py-5">
                    <i class="fas fa-shopping-cart mb-3"></i>
                    <h3>Giỏ hàng trống</h3>
                    <p class="mb-4">Thêm món ăn yêu thích của bạn để tiếp tục!</p>
                    <a href="menu.php" class="btn btn-primary">
                        <i class="fas fa-utensils me-2"></i>Xem thực đơn
                    </a>
                </div>
            `;
        }
    };

    // Select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
                checkbox.closest('.cart-item').classList.toggle('selected', checkbox.checked);
            });
            updateCartSummary();
        });
    }

    // Individual item checkboxes
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            checkbox.closest('.cart-item').classList.toggle('selected', checkbox.checked);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length;
            }
            updateCartSummary();
        });
    });

    // Search functionality
    const searchInput = document.getElementById('search-input');
    const searchClear = document.getElementById('search-clear');
    const cartItemsContainer = document.getElementById('cart-items');

    if (searchInput && searchClear && cartItemsContainer) {
        const performSearch = debounce(() => {
            const query = searchInput.value.trim().toLowerCase();
            const cartItems = document.querySelectorAll('.cart-item');

            let hasResults = false;
            cartItems.forEach(item => {
                const itemNameElement = item.querySelector('.item-name');
                if (!itemNameElement) {
                    console.warn('Item name element not found in cart item');
                    return;
                }
                const itemName = itemNameElement.textContent.toLowerCase();
                const isVisible = itemName.includes(query);
                item.style.display = isVisible ? 'flex' : 'none';
                if (isVisible) hasResults = true;
            });

            // Remove existing no-results message
            const existingNoResults = cartItemsContainer.querySelector('.no-results');
            if (existingNoResults) existingNoResults.remove();

            // Show no results message if needed
            if (!hasResults && query && cartItems.length > 0) {
                const noResults = document.createElement('div');
                noResults.classList.add('no-results');
                noResults.innerHTML = `
                    <i class="fas fa-search"></i>
                    <h4>Không tìm thấy món ăn</h4>
                    <p>Vui lòng thử từ khóa khác!</p>
                `;
                cartItemsContainer.appendChild(noResults);
            }

            searchClear.classList.toggle('show', query.length > 0);
        }, 300);

        searchInput.addEventListener('input', performSearch);
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchClear.classList.remove('show');
            performSearch();
        });
    }

    // Delivery options
    const deliveryOptions = document.querySelectorAll('.delivery-option');
    const addressInputContainer = document.getElementById('address-input-container');
    if (deliveryOptions && addressInputContainer) {
        deliveryOptions.forEach(option => {
            option.addEventListener('click', () => {
                deliveryOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                const input = option.querySelector('input');
                input.checked = true;
                addressInputContainer.classList.toggle('show', input.value === 'delivery');
                updateCartSummary();
            });
        });
    }

    // Payment methods
    const paymentMethods = document.querySelectorAll('.payment-method');
    if (paymentMethods) {
        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');
                const input = method.querySelector('input');
                if (input) input.checked = true;
            });
        });
    }

    // Google Maps initialization
    window.initMap = () => {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        map = new google.maps.Map(mapElement, {
            center: { lat: 10.7769, lng: 106.7009 },
            zoom: 15,
            styles: [
                { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] },
                { featureType: "transit", elementType: "labels", stylers: [{ visibility: "off" }] }
            ]
        });

        marker = new google.maps.Marker({
            map: map,
            draggable: true
        });

        isMapInitialized = true;

        const addressInput = document.getElementById('address-input');
        if (addressInput) {
            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ['address'],
                componentRestrictions: { country: 'vn' }
            });

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (place.geometry) {
                    map.setCenter(place.geometry.location);
                    marker.setPosition(place.geometry.location);
                    mapElement.classList.add('show');
                    validateAddressInputs();
                }
            });
        }
    };

    // Validate address inputs
    const validateAddressInputs = () => {
        const addressInput = document.getElementById('address-input');
        const phoneInput = document.getElementById('phone-input');
        const confirmAddressBtn = document.getElementById('confirm-address-btn');

        if (addressInput && phoneInput && confirmAddressBtn) {
            const isValidAddress = addressInput.value.trim().length > 0;
            const isValidPhone = phoneInput.value.match(/^[0][0-9]{9}$/);
            confirmAddressBtn.disabled = !(isValidAddress && isValidPhone);
        }
    };

    // Address and phone input handlers
    const addressInput = document.getElementById('address-input');
    const phoneInput = document.getElementById('phone-input');
    if (addressInput && phoneInput) {
        addressInput.addEventListener('input', validateAddressInputs);
        phoneInput.addEventListener('input', validateAddressInputs);
    }

    // Confirm address button
    const confirmAddressBtn = document.getElementById('confirm-address-btn');
    if (confirmAddressBtn) {
        confirmAddressBtn.addEventListener('click', () => {
            if (!confirmAddressBtn.disabled) {
                showAlert('Địa chỉ đã được xác nhận!', 'success');
            }
        });
    }

    // Checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            if (selectedItems.length === 0) {
                showAlert('Vui lòng chọn ít nhất một món để thanh toán!', 'warning');
                return;
            }

            // Redirect to checkout page
            window.location.href = 'checkout.php';
        });
    }

    // Initialize cart summary on page load
    updateCartSummary();
});