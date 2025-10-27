<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Thiết lập các biến mặc định
$page_title = $page_title ?? 'CTUT Restaurant';
$page_css = $page_css ?? [];
$page_js = $page_js ?? [];
$show_background_overlay = $show_background_overlay ?? false;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Header CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header.css?v=<?php echo time(); ?>">

    <!-- Page specific CSS -->
    <?php foreach ($page_css as $css): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL . $css; ?>?v=<?php echo time(); ?>">
    <?php endforeach; ?>
</head>

<body>
    <?php if ($show_background_overlay): ?>
        <div class="background-overlay"></div>
    <?php endif; ?>

    <?php
    try {
        include 'templates/header.php';
        echo "<!-- Header loaded successfully -->";
    } catch (Exception $e) {
        echo "<p>Lỗi tải header: " . htmlspecialchars($e->getMessage()) . "</p>";
        error_log("Header error: " . $e->getMessage());
    }
    ?>

    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <?php
    // Include footer if exists
    if (file_exists('templates/footer.php')) {
        try {
            include 'templates/footer.php';
            echo "<!-- Footer loaded successfully -->";
        } catch (Exception $e) {
            echo "<p>Lỗi tải footer: " . htmlspecialchars($e->getMessage()) . "</p>";
            error_log("Footer error: " . $e->getMessage());
        }
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Page specific JS -->
    <?php foreach ($page_js as $js): ?>
        <script src="<?php echo BASE_URL . $js; ?>"></script>
    <?php endforeach; ?>
</body>

</html>