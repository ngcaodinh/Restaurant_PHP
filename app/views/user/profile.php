<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Thông tin cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/profile.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    $headerPath = dirname(dirname(dirname(__DIR__))) . '/templates/header.php';
    if (file_exists($headerPath)) {
        require_once $headerPath;
    }
    $avatar = !empty($user['avatar_url']) ? $user['avatar_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=random&size=120';
    ?>

    <div class="container profile-container">
        <form id="profileForm" method="POST" action="<?php echo BASE_URL; ?>user/profile" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <img id="avatar-preview" src="<?php echo $avatar; ?>" alt="Avatar">
                                <label for="avatar-upload" class="avatar-upload-label d-none"><i class="fas fa-camera"></i></label>
                                <input type="file" id="avatar-upload" name="avatar" accept="image/*" class="d-none">
                            </div>
                            <h3><?php echo htmlspecialchars($user['name'] ?? ''); ?></h3>
                            <p><?php echo htmlspecialchars($user['role'] ?? ''); ?></p>
                        </div>
                        <div class="profile-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Thông tin chi tiết</h5>
                                <button type="button" id="edit-profile-btn" class="btn btn-outline-primary"><i class="fas fa-pencil-alt"></i> Chỉnh sửa thông tin</button>
                            </div>
                            <fieldset id="profile-fieldset" disabled>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Họ và tên</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Địa chỉ</label>
                                        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['status'] ?? ''); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ngày tham gia</label>
                                        <input type="text" class="form-control" value="<?php echo date('d/m/Y', strtotime($user['created_at'] ?? 'now')); ?>" readonly>
                                    </div>
                                </div>
                            </fieldset>
                            <div id="form-actions" class="d-flex justify-content-end gap-2 mt-4 d-none">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
                                <button type="button" id="cancel-edit-btn" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</button>
                                <button type="button" class="btn btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="fas fa-key"></i> Đổi mật khẩu</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đổi mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <input type="text" autocomplete="username" class="d-none" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="new_password" name="new_password" required autocomplete="new-password">
                                <i class="fas fa-eye password-toggle-icon" data-target="new_password"></i>
                            </div>
                            <div class="password-strength-container">
                                <div id="password-strength-bar" class="password-strength-bar"></div>
                            </div>
                            <div id="password-strength-text" class="password-strength-text"></div>
                            <ul id="password-requirements" class="password-requirements">
                                <li id="req-length">Ít nhất 8 ký tự</li>
                                <li id="req-uppercase">Chứa ít nhất 1 chữ in hoa</li>
                                <li id="req-lowercase">Chứa ít nhất 1 chữ thường</li>
                                <li id="req-number">Chứa ít nhất 1 số</li>
                                <li id="req-special">Chứa ít nhất 1 ký tự đặc biệt</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                                <i class="fas fa-eye password-toggle-icon" data-target="confirm_password"></i>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="changePassword()">Lưu thay đổi</button>
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
    <script src="<?php echo BASE_URL; ?>assets/js/profile.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('edit-profile-btn');
            const cancelBtn = document.getElementById('cancel-edit-btn');
            const fieldset = document.getElementById('profile-fieldset');
            const formActions = document.getElementById('form-actions');
            const avatarUploadLabel = document.querySelector('.avatar-upload-label');
            const avatarUploadInput = document.getElementById('avatar-upload');
            const avatarPreview = document.getElementById('avatar-preview');

            const form = document.getElementById('profileForm');
            const inputs = form.querySelectorAll('input:not([type="file"])');
            const originalValues = new Map();
            inputs.forEach(input => {
                originalValues.set(input.id, input.value);
            });

            editBtn.addEventListener('click', function() {
                fieldset.disabled = false;
                formActions.classList.remove('d-none');
                avatarUploadLabel.classList.remove('d-none');
                this.classList.add('d-none');
            });

            cancelBtn.addEventListener('click', function() {
                fieldset.disabled = true;
                formActions.classList.add('d-none');
                avatarUploadLabel.classList.add('d-none');
                editBtn.classList.remove('d-none');

                // Restore original values
                inputs.forEach(input => {
                    input.value = originalValues.get(input.id);
                });

                // Reset file input and preview
                avatarUploadInput.value = '';
                avatarPreview.src = '<?php echo $avatar; ?>';
            });

            avatarUploadInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) return;

                // Preview image
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);

                // Upload via AJAX
                const formData = new FormData();
                formData.append('avatar', file);

                fetch('<?php echo BASE_URL; ?>api/avatar_upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // Update avatar on header as well if it exists
                            const headerAvatar = document.getElementById('header-user-avatar');
                            if (headerAvatar) {
                                headerAvatar.src = data.avatar_url;
                            }
                        } else {
                            Swal.fire('Lỗi!', data.message, 'error');
                            // Revert preview if upload failed
                            avatarPreview.src = '<?php echo $avatar; ?>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Lỗi!', 'Có lỗi xảy ra khi tải lên ảnh.', 'error');
                        avatarPreview.src = '<?php echo $avatar; ?>';
                    });
            });
        });

        <?php if (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: '<?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>',
            });
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: '<?php echo htmlspecialchars($success); ?>',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                if ('<?php echo $success; ?>' === 'Cập nhật thông tin thành công') {
                    location.reload();
                }
            });
        <?php endif; ?>

        function changePassword() {
            const form = document.getElementById('changePasswordForm');
            const formData = new FormData(form);

            fetch('<?php echo BASE_URL; ?>user/change-password', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Thành công!', data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                        form.reset();
                    } else {
                        Swal.fire('Lỗi!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Lỗi!', 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
                });
        }
    </script>
</body>

</html>