<?php
$pageTitle = $pageTitle ?? 'Đăng nhập';
$old = $old ?? [];
$errors = $errors ?? [];
$flashError = get_flash('error');
$field = static function (string $key, string $default = '') use ($old): string {
    return htmlspecialchars((string) ($old[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};
$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> - ICONVINA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?></style>
</head>
<body class="bg-light">
    <section class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="erp-card p-4 p-xl-5">
                        <div class="text-uppercase small fw-semibold text-secondary mb-2">ICONVINA ERP</div>
                        <h1 class="h3 fw-bold mb-2">Đăng nhập</h1>
                        <div class="text-secondary mb-4">Môi trường test người dùng nội bộ.</div>

                        <?php if ($flashError): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <?php if ($errorFor('auth')): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars((string) $errorFor('auth'), ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars(app_url('/login'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Tên đăng nhập</label>
                                <input type="text" name="username" class="form-control rounded-4 <?php echo $errorFor('username') ? 'is-invalid' : ''; ?>" value="<?php echo $field('username'); ?>" maxlength="80" autofocus>
                                <?php if ($errorFor('username')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('username'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mật khẩu</label>
                                <input type="password" name="password" class="form-control rounded-4 <?php echo $errorFor('password') ? 'is-invalid' : ''; ?>" maxlength="255">
                                <?php if ($errorFor('password')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('password'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-dark rounded-4 py-3">Đăng nhập</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
