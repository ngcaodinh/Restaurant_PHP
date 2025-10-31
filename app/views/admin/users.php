<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Người dùng - Admin</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="<?php echo BASE_URL; ?>assets/css/material-dashboard/nucleo-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="<?php echo BASE_URL; ?>assets/css/material-dashboard/material-dashboard.min.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php require_once 'app/views/admin/sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="/admin/dashboard">Admin</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Người dùng</li>
                    </ol>
                    <h6 class="font-weight-bolder mb-0">Quản lý Người dùng</h6>
                </nav>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Danh sách người dùng</h6>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Người dùng</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Vai trò</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày tạo</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($user['name']); ?></h6>
                                                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0"><?php echo $user['role']; ?></p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="badge badge-sm bg-gradient-<?php echo $user['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo $user['status']; ?></span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="javascript:;" class="text-secondary font-weight-bold text-xs" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                                        Sửa
                                                    </a>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Chỉnh sửa người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="input-group input-group-outline my-3">
                            <label class="form-label">Tên</label>
                            <input type="text" class="form-control" id="edit_name" name="name">
                        </div>
                        <div class="input-group input-group-outline my-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="input-group input-group-outline my-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="input-group input-group-static mb-4">
                            <label for="edit_role" class="ms-0">Vai trò</label>
                            <select class="form-control" id="edit_role" name="role">
                                <option value="User">User</option>
                                <option value="PremiumUser">Premium User</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="input-group input-group-static mb-4">
                            <label for="edit_status" class="ms-0">Trạng thái</label>
                            <select class="form-control" id="edit_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser()">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/popper.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/material-dashboard/core/bootstrap.min.js"></script>
    <script>
        let editModal;
        document.addEventListener('DOMContentLoaded', function() {
            editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        });

        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            const nameInput = document.getElementById('edit_name');
            nameInput.value = user.name;
            nameInput.parentElement.classList.add('is-filled');

            const emailInput = document.getElementById('edit_email');
            emailInput.value = user.email || '';
            emailInput.parentElement.classList.add('is-filled');

            const phoneInput = document.getElementById('edit_phone');
            phoneInput.value = user.phone || '';
            phoneInput.parentElement.classList.add('is-filled');

            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;

            editModal.show();
        }

        function updateUser() {
            const form = document.getElementById('editUserForm');
            const formData = new FormData(form);

            fetch('/admin/updateUser', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>

</html>