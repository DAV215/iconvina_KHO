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
$imagePath = trim((string) ($material['image_path'] ?? ''));

$categoryById = [];
$childrenByParent = [];
foreach ($categoryOptions as $category) {
    $categoryById[(int) $category['id']] = $category;
    $parentKey = $category['parent_id'] === null ? 'root' : (string) $category['parent_id'];
    $childrenByParent[$parentKey][] = $category;
}

$selectedCategoryName = 'Chưa phân loại';
if ($selectedCategoryId !== '' && isset($categoryById[(int) $selectedCategoryId])) {
    $selectedCategoryName = (string) ($categoryById[(int) $selectedCategoryId]['label'] ?? $categoryById[(int) $selectedCategoryId]['name']);
}

$renderCategoryTree = static function (?int $parentId = null) use (&$renderCategoryTree, $childrenByParent, $selectedCategoryId): void {
    $key = $parentId === null ? 'root' : (string) $parentId;
    $items = $childrenByParent[$key] ?? [];
    if ($items === []) {
        return;
    }
    echo '<ul class="erp-tree-list">';
    foreach ($items as $category) {
        $id = (int) $category['id'];
        $label = (string) ($category['label'] ?? $category['name']);
        $hasChildren = isset($childrenByParent[(string) $id]) && $childrenByParent[(string) $id] !== [];
        $isSelected = $selectedCategoryId === (string) $id;
        echo '<li class="erp-tree-item">';
        echo '<div class="erp-tree-row">';
        if ($hasChildren) {
            echo '<button type="button" class="erp-tree-toggle" data-tree-toggle="' . $id . '" aria-expanded="true"><i class="bi bi-dash-lg"></i></button>';
        } else {
            echo '<span style="width:34px;min-width:34px;"></span>';
        }
        echo '<button type="button" class="erp-tree-card text-start ' . ($isSelected ? 'is-selected' : '') . '" data-category-select="' . $id . '" data-category-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">';
        echo '<div class="fw-semibold">' . htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div class="erp-tree-meta">' . htmlspecialchars((string) $category['code'], ENT_QUOTES, 'UTF-8') . '</div>';
        echo '</button>';
        echo '</div>';
        if ($hasChildren) {
            echo '<div class="erp-tree-children mt-2" data-tree-children="' . $id . '">';
            $renderCategoryTree($id);
            echo '</div>';
        }
        echo '</li>';
    }
    echo '</ul>';
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
        <section class="erp-page-section">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <div class="erp-card p-3 p-lg-4 p-xl-5">
                    <div class="erp-toolbar mb-4">
                        <div class="erp-toolbar__meta">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Biểu mẫu vật tư</div>
                            <h3 class="h4 fw-bold mb-1"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="erp-inline-note">Nhập dữ liệu vật tư, chọn danh mục dạng cây và kiểm tra ảnh trước khi lưu.</div>
                        </div>
                        <div class="erp-toolbar__actions">
                            <a href="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Quay lại danh sách</a>
                        </div>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" class="erp-form-grid">
                        <div style="grid-column: span 4;">
                            <label class="form-label fw-semibold">Mã vật tư</label>
                            <input type="text" name="code" class="form-control form-control-lg erp-field <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30">
                            <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div style="grid-column: span 8;">
                            <label class="form-label fw-semibold">Tên vật tư</label>
                            <input type="text" name="name" class="form-control form-control-lg erp-field <?php echo $errorFor('name') ? 'is-invalid' : ''; ?>" value="<?php echo $field('name'); ?>" maxlength="190">
                            <?php if ($errorFor('name')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>

                        <div style="grid-column: span 5;">
                            <div class="erp-section-panel p-3 p-lg-4 h-100">
                                <label class="form-label fw-semibold">Cây danh mục</label>
                                <input type="hidden" name="category_id" id="materialCategoryInput" value="<?php echo htmlspecialchars($selectedCategoryId, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="erp-inline-note mb-3">Đang chọn: <strong id="materialCategoryCurrent"><?php echo htmlspecialchars($selectedCategoryName, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                <div class="erp-tree-panel <?php echo $errorFor('category_id') ? 'border border-danger' : ''; ?>">
                                    <div class="mb-3">
                                        <button type="button" class="erp-tree-card text-start w-100 <?php echo $selectedCategoryId === '' ? 'is-selected' : ''; ?>" data-category-select="" data-category-label="Chưa phân loại">
                                            <div class="fw-semibold">Chưa phân loại</div>
                                            <div class="erp-tree-meta">Không gắn vào danh mục cụ thể</div>
                                        </button>
                                    </div>
                                    <?php $renderCategoryTree(); ?>
                                </div>
                                <?php if ($errorFor('category_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errorFor('category_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                            </div>
                        </div>

                        <div style="grid-column: span 7;">
                            <div class="erp-form-grid">
                                <div style="grid-column: span 6;">
                                    <label class="form-label fw-semibold">Đơn vị</label>
                                    <input type="text" name="unit" class="form-control erp-field <?php echo $errorFor('unit') ? 'is-invalid' : ''; ?>" value="<?php echo $field('unit'); ?>" maxlength="50">
                                    <?php if ($errorFor('unit')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('unit'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div style="grid-column: span 6;">
                                    <label class="form-label fw-semibold">Trạng thái</label>
                                    <select name="is_active" class="form-select erp-select">
                                        <option value="1" <?php echo $isActive === '1' ? 'selected' : ''; ?>>Đang dùng</option>
                                        <option value="0" <?php echo $isActive === '0' ? 'selected' : ''; ?>>Ngừng dùng</option>
                                    </select>
                                </div>
                                <div style="grid-column: span 4;">
                                    <label class="form-label fw-semibold">Giá chuẩn</label>
                                    <input type="number" step="0.01" min="0" name="standard_cost" class="form-control erp-field <?php echo $errorFor('standard_cost') ? 'is-invalid' : ''; ?>" value="<?php echo $field('standard_cost', '0.00'); ?>">
                                    <?php if ($errorFor('standard_cost')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('standard_cost'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div style="grid-column: span 4;">
                                    <label class="form-label fw-semibold">Tồn tối thiểu</label>
                                    <input type="number" step="0.01" min="0" name="min_stock" class="form-control erp-field <?php echo $errorFor('min_stock') ? 'is-invalid' : ''; ?>" value="<?php echo $field('min_stock', '0.00'); ?>">
                                    <?php if ($errorFor('min_stock')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('min_stock'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div style="grid-column: span 4;">
                                    <label class="form-label fw-semibold">Màu sắc</label>
                                    <input type="text" name="color" class="form-control erp-field <?php echo $errorFor('color') ? 'is-invalid' : ''; ?>" value="<?php echo $field('color'); ?>" maxlength="100">
                                    <?php if ($errorFor('color')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('color'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div style="grid-column: span 12;">
                                    <label class="form-label fw-semibold">Quy cách / kích thước</label>
                                    <input type="text" name="specification" class="form-control erp-field <?php echo $errorFor('specification') ? 'is-invalid' : ''; ?>" value="<?php echo $field('specification'); ?>" maxlength="255" placeholder="Ví dụ: Tấm mica 1220x2440x3mm">
                                    <?php if ($errorFor('specification')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('specification'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div style="grid-column: span 7;">
                            <label class="form-label fw-semibold">Đường dẫn hình ảnh</label>
                            <input type="text" name="image_path" id="materialImagePath" class="form-control erp-field <?php echo $errorFor('image_path') ? 'is-invalid' : ''; ?>" value="<?php echo $field('image_path'); ?>" maxlength="255" placeholder="/uploads/materials/vat-tu.jpg hoặc URL ảnh">
                            <?php if ($errorFor('image_path')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('image_path'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>
                        <div style="grid-column: span 5;">
                            <label class="form-label fw-semibold">Xem trước ảnh</label>
                            <div class="erp-image-preview" id="materialImagePreview">
                                <?php if ($imagePath !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="Xem trước hình ảnh vật tư">
                                <?php else: ?>
                                    <div class="text-center text-secondary">
                                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                                        Chưa có hình ảnh để xem trước
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div style="grid-column: span 12;">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="description" rows="5" class="form-control erp-textarea <?php echo $errorFor('description') ? 'is-invalid' : ''; ?>"><?php echo $field('description'); ?></textarea>
                            <?php if ($errorFor('description')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                        </div>

                        <div style="grid-column: span 12;">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Hủy</a>
                                <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-check2-circle"></i>Lưu nguyên vật liệu</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const input = document.getElementById('materialCategoryInput');
    const current = document.getElementById('materialCategoryCurrent');
    const optionButtons = Array.from(document.querySelectorAll('[data-category-select]'));
    const toggleButtons = Array.from(document.querySelectorAll('[data-tree-toggle]'));
    const imageInput = document.getElementById('materialImagePath');
    const imagePreview = document.getElementById('materialImagePreview');

    const updateSelection = (value, label) => {
        input.value = value;
        current.textContent = label;
        optionButtons.forEach((button) => {
            const isSelected = button.dataset.categorySelect === value;
            button.classList.toggle('is-selected', isSelected);
        });
    };

    const renderPreview = (value) => {
        const src = (value || '').trim();
        if (src === '') {
            imagePreview.innerHTML = '<div class="text-center text-secondary"><i class="bi bi-image fs-1 d-block mb-2"></i>Chưa có hình ảnh để xem trước</div>';
            return;
        }
        imagePreview.innerHTML = '<img src="' + src.replace(/"/g, '&quot;') + '" alt="Xem trước hình ảnh vật tư">';
    };

    optionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            updateSelection(button.dataset.categorySelect || '', button.dataset.categoryLabel || 'Chưa phân loại');
        });
    });

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.treeToggle;
            const container = document.querySelector('[data-tree-children="' + id + '"]');
            if (!container) {
                return;
            }
            const collapsed = container.classList.toggle('is-collapsed');
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            button.innerHTML = collapsed ? '<i class="bi bi-plus-lg"></i>' : '<i class="bi bi-dash-lg"></i>';
        });
    });

    if (imageInput) {
        imageInput.addEventListener('input', () => renderPreview(imageInput.value));
    }
})();
</script>
</body>
</html>
