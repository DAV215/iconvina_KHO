<?php
$activeSidebar = $activeSidebar ?? 'suppliers';
$pageTitle = $pageTitle ?? 'Nhà cung cấp';
$filters = $filters ?? ['search' => '', 'status' => ''];
$sort = $sort ?? ['by' => 'updated_at', 'dir' => 'DESC'];
$statuses = $statuses ?? [];
$sortOptions = $sortOptions ?? [];
$suppliers = $suppliers ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$statusBadgeMap = ['1' => 'success', '0' => 'secondary'];
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Nhà cung cấp</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách nhà cung cấp</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#supplierFilterCollapse" aria-expanded="true" aria-controls="supplierFilterCollapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-layout-three-columns"></i>Hiển thị cột
                                </button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="suppliersTable">
                                    <?php foreach ([
                                        'code' => 'Mã',
                                        'name' => 'Tên nhà cung cấp',
                                        'contact' => 'Người liên hệ',
                                        'phone' => 'Điện thoại',
                                        'email' => 'Email',
                                        'status' => 'Trạng thái',
                                    ] as $key => $label): ?>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                            <span class="form-check-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars(app_url('/suppliers/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm nhà cung cấp</a>
                        </div>
                    </div>

                    <div class="collapse show mb-4" id="supplierFilterCollapse" data-filter-collapse="suppliers">
                        <form method="get" action="<?php echo htmlspecialchars(app_url('/suppliers'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label fw-semibold">Tìm kiếm</label>
                                    <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars((string) $filters['search'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Mã, tên, liên hệ, điện thoại, email">
                                </div>
                                <div class="col-12 col-lg-2">
                                    <label class="form-label fw-semibold">Trạng thái</label>
                                    <select name="status" class="form-select erp-select">
                                        <option value="">Tất cả</option>
                                        <?php foreach ($statuses as $statusValue => $statusLabel): ?>
                                            <option value="<?php echo htmlspecialchars((string) $statusValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $filters['status'] === (string) $statusValue ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <label class="form-label fw-semibold">Hiển thị</label>
                                    <select name="per_page" class="form-select erp-select">
                                        <?php foreach ([10, 25, 50, 100] as $size): ?>
                                            <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <label class="form-label fw-semibold">Sắp xếp theo</label>
                                    <select name="sort_by" class="form-select erp-select">
                                        <?php foreach ($sortOptions as $sortKey => $sortLabel): ?>
                                            <option value="<?php echo htmlspecialchars($sortKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $sort['by'] === $sortKey ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sortLabel, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-2">
                                    <label class="form-label fw-semibold">Chiều sắp xếp</label>
                                    <select name="sort_dir" class="form-select erp-select">
                                        <option value="desc" <?php echo strtolower((string) $sort['dir']) === 'desc' ? 'selected' : ''; ?>>Giảm dần</option>
                                        <option value="asc" <?php echo strtolower((string) $sort['dir']) === 'asc' ? 'selected' : ''; ?>>Tăng dần</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-lg-end">
                                        <a href="<?php echo htmlspecialchars(app_url('/suppliers'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                        <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Lọc</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="suppliersTable">
                                <thead>
                                    <tr>
                                        <th data-col="code">Mã</th>
                                        <th data-col="name">Tên nhà cung cấp</th>
                                        <th data-col="contact">Người liên hệ</th>
                                        <th data-col="phone">Điện thoại</th>
                                        <th data-col="email">Email</th>
                                        <th data-col="status">Trạng thái</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($suppliers === []): ?>
                                    <tr><td colspan="7" class="text-center text-secondary py-5">Không có nhà cung cấp phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <tr class="erp-row-compact">
                                            <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $supplier['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="name" class="erp-cell-compact">
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) $supplier['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php if (!empty($supplier['tax_code'])): ?><div class="erp-cell-secondary"><?php echo htmlspecialchars((string) $supplier['tax_code'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                            </td>
                                            <td data-col="contact"><?php echo htmlspecialchars((string) ($supplier['contact_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="phone"><?php echo htmlspecialchars((string) ($supplier['phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="email"><?php echo htmlspecialchars((string) ($supplier['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="status"><span class="badge text-bg-<?php echo $statusBadgeMap[(string) ($supplier['is_active'] ?? 1)] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(($supplier['is_active'] ?? 1) ? 'Đang dùng' : 'Ngưng dùng', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/suppliers/show?id=' . (int) $supplier['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/suppliers/edit?id=' . (int) $supplier['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                        <li><form method="post" action="<?php echo htmlspecialchars(app_url('/suppliers/delete?id=' . (int) $supplier['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa nhà cung cấp này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
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
