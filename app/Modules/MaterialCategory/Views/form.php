<?php
$activeSidebar = $activeSidebar ?? 'material-categories';
$pageTitle = $pageTitle ?? 'Biểu mẫu danh mục nguyên vật liệu';
$pageEyebrow = $pageEyebrow ?? 'Quản lý danh mục nguyên vật liệu';
$formAction = $formAction ?? '/material-categories/store';
$category = $category ?? [];
$parentOptions = $parentOptions ?? [];
$errors = $errors ?? [];
$field = static function (string $key, string $default = '') use ($category): string {
    return htmlspecialchars((string) ($category[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};
$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};
$selectedParentId = (string) ($category['parent_id'] ?? '');
$isActive = (string) ($category['is_active'] ?? '1');
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Kho</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/material-categories'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" class="row g-4">
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Mã</label>
                            <input type="text" name="code" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30">
                            <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">Tên danh mục</label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-4 <?php echo $errorFor('name') ? 'is-invalid' : ''; ?>" value="<?php echo $field('name'); ?>" maxlength="120">
                            <?php if ($errorFor('name')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">Danh mục cha</label>
                            <select name="parent_id" class="form-select rounded-4 <?php echo $errorFor('parent_id') ? 'is-invalid' : ''; ?>">
                                <option value="">Không có</option>
                                <?php foreach ($parentOptions as $option): ?>
                                    <option value="<?php echo (int) $option['id']; ?>" <?php echo $selectedParentId === (string) $option['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string) $option['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($errorFor('parent_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('parent_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="is_active" class="form-select rounded-4">
                                <option value="1" <?php echo $isActive === '1' ? 'selected' : ''; ?>>Đang dùng</option>
                                <option value="0" <?php echo $isActive === '0' ? 'selected' : ''; ?>>Ngừng dùng</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/material-categories'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Lưu</button>
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
