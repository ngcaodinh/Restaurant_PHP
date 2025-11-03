/**
 * Tệp JavaScript xử lý trang lịch sử mua hàng
 * Xử lý hiển thị chi tiết đơn hàng và hủy đơn hàng
 */

document.addEventListener('DOMContentLoaded', function () {
    const orderIdFilter = document.getElementById('orderIdFilter');
    const dateFilter = document.getElementById('dateFilter');
    const orderBoxes = document.querySelectorAll('.order-box');

    function filterOrders() {
        const orderIdValue = orderIdFilter.value.toLowerCase().replace('#', '').trim();
        const dateValue = dateFilter.value; // Format: YYYY-MM-DD

        orderBoxes.forEach(box => {
            // --- Order ID Matching ---
            const orderNumberEl = box.querySelector('.order-number');
            const cardOrderId = orderNumberEl ? orderNumberEl.textContent.toLowerCase().replace('#', '').trim() : '';

            // --- Date Matching ---
            const orderTimeEl = box.querySelector('.order-time');
            let cardDate = '';
            if (orderTimeEl) {
                const cardDateText = orderTimeEl.textContent.trim(); // Format: dd/mm/YYYY H:i
                // Extract date part (dd/mm/YYYY) and convert to YYYY-mm-dd
                const dateParts = cardDateText.split(' ')[0].split('/');
                if (dateParts.length === 3) {
                    cardDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                }
            }

            const idMatch = cardOrderId.includes(orderIdValue);
            const dateMatch = !dateValue || cardDate === dateValue;

            if (idMatch && dateMatch) {
                box.style.display = ''; // Show the order box
            } else {
                box.style.display = 'none'; // Hide the order box
            }
        });
    }

    if (orderIdFilter) {
        orderIdFilter.addEventListener('input', filterOrders);
    }

    if (dateFilter) {
        dateFilter.addEventListener('input', filterOrders);
    }
});

