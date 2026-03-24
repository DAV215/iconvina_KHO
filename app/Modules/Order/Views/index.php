<?php
$activeSidebar = $activeSidebar ?? 'orders';
$pageTitle = $pageTitle ?? 'Đơn hàng';
$pageEyebrow = $pageEyebrow ?? 'Quản lý đơn hàng';
$search = $search ?? '';
$status = $status ?? '';
$statuses = $statuses ?? [];
$orders = $orders ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$canCreate = has_permission('sales_order.create');
$canUpdate = has_permission('sales_order.update');
$canDelete = has_permission('sales_order.delete');
$canApprove = has_permission('sales_order.approve') || has_permission('sales_order.confirm');

$statusBadgeMap = [
    'draft' => 'secondary',
    'confirmed' => 'info',
    'waiting_stock' => 'secondary',
    'waiting_production' => 'warning',
    'ready_to_deliver' => 'success',
    'partially_delivered' => 'warning',
    'delivered' => 'primary',
    'closed' => 'dark',
    'cancelled' => 'danger',
];

$priorityBadgeMap = [
    'low' => 'light',
    'normal' => 'secondary',
    'high' => 'warning',
    'urgent' => 'danger',
];
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Đơn hàng</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách đơn hàng</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#orderFilterCollapse" aria-expanded="true" aria-controls="orderFilterCollapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-layout-three-columns"></i>Hiển thị cột
                                </button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="ordersTable">
                                    <?php foreach ([
                                        'code' => 'Mã',
                                        'customer' => 'Khách hàng',
                                        'quotation' => 'Báo giá',
                                        'order_date' => 'Ngày đặt',
                                        'status' => 'Trạng thái',
                                        'priority' => 'Ưu tiên',
                                        'total' => 'Tổng tiền',
                                    ] as $key => $label): ?>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                            <span class="form-check-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if ($canCreate): ?><a href="<?php echo htmlspecialchars(app_url('/orders/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm đơn hàng</a><?php endif; ?>
                        </div>
                    </div>

                    <div class="collapse show mb-4" id="orderFilterCollapse" data-filter-collapse="orders">
                        <form method="get" action="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label fw-semibold">Tìm kiếm</label>
                                    <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm theo mã đơn, mã KH, tên KH">
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label fw-semibold">Trạng thái</label>
                                    <select name="status" class="form-select erp-select">
                                        <option value="">Tất cả trạng thái</option>
                                        <?php foreach ($statuses as $statusOption): ?>
                                            <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === $statusOption ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $statusOption)), ENT_QUOTES, 'UTF-8'); ?>
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
                                        <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                        <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Lọc</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="ordersTable">
                                <thead>
                                    <tr>
                                        <th data-col="code">Mã</th>
                                        <th data-col="customer">Khách hàng</th>
                                        <th data-col="quotation">Báo giá</th>
                                        <th data-col="order_date">Ngày đặt</th>
                                        <th data-col="status">Trạng thái</th>
                                        <th data-col="priority">Ưu tiên</th>
                                        <th data-col="total" class="text-end">Tổng tiền</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($orders === []): ?>
                                    <tr><td colspan="8" class="text-center text-secondary py-5">Không có đơn hàng phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr class="erp-row-compact">
                                            <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $order['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="customer" class="erp-cell-compact">
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) $order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="erp-cell-secondary"><?php echo htmlspecialchars((string) $order['customer_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td data-col="quotation"><?php echo htmlspecialchars((string) ($order['quotation_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="order_date"><?php echo htmlspecialchars((string) $order['order_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="status"><span class="badge text-bg-<?php echo $statusBadgeMap[$order['status']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($order['status_label'] ?? ucwords(str_replace('_', ' ', (string) $order['status']))), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="priority"><span class="badge text-bg-<?php echo $priorityBadgeMap[$order['priority']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($order['priority_label'] ?? ucfirst((string) $order['priority'])), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="total" class="text-end fw-semibold"><?php echo number_format((float) $order['total_amount'], 2); ?></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/orders/show?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                        <?php if ($canUpdate): ?><li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/orders/edit?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li><?php endif; ?>
                                                        <?php if ($canApprove && (string) ($order['status'] ?? '') === 'draft'): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/orders/confirm?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xác nhận đơn bán hàng này?');"><button type="submit" class="dropdown-item">Xác nhận đơn</button></form></li><?php endif; ?>
                                                        <?php if ($canDelete): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/orders/delete?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li><?php endif; ?>
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
