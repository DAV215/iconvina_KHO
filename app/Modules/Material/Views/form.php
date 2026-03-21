<?php
$activeSidebar = $activeSidebar ?? 'materials';
$pageTitle = $pageTitle ?? 'Biểu mẫu nguyên vật liệu';
$pageEyebrow = $pageEyebrow ?? 'Quản lý nguyên vật liệu';
$formAction = $formAction ?? '/materials/store';
$material = $material ?? [];
$categoryOptions = $categoryOptions ?? [];
$errors = $errors ?? [];
$field = static function (string $key, string $default = '') use ($material): string {
    return htmlspecialchars((string) ($material[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};
$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};
$selectedCategoryId = (string) ($material['category_id'] ?? '');
$isActive = (string) ($material['is_active'] ?? '1');
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
                        <a href="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" class="row g-4">
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Mã</label>
                            <input type="text" name="code" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30">
                            <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">Tên vật tư</label>
                            <input type="text" name="name" class="form-control form-control-lg rounded-4 <?php echo $errorFor('name') ? 'is-invalid' : ''; ?>" value="<?php echo $field('name'); ?>" maxlength="190">
                            <?php if ($errorFor('name')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category_id" class="form-select rounded-4 <?php echo $errorFor('category_id') ? 'is-invalid' : ''; ?>">
                                <option value="">Chưa phân loại</option>
                                <?php foreach ($categoryOptions as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $selectedCategoryId === (string) $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string) $category['code'] . ' - ' . (string) $category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($errorFor('category_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('category_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Đơn vị</label>
                            <input type="text" name="unit" class="form-control rounded-4 <?php echo $errorFor('unit') ? 'is-invalid' : ''; ?>" value="<?php echo $field('unit'); ?>" maxlength="50">
                            <?php if ($errorFor('unit')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('unit'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Giá chuẩn</label>
                            <input type="number" step="0.01" min="0" name="standard_cost" class="form-control rounded-4 <?php echo $errorFor('standard_cost') ? 'is-invalid' : ''; ?>" value="<?php echo $field('standard_cost', '0.00'); ?>">
                            <?php if ($errorFor('standard_cost')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('standard_cost'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Tồn tối thiểu</label>
                            <input type="number" step="0.01" min="0" name="min_stock" class="form-control rounded-4 <?php echo $errorFor('min_stock') ? 'is-invalid' : ''; ?>" value="<?php echo $field('min_stock', '0.00'); ?>">
                            <?php if ($errorFor('min_stock')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('min_stock'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Màu sắc</label>
                            <input type="text" name="color" class="form-control rounded-4 <?php echo $errorFor('color') ? 'is-invalid' : ''; ?>" value="<?php echo $field('color'); ?>" maxlength="100">
                            <?php if ($errorFor('color')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('color'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">Quy cách / kích thước</label>
                            <input type="text" name="specification" class="form-control rounded-4 <?php echo $errorFor('specification') ? 'is-invalid' : ''; ?>" value="<?php echo $field('specification'); ?>" maxlength="255" placeholder="Ví dụ: Tấm mica 1220x2440x3mm">
                            <?php if ($errorFor('specification')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('specification'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="is_active" class="form-select rounded-4">
                                <option value="1" <?php echo $isActive === '1' ? 'selected' : ''; ?>>Đang dùng</option>
                                <option value="0" <?php echo $isActive === '0' ? 'selected' : ''; ?>>Ngừng dùng</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Hình ảnh</label>
                            <input type="text" name="image_path" class="form-control rounded-4 <?php echo $errorFor('image_path') ? 'is-invalid' : ''; ?>" value="<?php echo $field('image_path'); ?>" maxlength="255" placeholder="/uploads/materials/mica-trong-3mm.jpg hoặc URL ảnh">
                            <?php if ($errorFor('image_path')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('image_path'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="description" rows="4" class="form-control rounded-4 <?php echo $errorFor('description') ? 'is-invalid' : ''; ?>"><?php echo $field('description'); ?></textarea>
                            <?php if ($errorFor('description')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
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