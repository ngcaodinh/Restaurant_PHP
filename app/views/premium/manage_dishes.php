<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Món ăn - Premium</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="<?php echo BASE_URL; ?>assets/css/material-dashboard/nucleo-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="<?php echo BASE_URL; ?>assets/css/material-dashboard/material-dashboard.min.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php require_once 'app/views/premium/sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="<?php echo BASE_URL; ?>premium/dashboard">Admin</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Món ăn</li>
                    </ol>
                    <h6 class="font-weight-bolder mb-0">Quản lý Món ăn</h6>
                </nav>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">Danh sách món ăn</h6>
                                <button class="btn bg-gradient-light me-3" data-bs-toggle="modal" data-bs-target="#addDishModal">Thêm món mới</button>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="px-3 pb-3">
                                <form id="filterForm" method="GET" action="">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="input-group input-group-static">
                                                <label for="filter_category" class="ms-0">Lọc theo danh mục</label>
                                                <select class="form-control" id="filter_category" name="category" onchange="this.form.submit()">
                                                    <option value="">Tất cả</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($category['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <br>
                                            <div class="input-group input-group-outline <?php echo !empty($_GET['search']) ? 'is-filled' : ''; ?>">
                                                <label class="form-label">Tìm kiếm theo tên...</label>
                                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 d-flex justify-content-end">
                                            <button type="submit" class="btn btn-dark mb-0 me-2">Tìm</button>
                                            <a href="<?php echo BASE_URL; ?>premium/dishes" class="btn btn-secondary mb-0">Bỏ lọc</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Món ăn</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Danh mục</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Giá</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dishes as $dish): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div>
                                                            <img src="<?php echo htmlspecialchars($dish['image'] ?: 'https://via.placeholder.com/100x100?text=No+Image'); ?>" class="avatar avatar-sm me-3 border-radius-lg" alt="<?php echo htmlspecialchars($dish['name']); ?>">
                                                        </div>
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($dish['name']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($dish['category_name'] ?? 'N/A'); ?></p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="badge badge-sm bg-gradient-<?php echo $dish['status'] === 'Available' ? 'success' : 'secondary'; ?>"><?php echo $dish['status'] === 'Available' ? 'Hiển thị' : 'Không hiển thị'; ?></span>
                                                </td>
                                                <td class="align-middle text-center"><span class="text-secondary text-xs font-weight-bold"><?php echo number_format($dish['price'], 0, ',', '.'); ?>đ</span></td>
                                                <td class="align-middle">
                                                    <a href="javascript:;" class="text-secondary font-weight-bold text-xs" onclick='editDish(<?php echo json_encode($dish); ?>)'>Sửa</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Modals -->
    <!-- Add Dish Modal -->
    <div class="modal fade" id="addDishModal" tabindex="-1" aria-labelledby="addDishModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addDishForm" onsubmit="addDish(event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDishModalLabel">Thêm món ăn mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group input-group-outline my-3">
                            <label class="form-label">Tên món ăn</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Giá</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                        <div class="input-group input-group-static mb-3">
                            <label>Mô tả</label>
                            <textarea class="form-control" name="description" rows="3" id="add_description"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="add_image_upload" class="form-label">Tải lên hình ảnh</label>
                            <input class="form-control" type="file" id="add_image_upload" accept="image/jpeg,image/png,image/gif">
                            <div id="add_image_preview" class="mt-2"></div>
                            <input type="hidden" name="image" id="add_image_url">
                        </div>
                        <div class="input-group input-group-static mb-3">
                            <label for="add_category" class="ms-0">Danh mục</label>
                            <select class="form-control" id="add_category" name="category_id">
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group input-group-static mb-3">
                            <label for="add_status" class="ms-0">Trạng thái</label>
                            <select class="form-control" id="add_status" name="status">
                                <option value="Available">Hiển thị</option>
                                <option value="Unavailable">Không hiển thị</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm món ăn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Dish Modal -->
    <div class="modal fade" id="editDishModal" tabindex="-1" aria-labelledby="editDishModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editDishForm" onsubmit="updateDish(event)">
                    <input type="hidden" id="edit_dish_id" name="dish_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDishModalLabel">Chỉnh sửa món ăn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group input-group-outline my-3 is-filled">
                            <label class="form-label">Tên món ăn</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="input-group input-group-outline mb-3 is-filled">
                            <label class="form-label">Giá</label>
                            <input type="number" class="form-control" id="edit_price" name="price" required>
                        </div>
                        <div class="input-group input-group-outline mb-3 is-filled">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>



                        <div class="mb-3">
                            <label class="form-label">Cập nhật hình ảnh</label>
                            <div id="edit_image_preview" class="mb-2"></div>
                            <input class="form-control" type="file" id="edit_image_upload" accept="image/jpeg,image/png,image/gif">
                            <input type="hidden" name="image" id="edit_image_url">
                        </div>
                        <div class="input-group input-group-static mb-3">
                            <label for="edit_category" class="ms-0">Danh mục</label>
                            <select class="form-control" id="edit_category" name="category_id">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group input-group-static mb-3">
                            <label for="edit_status" class="ms-0">Trạng thái</label>
                            <select class="form-control" id="edit_status" name="status">
                                <option value="Available">Hiển thị</option>
                                <option value="Unavailable">Không hiển thị</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/popper.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/plugins/perfect-scrollbar.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/plugins/bootstrap-notify.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/material-dashboard.min.js"></script>
    <script>
        const BASE_URL = '<?php echo rtrim(BASE_URL, '/'); ?>/';
        let addModal, editModal;

        function showNotification(message, type) {
            $.notify({
                icon: type === 'success' ? "done" : "warning",
                message: message
            }, {
                type: type,
                timer: 3000,
                placement: {
                    from: 'top',
                    align: 'right'
                }
            });
        }

        function handleImageUpload(event, previewContainerId, urlInputId) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('dish_image', file);

            const previewContainer = document.getElementById(previewContainerId);
            previewContainer.innerHTML = '<p class="text-muted">Đang tải ảnh lên...</p>';

            fetch(BASE_URL + 'api/dish_image_upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const imageUrl = data.image_url.startsWith('/') ? data.image_url : '/' + data.image_url;
                        previewContainer.innerHTML = `<img src="${BASE_URL}${imageUrl.substring(1)}" class="avatar avatar-xl me-3 border-radius-lg" alt="Image Preview">`;
                        document.getElementById(urlInputId).value = imageUrl;
                    } else {
                        previewContainer.innerHTML = `<p class="text-danger">Lỗi: ${data.message}</p>`;
                        showNotification(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Image upload error:', error);
                    previewContainer.innerHTML = '<p class="text-danger">Đã có lỗi xảy ra khi tải ảnh lên.</p>';
                    showNotification('Lỗi không xác định khi tải ảnh.', 'danger');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            addModal = new bootstrap.Modal(document.getElementById('addDishModal'));
            editModal = new bootstrap.Modal(document.getElementById('editDishModal'));

            const addImageUploadInput = document.getElementById('add_image_upload');
            if (addImageUploadInput) {
                addImageUploadInput.addEventListener('change', (event) => handleImageUpload(event, 'add_image_preview', 'add_image_url'));
            }

            const editImageUploadInput = document.getElementById('edit_image_upload');
            if (editImageUploadInput) {
                editImageUploadInput.addEventListener('change', (event) => handleImageUpload(event, 'edit_image_preview', 'edit_image_url'));
            }
        });

        function addDish(event) {
            event.preventDefault();
            const form = document.getElementById('addDishForm');
            const formData = new FormData(form);

            fetch(BASE_URL + 'api/admin/dish/create', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    $.notify({
                        icon: data.success ? "done" : "warning",
                        message: data.message
                    }, {
                        type: data.success ? 'success' : 'danger',
                        timer: 3000,
                        placement: {
                            from: 'top',
                            align: 'right'
                        }
                    });
                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function editDish(dish) {
            document.getElementById('edit_dish_id').value = dish.id;
            document.getElementById('edit_name').value = dish.name;
            document.getElementById('edit_price').value = dish.price;
            document.getElementById('edit_description').value = dish.description || '';
            document.getElementById('edit_category').value = dish.category_id || '';
            document.getElementById('edit_status').value = dish.status;

            const imageUrl = dish.image || '';
            document.getElementById('edit_image_url').value = imageUrl;
            const previewContainer = document.getElementById('edit_image_preview');
            if (imageUrl) {
                previewContainer.innerHTML = `<img src="${imageUrl}" class="avatar avatar-xl me-3 border-radius-lg" alt="Image Preview">`;
            } else {
                previewContainer.innerHTML = '<p class="text-muted">Chưa có ảnh.</p>';
            }

            editModal.show();
        }

        function updateDish(event) {
            event.preventDefault();
            const form = document.getElementById('editDishForm');
            const formData = new FormData(form);

            fetch(BASE_URL + 'api/admin/dish/update', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    $.notify({
                        icon: data.success ? "done" : "warning",
                        message: data.message
                    }, {
                        type: data.success ? 'success' : 'danger',
                        timer: 3000,
                        placement: {
                            from: 'top',
                            align: 'right'
                        }
                    });
                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>
