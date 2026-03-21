<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICONVINA ERP Tổng quan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style><?php require __DIR__ . '/partials/theme.css'; ?></style>
</head>
<body>
    <div class="erp-shell d-flex">
        <?php $activeSidebar = 'dashboard'; include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="erp-main flex-grow-1">
            <?php
                $pageEyebrow = 'Tổng quan hệ thống';
                $pageTitle = 'Trung tâm điều hành ERP';
                include __DIR__ . '/partials/header.php';
            ?>
            <?php include __DIR__ . '/dashboard.php'; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>