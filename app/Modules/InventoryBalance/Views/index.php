<?php
$activeSidebar = $activeSidebar ?? 'inventory-balance';
$pageTitle = $pageTitle ?? 'Xem tồn kho';
$pageEyebrow = $pageEyebrow ?? 'Kho / Tồn kho';
$filters = $filters ?? [];
$sort = $sort ?? [];
$itemTypes = $itemTypes ?? [];
$stockStatuses = $stockStatuses ?? [];
$categoryOptions = $categoryOptions ?? [];
$balances = $balances ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$filterValue = static function (string $key) use ($filters): string {
    return htmlspecialchars((string) ($filters[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};
$sortBy = (string) ($sort['by'] ?? 'code');
$sortDir = strtolower((string) ($sort['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
$buildSortUrl = static function (string $column) use ($filters, $sortBy, $sortDir, $perPage): string {
    $nextDir = $sortBy === $column && $sortDir === 'asc' ? 'desc' : 'asc';
    $query = array_filter([
        'item_type' => (string) ($filters['item_type'] ?? ''),
        'code' => (string) ($filters['code'] ?? ''),
        'name' => (string) ($filters['name'] ?? ''),
        'category_id' => (string) ($filters['category_id'] ?? ''),
        'stock_status' => (string) ($filters['stock_status'] ?? ''),
        'is_active' => (string) ($filters['is_active'] ?? ''),
        'sort' => $column,
        'dir' => $nextDir,
        'per_page' => $perPage,
    ], static fn ($value): bool => $value !== '');

    return app_url('/inventory/balance' . ($query === [] ? '' : '?' . http_build_query($query)));
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
    <style>
        body { font-size: 13px; }
        .inventory-balance-card .erp-toolbar {
            position: sticky;
            top: 72px;
            z-index: 15;
            background: #fff;
            padding-bottom: .75rem;
        }
        .inventory-balance-table td,
        .inventory-balance-table th {
            white-space: nowrap;
        }
        .inventory-balance-name {
            max-width: 240px;
        }
        .erp-status-badge.is-pending {
            background: #fff7ed;
            color: #b45309;
        }
    </style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <div class="erp-card p-3 p-lg-4 p-xl-5 inventory-balance-card">
                    <div class="erp-toolbar mb-4">
                        <div class="erp-toolbar__meta">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2"><?php echo htmlspecialchars($pageEyebrow, ENT_QUOTES, 'UTF-8'); ?></div>
                            <h3 class="h4 fw-bold mb-1">Tồn kho hiện tại</h3>
                            <div class="erp-inline-note">Theo dõi số lượng tồn, giá trị tồn và cảnh báo tồn thấp cho vật tư và bán thành phẩm.</div>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#inventoryBalanceFilterCollapse" aria-expanded="true" aria-controls="inventoryBalanceFilterCollapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-layout-three-columns"></i>Hiển thị cột
                                </button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="inventoryBalanceTable">
                                    <?php foreach ([
                                        'item_type' => 'Loại',
                                        'code' => 'Mã',
                                        'name' => 'Tên',
                                        'category' => 'Danh mục',
                                        'unit' => 'Đơn vị',
                                        'standard_cost' => 'Giá chuẩn',
                                        'current_qty' => 'Tồn hiện tại',
                                        'stock_value' => 'Giá trị tồn',
                                        'min_stock' => 'Tồn tối thiểu',
                                        'status' => 'Trạng thái tồn',
                                        'active' => 'Kích hoạt',
                                    ] as $columnKey => $columnLabel): ?>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($columnKey, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                            <span class="form-check-label"><?php echo htmlspecialchars($columnLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="collapse show mb-4" id="inventoryBalanceFilterCollapse" data-filter-collapse="inventory-balance">
                        <form method="get" action="<?php echo htmlspecialchars(app_url('/inventory/balance'), ENT_QUOTES, 'UTF-8'); ?>" class="p-3 p-lg-3 erp-surface-soft rounded-4 border">
                            <div class="erp-filter-grid">
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Loại item</label>
                                    <select name="item_type" class="form-select erp-select">
                                        <option value="">Tất cả</option>
                                        <?php foreach ($itemTypes as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($filters['item_type'] ?? '') === (string) $key ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Mã</label>
                                    <input type="text" name="code" value="<?php echo $filterValue('code'); ?>" class="form-control erp-field" placeholder="Nhập mã vật tư / bán thành phẩm">
                                </div>
                                <div style="grid-column: span 3;">
                                    <label class="form-label fw-semibold">Tên</label>
                                    <input type="text" name="name" value="<?php echo $filterValue('name'); ?>" class="form-control erp-field" placeholder="Nhập tên item">
                                </div>
                                <div style="grid-column: span 3;">
                                    <label class="form-label fw-semibold">Danh mục vật tư</label>
                                    <select name="category_id" class="form-select erp-select">
                                        <option value="">Tất cả danh mục</option>
                                        <?php foreach ($categoryOptions as $category): ?>
                                            <option value="<?php echo (int) ($category['id'] ?? 0); ?>" <?php echo (string) ($filters['category_id'] ?? '') === (string) ($category['id'] ?? '') ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) ($category['label'] ?? $category['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Trạng thái tồn</label>
                                    <select name="stock_status" class="form-select erp-select">
                                        <option value="">Tất cả</option>
                                        <?php foreach ($stockStatuses as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($filters['stock_status'] ?? '') === (string) $key ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="grid-column: span 2;">
                                    <label class="form-label fw-semibold">Kích hoạt</label>
                                    <select name="is_active" class="form-select erp-select">
                                        <option value="">Tất cả</option>
                                        <option value="1" <?php echo (string) ($filters['is_active'] ?? '') === '1' ? 'selected' : ''; ?>>Đang dùng</option>
                                        <option value="0" <?php echo (string) ($filters['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Ngừng dùng</option>
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
                                <a href="<?php echo htmlspecialchars(app_url('/inventory/balance'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-funnel"></i>Áp dụng bộ lọc</button>
                            </div>
                        </form>
                    </div>

                    <div class="erp-table-shell p-2">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle inventory-balance-table" id="inventoryBalanceTable">
                                <thead>
                                    <tr>
                                        <th data-col="item_type">Loại</th>
                                        <th data-col="code"><a href="<?php echo htmlspecialchars($buildSortUrl('code'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Mã <i class="bi <?php echo $sortIcon('code'); ?>"></i></a></th>
                                        <th data-col="name"><a href="<?php echo htmlspecialchars($buildSortUrl('name'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Tên <i class="bi <?php echo $sortIcon('name'); ?>"></i></a></th>
                                        <th data-col="category">Danh mục</th>
                                        <th data-col="unit">Đơn vị</th>
                                        <th data-col="standard_cost" class="text-end"><a href="<?php echo htmlspecialchars($buildSortUrl('standard_cost'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Giá chuẩn <i class="bi <?php echo $sortIcon('standard_cost'); ?>"></i></a></th>
                                        <th data-col="current_qty" class="text-end"><a href="<?php echo htmlspecialchars($buildSortUrl('current_qty'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Tồn hiện tại <i class="bi <?php echo $sortIcon('current_qty'); ?>"></i></a></th>
                                        <th data-col="stock_value" class="text-end"><a href="<?php echo htmlspecialchars($buildSortUrl('stock_value'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Giá trị tồn <i class="bi <?php echo $sortIcon('stock_value'); ?>"></i></a></th>
                                        <th data-col="min_stock" class="text-end"><a href="<?php echo htmlspecialchars($buildSortUrl('min_stock'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-th-link">Tồn tối thiểu <i class="bi <?php echo $sortIcon('min_stock'); ?>"></i></a></th>
                                        <th data-col="status" class="text-center">Trạng thái tồn</th>
                                        <th data-col="active" class="text-center">Kích hoạt</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($balances === []): ?>
                                    <tr><td colspan="12" class="text-center py-5 text-secondary">Không có dữ liệu tồn kho phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($balances as $balance): ?>
                                        <tr class="erp-row-compact">
                                            <td data-col="item_type"><span class="erp-tree-chip"><?php echo htmlspecialchars((string) ($balance['item_type_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) ($balance['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="name" class="erp-cell-compact">
                                                <div class="fw-semibold text-truncate inventory-balance-name"><?php echo htmlspecialchars((string) ($balance['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-col="category"><?php echo htmlspecialchars((string) ($balance['category_display'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="unit"><?php echo htmlspecialchars((string) ($balance['unit'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="standard_cost" class="text-end fw-semibold"><?php echo number_format((float) ($balance['standard_cost'] ?? 0), 2); ?></td>
                                            <td data-col="current_qty" class="text-end fw-semibold"><?php echo number_format((float) ($balance['current_qty'] ?? 0), 2); ?></td>
                                            <td data-col="stock_value" class="text-end fw-semibold"><?php echo number_format((float) ($balance['stock_value'] ?? 0), 2); ?></td>
                                            <td data-col="min_stock" class="text-end"><?php echo number_format((float) ($balance['min_stock'] ?? 0), 2); ?></td>
                                            <td data-col="status" class="text-center"><span class="erp-status-badge <?php echo htmlspecialchars((string) ($balance['stock_status_badge'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($balance['stock_status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="active" class="text-center"><span class="erp-status-badge <?php echo (int) ($balance['is_active'] ?? 0) === 1 ? 'is-active' : 'is-inactive'; ?>"><?php echo (int) ($balance['is_active'] ?? 0) === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?></span></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <?php if (($balance['detail_url'] ?? null) !== null): ?>
                                                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars((string) $balance['detail_url'], ENT_QUOTES, 'UTF-8'); ?>">Chi tiết item</a></li>
                                                        <?php endif; ?>
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>">Sổ giao dịch kho</a></li>
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
