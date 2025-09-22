document.addEventListener('DOMContentLoaded', () => {
    // Cart state
    let isLoading = false;
    let map, marker;
    let isMapInitialized = false;

    // Format price
    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    };

    // Debounce function for search
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
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
        const checkoutBtn = document.getElementById('checkout-btn');

        selectedCountElement.textContent = selectedCount;
        checkoutCountElement.textContent = selectedCount;

        let subtotal = 0;
        selectedItems.forEach(checkbox => {
            const cartItem = checkbox.closest('.cart-item');
            const price = parseFloat(cartItem.querySelector('.item-price').textContent.replace(/[^0-9]/g, ''));
            const quantity = parseInt(cartItem.querySelector('.quantity-display').textContent);
            subtotal += price * quantity;
        });

        const deliveryOption = document.querySelector('input[name="delivery"]:checked').value;
        const deliveryFee = deliveryOption === 'delivery' ? 25000 : 0;
        const discount = 0; // Giả định không có giảm giá
        const total = subtotal + deliveryFee - discount;

        subtotalElement.textContent = formatPrice(subtotal);
        deliveryFeeElement.textContent = formatPrice(deliveryFee);
        discountElement.textContent = formatPrice(discount);
        totalElement.textContent = formatPrice(total);

        checkoutBtn.disabled = selectedCount === 0;
    };


    // Handle remove item
    const removeButtons = document.querySelectorAll('.remove-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault(); // Ngăn form submit mặc định
            if (isLoading) return;

            isLoading = true;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';

            const form = button.closest('form');
            const cartItemId = form.querySelector('input[name="cart_item_id"]').value;
            console.log('cartItemId:', cartItemId); // Ghi log ID gửi đi

            try {
                console.log('Fetching:', BASE_URL + 'cart.php'); // Ghi log URL
                const response = await fetch(BASE_URL + 'cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=remove_item&cart_item_id=${encodeURIComponent(cartItemId)}`
                });

                console.log('Response status:', response.status); // Ghi log mã trạng thái
                if (!response.ok) {
                    throw new Error(`Network error: ${response.status} ${response.statusText}`);
                }

                let data;
                try {
                    data = await response.json(); // Parse JSON
                    console.log('Server response:', data); // Ghi log phản hồi
                } catch (error) {
                    const text = await response.text();
                    console.error('Invalid JSON response:', text); // Ghi log phản hồi không phải JSON
                    throw new Error(`Invalid JSON response: ${text}`);
                }

                if (data.success) {
                    const cartItem = button.closest('.cart-item');
                    cartItem.style.transition = 'opacity 0.3s ease';
                    cartItem.style.opacity = '0';
                    setTimeout(() => {
                        cartItem.remove();
                        updateCartSummary();

                        // Hiển thị thông báo thành công
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success';
                        alert.textContent = data.message;
                        document.querySelector('.main-content').prepend(alert);
                        setTimeout(() => alert.remove(), 3000);

                        // Kiểm tra giỏ hàng trống
                        if (!document.querySelectorAll('.cart-item').length) {
                            const cartItemsContainer = document.getElementById('cart-items');
                            cartItemsContainer.innerHTML = `
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>Giỏ hàng trống</h3>
                                <p>Thêm món ăn yêu thích của bạn để tiếp tục!</p>
                            </div>
                        `;
                        }
                    }, 300);
                } else {
                    throw new Error(data.message || 'Server response error');
                }
            } catch (error) {
                console.error('Error removing item:', error);
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.textContent = error.message || 'Không thể xóa món';
                document.querySelector('.main-content').prepend(alert);
                setTimeout(() => alert.remove(), 3000);
            } finally {
                isLoading = false;
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash-alt"></i> Xóa';
            }
        });
    });
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
            selectAllCheckbox.checked = document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length;
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
                const itemName = item.querySelector('.item-name').textContent.toLowerCase();
                const isVisible = itemName.includes(query);
                item.style.display = isVisible ? 'flex' : 'none';
                if (isVisible) hasResults = true;
            });

            const noResults = document.createElement('div');
            noResults.classList.add('no-results');
            noResults.innerHTML = `
                <i class="fas fa-search"></i>
                <h4>Không tìm thấy món ăn</h4>
                <p>Vui lòng thử từ khóa khác!</p>
            `;
            cartItemsContainer.querySelector('.no-results')?.remove();
            if (!hasResults && query) {
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
                method.querySelector('input').checked = true;
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
                alert('Địa chỉ đã được xác nhận!');
                // Thêm logic gửi địa chỉ đến server nếu cần
            }
        });
    }
});