function openModal(dish = null) {
    const modal = document.getElementById('dishModal');
    const form = document.getElementById('dishForm');
    const title = document.getElementById('modalTitle');
    const dishIdInput = document.getElementById('dish_id');
    const nameInput = document.getElementById('name');
    const priceInput = document.getElementById('price');
    const descriptionInput = document.getElementById('description');
    const categoryInput = document.getElementById('category_id_form');
    const statusInput = document.getElementById('status_form');
    const modalMessage = document.getElementById('modal-message');

    // Reset form and message
    form.reset();
    dishIdInput.value = 0;
    title.textContent = 'Thêm món ăn';
    modalMessage.innerHTML = '';

    if (dish && typeof dish === 'object') {
        title.textContent = 'Sửa món ăn';
        dishIdInput.value = dish.id || 0;
        nameInput.value = dish.name || '';
        priceInput.value = dish.price || 0;
        descriptionInput.value = dish.description || '';
        categoryInput.value = dish.category_id || '';
        statusInput.value = dish.status || 'Active';
    }

    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('dishModal');
    modal.style.display = 'none';
    document.getElementById('dishForm').reset();
    document.getElementById('dish_id').value = 0;
    document.getElementById('modal-message').innerHTML = '';
}

function confirmDelete(id) {
    if (confirm('Bạn có chắc muốn xóa món ăn này?')) {
        window.location.href = `dish_manage.php?action=delete&id=${id}`;
    }
}

function applyFilters() {
    const categoryId = document.getElementById('category_id').value;
    const priceRange = document.getElementById('price_range').value;
    const status = document.getElementById('status').value;
    const search = document.getElementById('search').value;

    const url = new URL(window.location);
    url.searchParams.set('category_id', categoryId);
    url.searchParams.set('price_range', priceRange);
    url.searchParams.set('status', status);
    url.searchParams.set('search', search);
    url.searchParams.set('page', '1');
    window.location = url;
}

function refreshTable() {
    const url = new URL(window.location);
    fetch(`dish_manage.php?${url.searchParams.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTbody = doc.querySelector('#dish-tbody');
        const newPagination = doc.querySelector('.pagination');
        document.querySelector('#dish-tbody').innerHTML = newTbody.innerHTML;
        document.querySelector('.pagination').innerHTML = newPagination.innerHTML;
    })
    .catch(error => {
        console.error('Error refreshing table:', error);
        showMessage('Lỗi tải lại bảng món ăn.', 'error');
    });
}

function showMessage(message, type) {
    const messageArea = document.getElementById('message-area');
    const modalMessage = document.getElementById('modal-message');
    const messageDiv = document.createElement('div');
    messageDiv.className = type === 'error' ? 'error-message' : 'success-message';
    messageDiv.textContent = message;
    messageArea.innerHTML = '';
    messageArea.appendChild(messageDiv);
    modalMessage.innerHTML = messageDiv.outerHTML;
}

document.getElementById('dishForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const saveButton = form.querySelector('.save-btn');
    saveButton.disabled = true;

    fetch('/Restaurant_PHP/ajax_dish_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        saveButton.disabled = false;
        if (data.success) {
            showMessage(data.message, 'success');
            closeModal();
            refreshTable();
        } else {
            showMessage(data.errors.join('<br>'), 'error');
        }
    })
    .catch(error => {
        saveButton.disabled = false;
        showMessage('Lỗi hệ thống: ' + error.message, 'error');
        console.error('AJAX error:', error);
    });
});

window.onclick = function(event) {
    const modal = document.getElementById('dishModal');
    if (event.target === modal) {
        closeModal();
    }
}