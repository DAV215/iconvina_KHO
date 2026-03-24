<?php
$activeSidebar = $activeSidebar ?? 'suppliers';
$pageTitle = $pageTitle ?? 'Nhà cung cấp';
$formAction = $formAction ?? app_url('/suppliers/store');
$supplier = $supplier ?? [];
$errors = $errors ?? [];

$field = static function (string $key, string $default = '') use ($supplier): string {
    return htmlspecialchars((string) ($supplier[$key] ?? $default), ENT_QUOTES, 'UTF-8');
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
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="py-4 py-xl-5">
            <div class="container-fluid px-4 px-xl-5">
                <div class="erp-card p-4 p-xl-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Nhà cung cấp</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/suppliers'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" class="row g-4">
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Mã NCC</label>
                            <input type="text" name="code" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30">
                            <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">Tên nhà cung cấp</label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-4 <?php echo $errorFor('name') ? 'is-invalid' : ''; ?>" value="<?php echo $field('name'); ?>" maxlength="190">
                            <?php if ($errorFor('name')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Người liên hệ</label>
                            <input type="text" name="contact_name" class="form-control rounded-4 <?php echo $errorFor('contact_name') ? 'is-invalid' : ''; ?>" value="<?php echo $field('contact_name'); ?>" maxlength="150">
                            <?php if ($errorFor('contact_name')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('contact_name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Điện thoại</label>
                            <input type="text" name="phone" class="form-control rounded-4 <?php echo $errorFor('phone') ? 'is-invalid' : ''; ?>" value="<?php echo $field('phone'); ?>" maxlength="30">
                            <?php if ($errorFor('phone')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('phone'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control rounded-4 <?php echo $errorFor('email') ? 'is-invalid' : ''; ?>" value="<?php echo $field('email'); ?>" maxlength="150">
                            <?php if ($errorFor('email')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('email'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Mã số thuế</label>
                            <input type="text" name="tax_code" class="form-control rounded-4 <?php echo $errorFor('tax_code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('tax_code'); ?>" maxlength="50">
                            <?php if ($errorFor('tax_code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('tax_code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="is_active" class="form-select rounded-4 <?php echo $errorFor('is_active') ? 'is-invalid' : ''; ?>">
                                <option value="1" <?php echo (string) ($supplier['is_active'] ?? '1') === '1' ? 'selected' : ''; ?>>Đang dùng</option>
                                <option value="0" <?php echo (string) ($supplier['is_active'] ?? '1') === '0' ? 'selected' : ''; ?>>Ngưng dùng</option>
                            </select>
                            <?php if ($errorFor('is_active')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('is_active'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ</label>
                            <textarea name="address" rows="3" class="form-control rounded-4 <?php echo $errorFor('address') ? 'is-invalid' : ''; ?>"><?php echo $field('address'); ?></textarea>
                            <?php if ($errorFor('address')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('address'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="note" rows="4" class="form-control rounded-4 <?php echo $errorFor('note') ? 'is-invalid' : ''; ?>"><?php echo $field('note'); ?></textarea>
                            <?php if ($errorFor('note')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('note'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/suppliers'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Lưu nhà cung cấp</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
