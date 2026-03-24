<?php
$activeSidebar = $activeSidebar ?? 'quotations';
$pageTitle = $pageTitle ?? 'Báo giá';
$pageEyebrow = $pageEyebrow ?? 'Quản lý báo giá';
$search = $search ?? '';
$status = $status ?? '';
$statuses = $statuses ?? [];
$quotations = $quotations ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);

$statusBadgeMap = [
    'draft' => 'secondary',
    'pending_approval' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger',
    'cancelled' => 'secondary',
    'converted_to_order' => 'primary',
];
$statusLabels = [
    'draft' => 'Nháp',
    'pending_approval' => 'Chờ duyệt',
    'approved' => 'Đã duyệt',
    'rejected' => 'Từ chối',
    'cancelled' => 'Đã hủy',
    'converted_to_order' => 'Đã chuyển đơn',
];
$canCreate = has_permission('quotation.create');
$canUpdate = has_permission('quotation.update');
$canDelete = has_permission('quotation.delete');
$canSubmitPermission = has_permission('quotation.submit');
$canApprovePermission = has_permission('quotation.approve');
$canRejectPermission = has_permission('quotation.reject');
$canCancelPermission = has_permission('quotation.cancel');
$money = static fn (mixed $value): string => number_format((float) $value, 0, ',', '.');
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Báo giá</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách báo giá</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#quote-filter-collapse" aria-expanded="true" aria-controls="quote-filter-collapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-layout-three-columns"></i>Hiển thị cột
                                </button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="quote-list-table">
                                    <?php foreach ([
                                        'code' => 'Mã',
                                        'customer' => 'Khách hàng',
                                        'quote_date' => 'Ngày báo giá',
                                        'expired_at' => 'Hiệu lực',
                                        'status' => 'Trạng thái',
                                        'subtotal' => 'Tạm tính',
                                        'total' => 'Tổng tiền',
                                    ] as $key => $label): ?>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                            <span class="form-check-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if ($canCreate): ?>
                                <a href="<?php echo htmlspecialchars(app_url('/quotations/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm báo giá</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="collapse show mb-4" id="quote-filter-collapse" data-filter-collapse="quotes">
                        <form method="get" action="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label fw-semibold">Tìm kiếm</label>
                                    <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm theo mã báo giá, mã KH, tên KH">
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Trạng thái</label>
                                    <select name="status" class="form-select erp-select">
                                        <option value="">Tất cả trạng thái</option>
                                        <?php foreach ($statuses as $statusOption): ?>
                                            <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === $statusOption ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) ($statusLabels[$statusOption] ?? $statusOption), ENT_QUOTES, 'UTF-8'); ?>
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
                                    <div class="d-flex gap-2 justify-content-lg-end">
                                        <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                        <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Lọc</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="quote-list-table">
                                <thead>
                                    <tr>
                                        <th data-col="code">Mã</th>
                                        <th data-col="customer">Khách hàng</th>
                                        <th data-col="quote_date">Ngày báo giá</th>
                                        <th data-col="expired_at">Hiệu lực đến</th>
                                        <th data-col="status">Trạng thái</th>
                                        <th data-col="subtotal" class="text-end">Tạm tính</th>
                                        <th data-col="total" class="text-end">Tổng tiền</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($quotations === []): ?>
                                    <tr><td colspan="8" class="text-center text-secondary py-5">Không có báo giá phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($quotations as $quotation): ?>
                                        <tr class="erp-row-compact">
                                            <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $quotation['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="customer" class="erp-cell-compact">
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) $quotation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="erp-cell-secondary"><?php echo htmlspecialchars((string) $quotation['customer_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-col="quote_date"><?php echo htmlspecialchars((string) $quotation['quote_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="expired_at"><?php echo htmlspecialchars((string) ($quotation['expired_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="status"><span class="badge text-bg-<?php echo $statusBadgeMap[$quotation['status']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($quotation['status_label'] ?? ($statusLabels[$quotation['status']] ?? $quotation['status'])), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="subtotal" class="text-end"><?php echo htmlspecialchars($money($quotation['subtotal']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="total" class="text-end fw-semibold"><?php echo htmlspecialchars($money($quotation['total_amount']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/quotations/show?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                        <?php if ($canUpdate && in_array((string) ($quotation['status'] ?? 'draft'), ['draft', 'rejected'], true)): ?>
                                                            <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/quotations/edit?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                        <?php endif; ?>
                                                        <?php if ($canSubmitPermission && (string) ($quotation['status'] ?? '') === 'draft'): ?>
                                                            <li><form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/submit?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="dropdown-item">Trình duyệt</button></form></li>
                                                        <?php endif; ?>
                                                        <?php if ($canApprovePermission && (string) ($quotation['status'] ?? '') === 'pending_approval'): ?>
                                                            <li><form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/approve?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="dropdown-item">Duyệt</button></form></li>
                                                        <?php endif; ?>
                                                        <?php if ($canRejectPermission && (string) ($quotation['status'] ?? '') === 'pending_approval'): ?>
                                                            <li><form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/reject?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="dropdown-item text-danger">Từ chối</button></form></li>
                                                        <?php endif; ?>
                                                        <?php if ($canCancelPermission && in_array((string) ($quotation['status'] ?? ''), ['draft', 'pending_approval'], true)): ?>
                                                            <li><form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/cancel?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn hủy báo giá này?');"><button type="submit" class="dropdown-item">Hủy</button></form></li>
                                                        <?php endif; ?>
                                                        <?php if ($canDelete): ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/delete?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa báo giá này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
                                                        <?php endif; ?>
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
