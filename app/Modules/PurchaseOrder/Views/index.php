<?php
$activeSidebar = $activeSidebar ?? 'purchase-orders';
$pageTitle = $pageTitle ?? 'Đơn mua hàng';
$filters = $filters ?? ['search' => '', 'status' => '', 'date_from' => '', 'date_to' => ''];
$sort = $sort ?? ['by' => 'order_date', 'dir' => 'desc'];
$statuses = $statuses ?? [];
$statusLabels = $statusLabels ?? [];
$sortOptions = $sortOptions ?? [];
$purchaseOrders = $purchaseOrders ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$canCreate = po_permission('create');
$canDelete = po_permission('delete');
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
        body { font-size: 13.5px; }
        .po-index-table td, .po-index-table th { white-space: nowrap; }
        .po-index-table .po-col-supplier { min-width: 220px; white-space: normal; }
    </style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <div class="erp-card p-3 p-lg-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-1">Quy trình đơn mua hàng</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách đơn mua hàng</h3>
                            <div class="text-secondary">Theo dõi đơn mua hàng theo luồng nháp, duyệt, nhận hàng, nhập kho và đóng hồ sơ.</div>
                        </div>
                        <?php if ($canCreate): ?>
                            <a href="<?php echo htmlspecialchars(app_url('/purchase-orders/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4">Tạo đơn mua</a>
                        <?php endif; ?>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/purchase-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-3">
                                <label class="form-label fw-semibold">Tìm kiếm</label>
                                <input type="text" name="search" class="form-control erp-field" value="<?php echo htmlspecialchars((string) $filters['search'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Mã đơn, NCC, trạng thái">
                            </div>
                            <div class="col-6 col-lg-2">
                                <label class="form-label fw-semibold">Trạng thái</label>
                                <select name="status" class="form-select erp-select">
                                    <option value="">Tất cả</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $filters['status'] === $status ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars((string) ($statusLabels[$status] ?? $status), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-lg-2">
                                <label class="form-label fw-semibold">Từ ngày</label>
                                <input type="date" name="date_from" class="form-control erp-field" value="<?php echo htmlspecialchars((string) $filters['date_from'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-6 col-lg-2">
                                <label class="form-label fw-semibold">Đến ngày</label>
                                <input type="date" name="date_to" class="form-control erp-field" value="<?php echo htmlspecialchars((string) $filters['date_to'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-6 col-lg-1">
                                <label class="form-label fw-semibold">Dòng</label>
                                <select name="per_page" class="form-select erp-select">
                                    <?php foreach ([10, 25, 50, 100] as $size): ?>
                                        <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-lg-1">
                                <label class="form-label fw-semibold">Sắp xếp</label>
                                <select name="sort_dir" class="form-select erp-select">
                                    <option value="desc" <?php echo strtolower((string) $sort['dir']) === 'desc' ? 'selected' : ''; ?>>Giảm dần</option>
                                    <option value="asc" <?php echo strtolower((string) $sort['dir']) === 'asc' ? 'selected' : ''; ?>>Tăng dần</option>
                                </select>
                            </div>
                            <div class="col-12 col-lg-1">
                                <label class="form-label fw-semibold">Theo</label>
                                <select name="sort_by" class="form-select erp-select">
                                    <?php foreach ($sortOptions as $sortKey => $sortLabel): ?>
                                        <option value="<?php echo htmlspecialchars($sortKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $sort['by'] === $sortKey ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sortLabel, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <a href="<?php echo htmlspecialchars(app_url('/purchase-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn">Đặt lại</a>
                                <button type="submit" class="btn btn-dark erp-btn">Lọc</button>
                            </div>
                        </div>
                    </form>

                    <div class="erp-table-shell p-2">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle po-index-table">
                                <thead>
                                    <tr>
                                        <th>Mã PO</th>
                                        <th class="po-col-supplier">Nhà cung cấp</th>
                                        <th>Ngày đặt</th>
                                        <th>Ngày nhận dự kiến</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Tổng tiền</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($purchaseOrders === []): ?>
                                    <tr><td colspan="7" class="text-center text-secondary py-5">Không có đơn mua hàng phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($purchaseOrders as $purchaseOrder): ?>
                                        <?php
                                        $status = (string) ($purchaseOrder['status'] ?? 'draft');
                                        $canEdit = po_permission('update') && in_array($status, ['draft', 'rejected'], true);
                                        $canSubmit = po_permission('submit') && in_array($status, ['draft', 'rejected'], true);
                                        $canApprove = po_permission('approve') && $status === 'pending_approval';
                                        $canReject = po_permission('reject') && $status === 'pending_approval';
                                        $canCancel = po_permission('cancel') && in_array($status, ['draft', 'pending_approval'], true);
                                        ?>
                                        <tr class="erp-row-compact">
                                            <td><span class="erp-code-badge"><?php echo htmlspecialchars((string) ($purchaseOrder['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="po-col-supplier">
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) ($purchaseOrder['supplier_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="erp-cell-secondary"><?php echo htmlspecialchars((string) ($purchaseOrder['supplier_phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars((string) ($purchaseOrder['order_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($purchaseOrder['expected_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($purchaseOrder['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($purchaseOrder['status_label'] ?? $status), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-end fw-semibold"><?php echo number_format((float) ($purchaseOrder['total_amount'] ?? 0), 2); ?></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/purchase-orders/show?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                        <?php if ($canEdit): ?><li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/purchase-orders/edit?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li><?php endif; ?>
                                                        <?php if ($canSubmit): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/submit?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Trình duyệt đơn mua hàng này?');"><button type="submit" class="dropdown-item">Trình duyệt</button></form></li><?php endif; ?>
                                                        <?php if ($canApprove): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/approve?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Duyệt đơn mua hàng này?');"><button type="submit" class="dropdown-item">Duyệt</button></form></li><?php endif; ?>
                                                        <?php if ($canReject): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/reject?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Từ chối đơn mua hàng này?');"><button type="submit" class="dropdown-item text-warning">Từ chối</button></form></li><?php endif; ?>
                                                        <?php if ($canCancel): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/cancel?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Hủy đơn mua hàng này?');"><button type="submit" class="dropdown-item text-warning">Hủy</button></form></li><?php endif; ?>
                                                        <?php if ($canDelete): ?><li><hr class="dropdown-divider"></li><li><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/delete?id=' . (int) $purchaseOrder['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xóa PO này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li><?php endif; ?>
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
