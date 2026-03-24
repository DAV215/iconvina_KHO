<?php
$activeSidebar = $activeSidebar ?? 'production-orders';
$pageTitle = $pageTitle ?? 'Chi tiết lệnh sản xuất';
$productionOrder = $productionOrder ?? [];
$users = $users ?? [];
$taskStatuses = $taskStatuses ?? [];
$status = (string) ($status ?? '');
$statusMap = [
    'created' => ['Đã tạo lệnh sản xuất.', 'success'],
    'updated' => ['Đã cập nhật lệnh sản xuất.', 'success'],
];
$actions = $productionOrder['available_actions'] ?? [];
$materialSummary = $productionOrder['material_summary'] ?? [];
$materialRequirements = $productionOrder['material_requirements'] ?? [];
$issueTransactions = $productionOrder['issue_transactions'] ?? [];
$logs = $productionOrder['logs'] ?? [];
$activeBom = $productionOrder['active_bom'] ?? null;
$canAssign = has_permission('production.assign');
$canUpdate = has_permission('production.update');
$canComplete = production_permission('complete');
$canIssue = production_permission('issue');
$canStart = production_permission('start');
$canViewLog = production_permission('view_log');
$canManageTaskUpdates = $canUpdate || $canAssign || $canComplete;
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
$canViewStock = has_permission('stock.view');
$canViewSalesOrder = has_permission('sales_order.view');
$processSteps = match ((string) ($productionOrder['status'] ?? 'draft')) {
    'released' => [
        ['label' => 'Nháp', 'state' => 'completed', 'time' => $productionOrder['created_at'] ?? '-', 'icon' => 'file-earmark-plus'],
        ['label' => 'Phát hành', 'state' => 'current', 'time' => $productionOrder['updated_at'] ?? '-', 'icon' => 'send-check'],
        ['label' => 'Sản xuất', 'state' => 'pending', 'time' => 'Chưa bắt đầu', 'icon' => 'gear'],
        ['label' => 'Hoàn tất', 'state' => 'pending', 'time' => 'Chưa hoàn tất', 'icon' => 'check2-circle'],
    ],
    'in_progress' => [
        ['label' => 'Nháp', 'state' => 'completed', 'time' => $productionOrder['created_at'] ?? '-', 'icon' => 'file-earmark-plus'],
        ['label' => 'Phát hành', 'state' => 'completed', 'time' => $productionOrder['updated_at'] ?? '-', 'icon' => 'send-check'],
        ['label' => 'Sản xuất', 'state' => 'current', 'time' => $productionOrder['actual_start_at'] ?? '-', 'icon' => 'gear-wide-connected'],
        ['label' => 'Hoàn tất', 'state' => 'pending', 'time' => 'Chờ hoàn tất', 'icon' => 'check2-circle'],
    ],
    'completed' => [
        ['label' => 'Nháp', 'state' => 'completed', 'time' => $productionOrder['created_at'] ?? '-', 'icon' => 'file-earmark-plus'],
        ['label' => 'Phát hành', 'state' => 'completed', 'time' => $productionOrder['updated_at'] ?? '-', 'icon' => 'send-check'],
        ['label' => 'Sản xuất', 'state' => 'completed', 'time' => $productionOrder['actual_start_at'] ?? '-', 'icon' => 'gear-wide-connected'],
        ['label' => 'Hoàn tất', 'state' => 'current', 'time' => $productionOrder['actual_end_at'] ?? '-', 'icon' => 'check2-circle'],
    ],
    default => [
        ['label' => 'Nháp', 'state' => 'current', 'time' => $productionOrder['created_at'] ?? '-', 'icon' => 'file-earmark-plus'],
        ['label' => 'Phát hành', 'state' => 'pending', 'time' => 'Chưa phát hành', 'icon' => 'send-check'],
        ['label' => 'Sản xuất', 'state' => 'pending', 'time' => 'Chưa bắt đầu', 'icon' => 'gear'],
        ['label' => 'Hoàn tất', 'state' => 'pending', 'time' => 'Chưa hoàn tất', 'icon' => 'check2-circle'],
    ],
};
$actionLabels = [
    'create' => 'Tạo lệnh',
    'release' => 'Phát hành',
    'issue_materials' => 'Tạo phiếu xuất vật tư',
    'issue_partial' => 'Xuất vật tư một phần',
    'issue_full' => 'Xuất vật tư đủ',
    'start' => 'Bắt đầu sản xuất',
    'complete' => 'Hoàn tất',
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
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?>
        body{font-size:13px}
        .prod-task-table th,.prod-task-table td,.prod-material-table th,.prod-material-table td,.prod-log-table th,.prod-log-table td{font-size:12.75px;vertical-align:top}
        .prod-task-table .form-control,.prod-task-table .form-select{font-size:12.5px;min-height:34px}
        .prod-action-row form{display:inline-block}
        .prod-summary-card{min-height:100px}
    </style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-4 px-xl-5">
                <?php if (isset($statusMap[$status])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars((string) $statusMap[$status][1], ENT_QUOTES, 'UTF-8'); ?> rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $statusMap[$status][0], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($flashSuccess = get_flash('success')): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($flashError = get_flash('error')): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <div class="erp-detail-section__eyebrow">Sản xuất</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <h2 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) ($productionOrder['code'] ?? 'PO'), ENT_QUOTES, 'UTF-8'); ?></h2>
                            <span class="badge text-bg-<?php echo htmlspecialchars((string) ($productionOrder['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($productionOrder['status_label'] ?? 'Nháp'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge text-bg-<?php echo htmlspecialchars((string) ($materialSummary['issue_status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($materialSummary['issue_status_label'] ?? 'Chưa xuất vật tư'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="text-secondary"><?php echo htmlspecialchars((string) ($productionOrder['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap prod-action-row">
                        <a href="<?php echo htmlspecialchars(app_url('/production-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                        <?php if (!empty($productionOrder['bom_url'])): ?>
                            <a href="<?php echo htmlspecialchars((string) $productionOrder['bom_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary rounded-4 px-4">Xem BOM</a>
                        <?php endif; ?>
                        <?php if ($canViewStock && !empty($productionOrder['stock_receipt_url'])): ?>
                            <a href="<?php echo htmlspecialchars((string) $productionOrder['stock_receipt_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary rounded-4 px-4">Phiếu nhập kho</a>
                        <?php endif; ?>
                        <?php if ($canViewSalesOrder && !empty($productionOrder['sales_order_url'])): ?>
                            <a href="<?php echo htmlspecialchars((string) $productionOrder['sales_order_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary rounded-4 px-4">Mở đơn bán</a>
                        <?php endif; ?>
                        <?php if (!empty($actions['can_release'])): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/release?id=' . (int) ($productionOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Phát hành lệnh sản xuất này?');">
                                <button type="submit" class="btn btn-outline-dark rounded-4 px-4">Phát hành</button>
                            </form>
                        <?php endif; ?>
                        <?php if (!empty($actions['can_start'])): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/start?id=' . (int) ($productionOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bắt đầu sản xuất?');">
                                <button type="submit" class="btn btn-outline-primary rounded-4 px-4">Bắt đầu sản xuất</button>
                            </form>
                        <?php endif; ?>
                        <?php if (!empty($actions['can_complete'])): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/complete?id=' . (int) ($productionOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Hoàn tất lệnh sản xuất và nhập kho?');">
                                <input type="hidden" name="completed_qty" value="<?php echo htmlspecialchars((string) ($productionOrder['planned_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-dark rounded-4 px-4">Hoàn tất</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($productionOrder['next_step_message'])): ?>
                    <div class="alert alert-<?php echo (($materialSummary['issue_status'] ?? '') === 'no_bom') ? 'danger' : 'info'; ?> rounded-4 border-0 shadow-sm mb-4">
                        <?php echo htmlspecialchars((string) $productionOrder['next_step_message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php
                $processTitle = 'Tracking lệnh sản xuất';
                $processSubtitle = 'Theo dõi từ nháp, phát hành, xuất vật tư, sản xuất đến nhập kho thành phẩm.';
                include base_path('app/Modules/Home/Views/partials/process_timeline.php');
                ?>

                <div class="accordion d-grid gap-4" id="productionAccordion">
                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#productionInfoCollapse" aria-expanded="true">1. Thông tin lệnh</button></h2>
                        <div id="productionInfoCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4"><strong>Đơn bán:</strong> <?php echo htmlspecialchars((string) ($productionOrder['sales_order_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Dòng đơn:</strong> <?php echo htmlspecialchars((string) ($productionOrder['sales_order_line_no'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Ưu tiên:</strong> <?php echo htmlspecialchars((string) ($productionOrder['priority_label'] ?? 'Bình thường'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Thành phẩm:</strong> <?php echo htmlspecialchars((string) (($productionOrder['component_code'] ?? '') . ' - ' . ($productionOrder['component_name'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>SL kế hoạch:</strong> <?php echo number_format((float) ($productionOrder['planned_qty'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-4"><strong>Tiến độ:</strong> <?php echo number_format((float) ($productionOrder['progress_percent'] ?? 0), 0); ?>%</div>
                                    <div class="col-12 col-md-4"><strong>BOM active:</strong> <?php echo htmlspecialchars((string) ($activeBom['version'] ?? 'Chưa có'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Trạng thái vật tư:</strong> <?php echo htmlspecialchars((string) ($materialSummary['issue_status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Phiếu nhập kho:</strong> <?php echo !empty($productionOrder['stock_receipt']['txn_no']) ? htmlspecialchars((string) $productionOrder['stock_receipt']['txn_no'], ENT_QUOTES, 'UTF-8') : 'Chưa tạo'; ?></div>
                                </div>
                                <div class="mt-3 text-secondary"><?php echo nl2br(htmlspecialchars((string) ($productionOrder['note'] ?? 'Chưa có ghi chú.'), ENT_QUOTES, 'UTF-8')); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#productionBomCollapse" aria-expanded="true">2. BOM / cấu tạo</button></h2>
                        <div id="productionBomCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <?php if (empty($activeBom['id'])): ?>
                                    <div class="alert alert-danger rounded-4 border-0 mb-0">Không có BOM active cho thành phẩm này. Không thể phát hành hoặc xuất vật tư.</div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string) (($activeBom['component_code'] ?? '') . ' - ' . ($activeBom['component_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-secondary small">Version BOM: <?php echo htmlspecialchars((string) ($activeBom['version'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if (!empty($productionOrder['bom_url'])): ?><a href="<?php echo htmlspecialchars((string) $productionOrder['bom_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">Mở BOM</a><?php endif; ?>
                                            <?php if (!empty($productionOrder['bom_tree_url'])): ?><a href="<?php echo htmlspecialchars((string) $productionOrder['bom_tree_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark btn-sm">Xem cấu tạo</a><?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table prod-material-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Loại</th>
                                                    <th>Mã</th>
                                                    <th>Tên</th>
                                                    <th>ĐVT</th>
                                                    <th class="text-end">Định mức / BOM</th>
                                                    <th class="text-end">Cần theo lệnh</th>
                                                    <th class="text-end">Đã xuất</th>
                                                    <th class="text-end">Còn phải xuất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($materialRequirements as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($row['item_kind_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($row['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($row['name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($row['unit'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['bom_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['required_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['issued_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['remaining_issue_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#productionMaterialCollapse" aria-expanded="true">3. Tình trạng vật tư</button></h2>
                        <div id="productionMaterialCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-3"><div class="erp-card-muted rounded-4 p-3 prod-summary-card"><div class="text-secondary small">Số dòng BOM</div><div class="fw-bold fs-5"><?php echo (int) ($materialSummary['line_count'] ?? 0); ?></div></div></div>
                                    <div class="col-12 col-md-3"><div class="erp-card-muted rounded-4 p-3 prod-summary-card"><div class="text-secondary small">Tổng cần xuất</div><div class="fw-bold fs-5"><?php echo htmlspecialchars((string) ($materialSummary['required_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></div></div></div>
                                    <div class="col-12 col-md-3"><div class="erp-card-muted rounded-4 p-3 prod-summary-card"><div class="text-secondary small">Đã xuất</div><div class="fw-bold fs-5"><?php echo htmlspecialchars((string) ($materialSummary['issued_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></div></div></div>
                                    <div class="col-12 col-md-3"><div class="erp-card-muted rounded-4 p-3 prod-summary-card"><div class="text-secondary small">Dòng đang thiếu</div><div class="fw-bold fs-5"><?php echo (int) ($materialSummary['shortage_line_count'] ?? 0); ?></div></div></div>
                                </div>

                                <div class="table-responsive mb-4">
                                    <table class="table prod-material-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mã vật tư</th>
                                                <th>Tên vật tư</th>
                                                <th>ĐVT</th>
                                                <th class="text-end">Cần theo lệnh</th>
                                                <th class="text-end">Tồn kho hiện tại</th>
                                                <th class="text-end">Thiếu / đủ</th>
                                                <th class="text-end">Đã xuất</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($materialRequirements === []): ?>
                                            <tr><td colspan="7" class="text-center text-secondary py-4">Chưa có dữ liệu BOM active.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($materialRequirements as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($row['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars((string) ($row['name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="text-secondary small"><?php echo htmlspecialchars((string) ($row['item_kind_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars((string) ($row['unit'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['required_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['current_stock'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end">
                                                        <span class="badge text-bg-<?php echo htmlspecialchars((string) ($row['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($row['status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <div class="small text-secondary mt-1">Thiếu: <?php echo htmlspecialchars((string) ($row['shortage_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="text-end"><?php echo htmlspecialchars((string) ($row['issued_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($canIssue && !empty($actions['can_issue']) && $materialRequirements !== []): ?>
                                    <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/issue-materials?id=' . (int) ($productionOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="border rounded-4 p-3 bg-light">
                                        <div class="row g-3 align-items-end mb-3">
                                            <div class="col-12 col-md-3">
                                                <label class="form-label fw-semibold">Ngày xuất kho</label>
                                                <input type="date" name="txn_date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="col-12 col-md-7">
                                                <label class="form-label fw-semibold">Ghi chú</label>
                                                <input type="text" name="note" class="form-control" maxlength="255" placeholder="Xuất vật tư cho lệnh sản xuất">
                                            </div>
                                            <div class="col-12 col-md-2 d-grid">
                                                <button type="submit" class="btn btn-primary">Xuất vật tư</button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm prod-material-table align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Mã</th>
                                                        <th>Tên</th>
                                                        <th class="text-end">Còn phải xuất</th>
                                                        <th class="text-end">Tồn khả dụng</th>
                                                        <th class="text-end">Xuất lần này</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($materialRequirements as $index => $row): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo htmlspecialchars((string) ($row['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                                            <input type="hidden" name="items[<?php echo $index; ?>][row_key]" value="<?php echo htmlspecialchars((string) ($row['row_key'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                        <td><?php echo htmlspecialchars((string) ($row['name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-end"><?php echo htmlspecialchars((string) ($row['remaining_issue_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-end"><?php echo htmlspecialchars((string) ($row['current_stock'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-end" style="max-width:150px">
                                                            <input type="number" step="0.01" min="0" name="items[<?php echo $index; ?>][issue_qty]" class="form-control form-control-sm text-end" value="<?php echo htmlspecialchars((string) ($row['remaining_issue_qty'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#productionIssueCollapse" aria-expanded="true">4. Phiếu xuất kho sản xuất</button></h2>
                        <div id="productionIssueCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table prod-material-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mã phiếu</th>
                                                <th>Ngày phiếu</th>
                                                <th class="text-end">Số dòng</th>
                                                <th class="text-end">Tổng SL</th>
                                                <th>Ghi chú</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($issueTransactions === []): ?>
                                            <tr><td colspan="6" class="text-center text-secondary py-4">Chưa có phiếu xuất vật tư cho lệnh này.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($issueTransactions as $transaction): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) ($transaction['txn_no'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($transaction['txn_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo (int) ($transaction['item_count'] ?? 0); ?></td>
                                                    <td class="text-end"><?php echo number_format((float) ($transaction['total_quantity'] ?? 0), 2); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($transaction['note'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if ($canViewStock): ?>
                                                            <a href="<?php echo htmlspecialchars((string) ($transaction['detail_url'] ?? '#'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">Xem phiếu</a>
                                                        <?php else: ?>
                                                            <span class="text-secondary">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#productionTasksCollapse" aria-expanded="true">5. Task thực hiện</button></h2>
                        <div id="productionTasksCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table prod-task-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Công việc</th>
                                                <th>Người phụ trách</th>
                                                <th>Trạng thái</th>
                                                <th class="text-end">Tiến độ</th>
                                                <th>Kế hoạch</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach (($productionOrder['tasks'] ?? []) as $task): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars((string) ($task['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="text-secondary small"><?php echo nl2br(htmlspecialchars((string) ($task['note'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars((string) ($task['assigned_name'] ?? 'Chưa phân công'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($task['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($task['status_label'] ?? 'Chờ xử lý'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-end"><?php echo number_format((float) ($task['progress_percent'] ?? 0), 0); ?>%</td>
                                                <td class="small text-secondary"><?php echo htmlspecialchars((string) (($task['planned_start_at'] ?? '-') . ' -> ' . ($task['planned_end_at'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="d-grid gap-2">
                                                        <?php if ($canAssign): ?>
                                                            <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/assign-task?id=' . (int) ($productionOrder['id'] ?? 0) . '&task_id=' . (int) ($task['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="row g-2">
                                                                <div class="col-3">
                                                                    <select name="assigned_to" class="form-select form-select-sm">
                                                                        <option value="">Chọn người</option>
                                                                        <?php foreach ($users as $user): ?>
                                                                            <option value="<?php echo (int) $user['id']; ?>" <?php echo (int) ($task['assigned_to'] ?? 0) === (int) $user['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) (($user['full_name'] ?: $user['username']) . ' (' . $user['username'] . ')'), ENT_QUOTES, 'UTF-8'); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-3"><input type="datetime-local" name="planned_start_at" class="form-control form-control-sm" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr((string) ($task['planned_start_at'] ?? ''), 0, 16)), ENT_QUOTES, 'UTF-8'); ?>"></div>
                                                                <div class="col-3"><input type="datetime-local" name="planned_end_at" class="form-control form-control-sm" value="<?php echo htmlspecialchars(str_replace(' ', 'T', substr((string) ($task['planned_end_at'] ?? ''), 0, 16)), ENT_QUOTES, 'UTF-8'); ?>"></div>
                                                                <div class="col-3"><button type="submit" class="btn btn-outline-secondary btn-sm w-100">Phân công</button></div>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if ($canManageTaskUpdates || (int) ($task['assigned_to'] ?? 0) === $currentUserId): ?>
                                                            <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/update-task?id=' . (int) ($productionOrder['id'] ?? 0) . '&task_id=' . (int) ($task['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="row g-2">
                                                                <div class="col-3">
                                                                    <select name="status" class="form-select form-select-sm">
                                                                        <?php foreach ($taskStatuses as $taskStatus): ?>
                                                                            <option value="<?php echo htmlspecialchars((string) $taskStatus, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($task['status'] ?? '') === $taskStatus) ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $taskStatus)), ENT_QUOTES, 'UTF-8'); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-3"><input type="number" name="progress_percent" min="0" max="100" step="1" class="form-control form-control-sm" value="<?php echo htmlspecialchars((string) number_format((float) ($task['progress_percent'] ?? 0), 0, '.', ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
                                                                <div class="col-6"><button type="submit" class="btn btn-outline-dark btn-sm w-100">Cập nhật</button></div>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($canViewLog && !empty($actions['can_view_log'])): ?>
                        <div class="accordion-item erp-card border-0">
                            <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#productionLogCollapse" aria-expanded="false">6. Nhật ký hoạt động</button></h2>
                            <div id="productionLogCollapse" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table prod-log-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Thời gian</th>
                                                    <th>Thao tác</th>
                                                    <th>Từ trạng thái</th>
                                                    <th>Sang trạng thái</th>
                                                    <th>Ghi chú</th>
                                                    <th>Người xử lý</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if ($logs === []): ?>
                                                <tr><td colspan="6" class="text-center text-secondary py-4">Chưa có nhật ký hoạt động.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($logs as $log): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) ($log['acted_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($actionLabels[$log['action'] ?? ''] ?? ($log['action'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['old_status'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['new_status'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['remark'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) (($log['acted_by_name'] ?? '') !== '' ? $log['acted_by_name'] : ($log['acted_by_username'] ?? $log['acted_by'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
