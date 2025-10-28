<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTUT Restaurant - Sản phẩm yêu thích</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/favorites.css">

</head>

<body>
    <div class="background-overlay"></div>
    <?php include __DIR__ . '/../../templates/header.php'; ?>

    <div class="container-xxl px-3 mt-5">
        <div class="cart-section">
            <div class="cart-header">
                <h2><i class="fas fa-heart me-3"></i>Sản phẩm yêu thích của bạn</h2>
            </div>

            <div class="cart-items" id="favorite-items">
                <?php if (empty($favorite_items)): ?>
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-heart-broken empty-icon"></i>
                        <h3>Bạn chưa có sản phẩm yêu thích nào.</h3>
                        <p class="mb-4">Hãy khám phá thực đơn và thêm những món bạn thích nhé!</p>
                        <a href="index.php" class="btn btn-primary btn-view-menu">
                            <i class="fas fa-utensils me-2"></i>Xem thực đơn
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($favorite_items as $item): ?>
                        <div class="favorite-item" data-id="<?php echo $item['id']; ?>">
                            <form action="favorites.php" method="POST" class="remove-form">
                                <input type="hidden" name="action" value="remove_from_favorites">
                                <input type="hidden" name="dish_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-remove-favorite" title="Xóa khỏi danh sách yêu thích">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <div class="item-image-container">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/images/placeholder.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                            </div>
                            <div class="item-info">
                                <div>
                                    <h5 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <div class="item-price">
                                        <?php echo number_format($item['price'], 0, ',', '.') . 'đ'; ?>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-add-to-cart add-to-cart-btn" data-dish-id="<?php echo $item['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/favorites.js"></script>
</body>

</html>
