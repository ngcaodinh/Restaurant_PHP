<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Quản lý món ăn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
</head>
<body>
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <?php
                $sidebarPath = dirname(dirname(dirname(__DIR__))) . '/templates/sidebar_admin.php';
                if (file_exists($sidebarPath)) {
                    include $sidebarPath;
                }
                ?>
            </div>
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-utensils"></i> Quản lý món ăn</h2>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDishModal">
                            <i class="fas fa-plus"></i> Thêm món ăn
                        </button>
                        <span class="badge bg-primary ms-2">Tổng: <?php echo $dishStats['total_dishes'] ?? 0; ?></span>
                        <span class="badge bg-success">Có sẵn: <?php echo $dishStats['available_dishes'] ?? 0; ?></span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên món</th>
                                        <th>Giá</th>
                                        <th>Danh mục</th>
                                        <th>Trạng thái</th>
                                        <th>Lượt bán</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dishes as $dish): ?>
                                    <tr>
                                        <td><?php echo $dish['id']; ?></td>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($dish['image'] ?: 'https://via.placeholder.com/50x50?text=No+Image'); ?>" 
                                                 alt="<?php echo htmlspecialchars($dish['name']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                        </td>
                                        <td><?php echo htmlspecialchars($dish['name']); ?></td>
                                        <td><?php echo number_format($dish['price'], 0, ',', '.'); ?>đ</td>
                                        <td><?php echo htmlspecialchars($dish['category_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $dish['status'] === 'Available' ? 'success' : 'secondary'; ?>">
                                                <?php echo $dish['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $dish['sales_count']; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="editDish(<?php echo htmlspecialchars(json_encode($dish)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="deleteDish(<?php echo $dish['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
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

    <!-- Add Dish Modal -->
    <div class="modal fade" id="addDishModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm món ăn mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addDishForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_name" class="form-label">Tên món ăn</label>
                                    <input type="text" class="form-control" id="add_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_price" class="form-label">Giá</label>
                                    <input type="number" class="form-control" id="add_price" name="price" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="add_description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_category" class="form-label">Danh mục</label>
                                    <select class="form-control" id="add_category" name="category_id">
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_status" class="form-label">Trạng thái</label>
                                    <select class="form-control" id="add_status" name="status">
                                        <option value="Available">Available</option>
                                        <option value="Unavailable">Unavailable</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="add_image" class="form-label">URL hình ảnh</label>
                            <input type="url" class="form-control" id="add_image" name="image">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="addDish()">Thêm món ăn</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Dish Modal -->
    <div class="modal fade" id="editDishModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa món ăn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editDishForm">
                        <input type="hidden" id="edit_dish_id" name="dish_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Tên món ăn</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_price" class="form-label">Giá</label>
                                    <input type="number" class="form-control" id="edit_price" name="price" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Danh mục</label>
                                    <select class="form-control" id="edit_category" name="category_id">
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Trạng thái</label>
                                    <select class="form-control" id="edit_status" name="status">
                                        <option value="Available">Available</option>
                                        <option value="Unavailable">Unavailable</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">URL hình ảnh</label>
                            <input type="url" class="form-control" id="edit_image" name="image">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateDish()">Cập nhật</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    $footerPath = dirname(dirname(dirname(__DIR__))) . '/templates/footer.php';
    if (file_exists($footerPath)) {
        require_once $footerPath;
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addDish() {
            const form = document.getElementById('addDishForm');
            const formData = new FormData(form);
            
            fetch('/api/admin/dish/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }

        function editDish(dish) {
            document.getElementById('edit_dish_id').value = dish.id;
            document.getElementById('edit_name').value = dish.name;
            document.getElementById('edit_price').value = dish.price;
            document.getElementById('edit_description').value = dish.description || '';
            document.getElementById('edit_category').value = dish.category_id || '';
            document.getElementById('edit_status').value = dish.status;
            document.getElementById('edit_image').value = dish.image || '';
            
            new bootstrap.Modal(document.getElementById('editDishModal')).show();
        }

        function updateDish() {
            const form = document.getElementById('editDishForm');
            const formData = new FormData(form);
            
            fetch('/api/admin/dish/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }

        function deleteDish(dishId) {
            if (!confirm('Bạn có chắc muốn xóa món ăn này?')) return;
            
            const formData = new FormData();
            formData.append('dish_id', dishId);
            
            fetch('/api/admin/dish/delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }
    </script>
</body>
</html>
