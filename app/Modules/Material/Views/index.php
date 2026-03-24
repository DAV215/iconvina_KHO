<?php
$activeSidebar = $activeSidebar ?? 'materials';
$pageTitle = $pageTitle ?? 'Nguyên vật liệu';
$pageEyebrow = $pageEyebrow ?? 'Quản lý nguyên vật liệu';
$filters = $filters ?? [];
$sort = $sort ?? [];
$materials = $materials ?? [];
$categoryOptions = $categoryOptions ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$filterValue = static function (string $key) use ($filters): string {
    return htmlspecialchars((string) ($filters[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};
$sortBy = (string) ($sort['by'] ?? '');
$sortDir = strtolower((string) ($sort['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
$buildSortUrl = static function (string $column) use ($filters, $sortBy, $sortDir, $perPage): string {
    $nextDir = $sortBy === $column && $sortDir === 'asc' ? 'desc' : 'asc';
    $query = array_filter([
        'code' => (string) ($filters['code'] ?? ''),
        'name' => (string) ($filters['name'] ?? ''),
        'category_id' => (string) ($filters['category_id'] ?? ''),
        'color' => (string) ($filters['color'] ?? ''),
        'status' => (string) ($filters['status'] ?? ''),
        'sort' => $column,
        'dir' => $nextDir,
        'per_page' => $perPage,
    ], static fn ($value): bool => $value !== '');
    return app_url('/materials' . ($query === [] ? '' : '?' . http_build_query($query)));
};
$sortIcon = static function (string $column) use ($sortBy, $sortDir): string {
    if ($sortBy !== $column) {
        return 'bi-arrow-down-up';
    }
    return $sortDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down';
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Kho vật tư</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách nguyên vật liệu</h3>
                            <div class="erp-inline-note">Thiết kế gọn, nhẹ mắt, ưu tiên phân tách bằng spacing và trạng thái trực quan.</div>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#materialFilterCollapse" aria-expanded="false" aria-controls="materialFilterCollapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-layout-three-columns"></i>Hiển thị cột
                                </button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="materialsTable">
                                    <?php foreach ([
                                        'code' => 'Mã',
                                        'name' => 'Tên vật tư',
                                        'category' => 'Danh mục',
                                        'specification' => 'Quy cách',
                                        'color' => 'Màu',
                                        'unit' => 'Đơn vị',
                                        'standard_cost' => 'Giá chuẩn',
                                        'min_stock' => 'Tồn tối thiểu',
                                        'status' => 'Trạng thái',
                                        'updated_at' => 'Cập nhật',
                                    ] as $columnKey => $columnLabel): ?>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($columnKey, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                            <span class="form-check-label"><?php echo htmlspecialchars($columnLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars(app_url('/materials/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm nguyên vật liệu</a>
                        </div>
                    </div>

                    <div class="collapse mb-4" id="materialFilterCollapse" data-filter-collapse="materials">
                        <form method="get" action="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="p-3 p-lg-3 erp-surface-soft rounded-4 border">
                            <div class="erp-filter-grid">
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Mã</label>
                                    <input type="text" name="code" value="<?php echo $filterValue('code'); ?>" class="form-control erp-field" placeholder="VD: VT-001">
                                </div>
                                <div style="grid-column: span 3;">
                                    <label class="form-label fw-semibold">Tên vật tư</label>
                                    <input type="text" name="name" value="<?php echo $filterValue('name'); ?>" class="form-control erp-field" placeholder="Nhập tên vật tư">
                                </div>
                                <div style="grid-column: span 3;">
                                    <label class="form-label fw-semibold">Danh mục</label>
                                    <select name="category_id" class="form-select erp-select">
                                        <option value="">Tất cả danh mục</option>
                                        <?php foreach ($categoryOptions as $category): ?>
                                            <option value="<?php echo (int) $category['id']; ?>" <?php echo (string) ($filters['category_id'] ?? '') === (string) $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) ($category['label'] ?? $category['name']), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Màu</label>
                                    <input type="text" name="color" value="<?php echo $filterValue('color'); ?>" class="form-control erp-field" placeholder="Nhập màu sắc">
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Trạng thái</label>
                                    <select name="status" class="form-select erp-select">
                                        <option value="">Tất cả</option>
                                        <option value="1" <?php echo (string) ($filters['status'] ?? '') === '1' ? 'selected' : ''; ?>>Đang dùng</option>
                                        <option value="0" <?php echo (string) ($filters['status'] ?? '') === '0' ? 'selected' : ''; ?>>Ngừng dùng</option>
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Hiển thị</label>
                                    <select name="per_page" class="form-select erp-select">
                                        <?php foreach ([10, 25, 50, 100] as $size): ?>
                                            <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($sortDir, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
                                <a href="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-funnel"></i>Áp dụng bộ lọc</button>
                            </div>
                        </form>
                    </div>

                    <div class="erp-table-shell p-2">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="materialsTable">
                                <thead>
                                    <tr>
                                        <th data-col="code"><a href="<?php echo htmlspecialchars($buildSortUrl('code'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Mã <i class="bi <?php echo $sortIcon('code'); ?>"></i></a></th>
                                        <th data-col="name"><a href="<?php echo htmlspecialchars($buildSortUrl('name'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Tên vật tư <i class="bi <?php echo $sortIcon('name'); ?>"></i></a></th>
                                        <th data-col="category"><a href="<?php echo htmlspecialchars($buildSortUrl('category'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Danh mục <i class="bi <?php echo $sortIcon('category'); ?>"></i></a></th>
                                        <th data-col="specification">Quy cách</th>
                                        <th data-col="color">Màu</th>
                                        <th data-col="unit"><a href="<?php echo htmlspecialchars($buildSortUrl('unit'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Đơn vị <i class="bi <?php echo $sortIcon('unit'); ?>"></i></a></th>
                                        <th data-col="standard_cost" class="text-end"><a href="<?php echo htmlspecialchars($buildSortUrl('standard_cost'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Giá chuẩn <i class="bi <?php echo $sortIcon('standard_cost'); ?>"></i></a></th>
                                        <th data-col="min_stock" class="text-end"><a href="<?php echo htmlspecialchars($buildSortUrl('min_stock'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Tồn tối thiểu <i class="bi <?php echo $sortIcon('min_stock'); ?>"></i></a></th>
                                        <th data-col="status" class="text-center">Trạng thái</th>
                                        <th data-col="updated_at">Cập nhật</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($materials === []): ?>
                                    <tr><td colspan="11" class="text-center py-5 text-secondary">Không có nguyên vật liệu phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($materials as $material): ?>
                                        <tr class="erp-row-compact">
                                            <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $material['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="name" class="erp-cell-compact">
                                                <div class="fw-semibold text-truncate" style="max-width: 220px; font-size: 0.83rem;"><?php echo htmlspecialchars((string) $material['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php if (!empty($material['image_path'])): ?><div class="erp-cell-secondary text-truncate" style="max-width: 240px;"><?php echo htmlspecialchars((string) $material['image_path'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                            </td>
                                            <td data-col="category"><span class="erp-tree-chip"><?php echo htmlspecialchars((string) ($material['category_name'] ?? 'Chưa phân loại'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="specification" class="erp-cell-compact"><div class="text-truncate" style="max-width: 220px;"><?php echo htmlspecialchars((string) ($material['specification'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></td>
                                            <td data-col="color"><?php echo htmlspecialchars((string) ($material['color'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="unit"><?php echo htmlspecialchars((string) $material['unit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="standard_cost" class="text-end fw-semibold"><?php echo number_format((float) $material['standard_cost'], 2); ?></td>
                                            <td data-col="min_stock" class="text-end"><?php echo number_format((float) $material['min_stock'], 2); ?></td>
                                            <td data-col="status" class="text-center"><span class="erp-status-badge <?php echo (int) $material['is_active'] === 1 ? 'is-active' : 'is-inactive'; ?>"><?php echo (int) $material['is_active'] === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?></span></td>
                                            <td data-col="updated_at"><?php echo htmlspecialchars((string) ($material['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/materials/show?id=' . (int) $material['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/materials/duplicate?id=' . (int) $material['id']), ENT_QUOTES, 'UTF-8'); ?>">Nhân bản</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/materials/edit?id=' . (int) $material['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                        <li><form method="post" action="<?php echo htmlspecialchars(app_url('/materials/delete?id=' . (int) $material['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa nguyên vật liệu này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php include base_path('app/Modules/Home/Views/partials/list_pagination.php'); ?>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars(app_url('/assets/js/erp-list.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
