<?php
$activeSidebar = $activeSidebar ?? 'purchase-orders';
$pageTitle = $pageTitle ?? 'Chi tiết đơn mua hàng';
$purchaseOrder = $purchaseOrder ?? [];
$statusLabels = $statusLabels ?? [];
$status = (string) ($purchaseOrder['status'] ?? 'draft');
$actions = $purchaseOrder['available_actions'] ?? [];
$items = $purchaseOrder['items'] ?? [];
$receivings = $purchaseOrder['receivings'] ?? [];
$extraCosts = $purchaseOrder['extra_costs'] ?? [];
$logs = $purchaseOrder['logs'] ?? [];
$receivingSummary = $purchaseOrder['receiving_summary'] ?? [];
$stockTransaction = $purchaseOrder['stock_transaction'] ?? null;
$trackingSteps = $purchaseOrder['tracking_steps'] ?? [];
$payments = $purchaseOrder['payments'] ?? [];
$fmt = static fn (mixed $value): string => number_format((float) $value, 2);
$actionLabels = [
    'create' => 'Tạo mới',
    'update' => 'Cập nhật',
    'submit' => 'Trình duyệt',
    'approve' => 'Duyệt',
    'reject' => 'Từ chối',
    'cancel' => 'Hủy',
    'receive_partial' => 'Nhận một phần',
    'receive_full' => 'Nhận đủ',
    'add_extra_cost' => 'Thêm chi phí',
    'submit_stock_in' => 'Trình duyệt nhập kho',
    'stock_in_approve' => 'Duyệt nhập kho',
    'close' => 'Đóng đơn',
    'payment_created' => 'Tạo payment',
    'payment_confirmed' => 'Xác nhận thanh toán',
    'delete' => 'Xóa',
];
$receiveTypeLabels = [
    'partial' => 'Một phần',
    'full' => 'Đủ',
];
$costTypeLabels = [
    'shipping_cost' => 'Chi phí vận chuyển',
    'extra_cost' => 'Chi phí khác',
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
    <style>
        body { font-size: 13.5px; }
        .po-sticky-top { position: sticky; top: 72px; z-index: 20; }
        .po-compact-table td, .po-compact-table th { padding: .45rem .55rem; }
        .po-summary-grid dt { color: #64748b; font-weight: 600; }
        .po-summary-grid dd { margin-bottom: .4rem; }
        .accordion-button { font-size: 13.5px; font-weight: 700; }
        .accordion-button:not(.collapsed) { background: rgba(15, 23, 42, .03); box-shadow: none; }
        .po-action-row form { display: inline-block; }
        .po-action-row > .btn,
        .po-action-row > form > .btn {
            min-height: 40px;
            padding: 0.5rem 0.95rem !important;
            border-radius: 12px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            font-size: 0.86rem !important;
            font-weight: 600 !important;
        }
        .po-section-card { overflow: hidden; }
        .po-back-btn {
            background: #fff !important;
            border-color: #0f3147 !important;
            color: #0f3147 !important;
        }
        .po-back-btn:hover,
        .po-back-btn:focus {
            background: #0f3147 !important;
            border-color: #0f3147 !important;
            color: #fff !important;
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
                <?php if ($success = get_flash('success')): ?><div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if ($error = get_flash('error')): ?><div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                    <div>
                        <div class="text-uppercase small fw-semibold text-secondary mb-1">Quy trình đơn mua hàng</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h3 class="h4 fw-bold mb-0"><?php echo htmlspecialchars((string) ($purchaseOrder['code'] ?? 'PO'), ENT_QUOTES, 'UTF-8'); ?></h3>
                            <span class="badge text-bg-<?php echo htmlspecialchars((string) ($purchaseOrder['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($purchaseOrder['status_label'] ?? $status), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="text-secondary">NCC: <?php echo htmlspecialchars((string) ($purchaseOrder['supplier_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap po-action-row">
                        <a href="<?php echo htmlspecialchars(app_url('/purchase-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark erp-btn po-back-btn">Quay lại</a>
                        <?php if (!empty($actions['can_edit'])): ?><a href="<?php echo htmlspecialchars(app_url('/purchase-orders/edit?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn">Chỉnh sửa</a><?php endif; ?>
                        <?php if (!empty($actions['can_submit'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/submit?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Trình duyệt đơn mua hàng này?');"><button type="submit" class="btn btn-outline-dark erp-btn">Trình duyệt</button></form><?php endif; ?>
                        <?php if (!empty($actions['can_approve'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/approve?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Duyệt đơn mua hàng này?');"><button type="submit" class="btn btn-success erp-btn">Duyệt</button></form><?php endif; ?>
                        <?php if (!empty($actions['can_reject'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/reject?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Từ chối đơn mua hàng này?');"><button type="submit" class="btn btn-outline-warning erp-btn">Từ chối</button></form><?php endif; ?>
                        <?php if (!empty($actions['can_cancel'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/cancel?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Hủy đơn mua hàng này?');"><button type="submit" class="btn btn-outline-secondary erp-btn">Hủy</button></form><?php endif; ?>
                        <?php if (!empty($actions['can_create_payment'])): ?><button type="button" class="btn btn-outline-dark erp-btn" data-bs-toggle="collapse" data-bs-target="#po-payment-create-collapse" aria-expanded="false" aria-controls="po-payment-create-collapse">Create Payment</button><?php endif; ?>
                        <?php if (!empty($actions['can_view_payments'])): ?><a href="#payments" class="btn btn-outline-secondary erp-btn">View Payments</a><?php endif; ?>
                    </div>
                </div>

                <div class="po-sticky-top mb-3">
                    <?php
                    $processTitle = 'Theo dõi quy trình';
                    $processSubtitle = 'Nháp -> Chờ duyệt -> Nhận hàng -> Nhập kho -> Đóng hồ sơ';
                    include base_path('app/Modules/PurchaseOrder/Views/partials/workflow_tracking.php');
                    ?>
                </div>

                <div class="accordion d-grid gap-3" id="purchaseOrderWorkflowAccordion">
                    <div class="accordion-item erp-card po-section-card" id="po-info">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#poInfoCollapse" aria-expanded="true">1. Thông tin đơn mua</button>
                        </h2>
                        <div id="poInfoCollapse" class="accordion-collapse collapse show" data-bs-parent="#purchaseOrderWorkflowAccordion">
                            <div class="accordion-body">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-8">
                                        <dl class="row po-summary-grid mb-0">
                                            <dt class="col-4">Mã PO</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Ngày đặt</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['order_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Ngày dự kiến</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['expected_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Nhà cung cấp</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['supplier_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Liên hệ</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['supplier_contact'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Điện thoại</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['supplier_phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Email</dt><dd class="col-8"><?php echo htmlspecialchars((string) ($purchaseOrder['supplier_email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-4">Ghi chú</dt><dd class="col-8"><?php echo nl2br(htmlspecialchars((string) ($purchaseOrder['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></dd>
                                        </dl>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <div class="row g-2">
                                            <div class="col-6"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Tạm tính</div><div class="fw-bold"><?php echo $fmt($purchaseOrder['subtotal'] ?? 0); ?></div></div></div>
                                            <div class="col-6"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Chiết khấu</div><div class="fw-bold"><?php echo $fmt($purchaseOrder['discount_amount'] ?? 0); ?></div></div></div>
                                            <div class="col-6"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Thuế</div><div class="fw-bold"><?php echo $fmt($purchaseOrder['tax_amount'] ?? 0); ?></div></div></div>
                                            <div class="col-6"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Tổng tiền</div><div class="fw-bold"><?php echo $fmt($purchaseOrder['total_amount'] ?? 0); ?></div></div></div>
                                            <div class="col-6"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Đã thanh toán</div><div class="fw-bold"><?php echo $fmt($purchaseOrder['paid_amount'] ?? 0); ?></div></div></div>
                                            <div class="col-6"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Còn phải trả</div><div class="fw-bold"><?php echo $fmt($purchaseOrder['remaining_amount'] ?? 0); ?></div></div></div>
                                            <div class="col-12"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Trạng thái thanh toán</div><div class="fw-bold"><span class="badge text-bg-<?php echo htmlspecialchars((string) ($purchaseOrder['payment_status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($purchaseOrder['payment_status_label'] ?? 'Chưa thanh toán'), ENT_QUOTES, 'UTF-8'); ?></span></div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card po-section-card" id="items">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#poItemsCollapse" aria-expanded="true">2. Danh sách vật tư</button>
                        </h2>
                        <div id="poItemsCollapse" class="accordion-collapse collapse show" data-bs-parent="#purchaseOrderWorkflowAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-sm po-compact-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Mã VT</th>
                                                <th>Mô tả</th>
                                                <th>ĐVT</th>
                                                <th class="text-end">SL đặt</th>
                                                <th class="text-end">Đã nhận</th>
                                                <th class="text-end">Còn lại</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-end">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($items as $index => $item): ?>
                                            <?php $summary = $receivingSummary[$index] ?? ['received_quantity' => 0, 'remaining_quantity' => (float) ($item['quantity'] ?? 0)]; ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars((string) ($item['material_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($item['description'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($item['unit'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-end"><?php echo $fmt($item['quantity'] ?? 0); ?></td>
                                                <td class="text-end"><?php echo $fmt($summary['received_quantity'] ?? 0); ?></td>
                                                <td class="text-end"><?php echo $fmt($summary['remaining_quantity'] ?? 0); ?></td>
                                                <td class="text-end"><?php echo $fmt($item['unit_price'] ?? 0); ?></td>
                                                <td class="text-end fw-semibold"><?php echo $fmt($item['total_amount'] ?? 0); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card po-section-card" id="receiving">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#poReceivingCollapse" aria-expanded="true">3. Nhận hàng</button>
                        </h2>
                        <div id="poReceivingCollapse" class="accordion-collapse collapse show" data-bs-parent="#purchaseOrderWorkflowAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($actions['can_receive_partial']) || !empty($actions['can_receive_full'])): ?>
                                    <form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/receive?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="mb-4">
                                        <div class="row g-3 align-items-end mb-3">
                                            <div class="col-12 col-md-3">
                                                <label class="form-label fw-semibold">Ngày nhận</label>
                                                <input type="date" name="received_at" class="form-control erp-field" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold">Ghi chú</label>
                                                <input type="text" name="remark" class="form-control erp-field" maxlength="255" placeholder="Ghi chú nhận hàng">
                                            </div>
                                            <div class="col-12 col-md-3 d-flex gap-2 justify-content-md-end">
                                                <?php if (!empty($actions['can_receive_partial'])): ?><button type="submit" name="receive_mode" value="partial" class="btn btn-outline-primary erp-btn">Nhận một phần</button><?php endif; ?>
                                                <?php if (!empty($actions['can_receive_full'])): ?><button type="submit" name="receive_mode" value="full" class="btn btn-primary erp-btn">Nhận đủ</button><?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm po-compact-table align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Vật tư</th>
                                                        <th class="text-end">Còn lại</th>
                                                        <th class="text-end">Nhận lần này</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($items as $index => $item): ?>
                                                    <?php $summary = $receivingSummary[$index] ?? ['remaining_quantity' => (float) ($item['quantity'] ?? 0)]; ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($item['description'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="text-end"><?php echo $fmt($summary['remaining_quantity'] ?? 0); ?></td>
                                                        <td class="text-end" style="max-width:140px;">
                                                            <input type="number" step="0.01" min="0" name="items[<?php echo $index; ?>][receive_quantity]" class="form-control form-control-sm text-end" value="<?php echo htmlspecialchars($fmt($summary['remaining_quantity'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-sm po-compact-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Ngày nhận</th>
                                                <th>Loại</th>
                                                <th>Ghi chú</th>
                                                <th>Người nhận</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($receivings === []): ?>
                                            <tr><td colspan="4" class="text-center text-secondary py-4">Chưa có lịch sử nhận hàng.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($receivings as $receiving): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($receiving['received_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($receiveTypeLabels[$receiving['receive_type'] ?? ''] ?? ($receiving['receive_type'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($receiving['remark'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) (($receiving['acted_by_name'] ?? '') !== '' ? $receiving['acted_by_name'] : ($receiving['acted_by_username'] ?? $receiving['acted_by'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card po-section-card" id="extra-costs">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#poExtraCostCollapse" aria-expanded="true">4. Chi phí phát sinh</button>
                        </h2>
                        <div id="poExtraCostCollapse" class="accordion-collapse collapse show" data-bs-parent="#purchaseOrderWorkflowAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($actions['can_add_extra_cost'])): ?>
                                    <form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/add-extra-cost?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 align-items-end mb-4">
                                        <div class="col-12 col-md-2">
                                            <label class="form-label fw-semibold">Loại</label>
                                            <select name="cost_type" class="form-select erp-select">
                                                <option value="shipping_cost">Chi phí vận chuyển</option>
                                                <option value="extra_cost">Chi phí khác</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label fw-semibold">Tên chi phí</label>
                                            <input type="text" name="label" class="form-control erp-field" maxlength="150" placeholder="VD: Vận chuyển nội địa">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label fw-semibold">Số tiền</label>
                                            <input type="number" step="0.01" min="0" name="amount" class="form-control erp-field" value="0.00">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label fw-semibold">Ghi chú</label>
                                            <input type="text" name="remark" class="form-control erp-field" maxlength="255">
                                        </div>
                                        <div class="col-12 col-md-1 d-grid">
                                            <button type="submit" class="btn btn-outline-dark erp-btn">Thêm</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-semibold">Danh sách chi phí</div>
                                    <div class="text-secondary">Tổng: <?php echo $fmt($purchaseOrder['extra_cost_total'] ?? 0); ?></div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm po-compact-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Loại</th>
                                                <th>Tên chi phí</th>
                                                <th class="text-end">Số tiền</th>
                                                <th>Ghi chú</th>
                                                <th>Ngày tạo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($extraCosts === []): ?>
                                            <tr><td colspan="5" class="text-center text-secondary py-4">Chưa có chi phí phát sinh.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($extraCosts as $cost): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($costTypeLabels[$cost['cost_type'] ?? ''] ?? ($cost['cost_type'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($cost['label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo $fmt($cost['amount'] ?? 0); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($cost['remark'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($cost['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card po-section-card" id="payments">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#poPaymentCollapse" aria-expanded="true">5. Thanh toán</button>
                        </h2>
                        <div id="poPaymentCollapse" class="accordion-collapse collapse show" data-bs-parent="#purchaseOrderWorkflowAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($actions['can_create_payment'])): ?>
                                    <div id="po-payment-create-collapse" class="collapse mb-4">
                                        <form method="post" action="<?php echo htmlspecialchars(app_url('/payments/store-voucher?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 align-items-end">
                                            <div class="col-12 col-md-2">
                                                <label class="form-label fw-semibold">Amount</label>
                                                <input type="number" step="0.01" min="0.01" max="<?php echo htmlspecialchars(number_format((float) ($purchaseOrder['remaining_amount'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" name="amount" class="form-control erp-field" value="<?php echo htmlspecialchars(number_format((float) ($purchaseOrder['remaining_amount'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label fw-semibold">Payment date</label>
                                                <input type="date" name="payment_date" class="form-control erp-field" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label fw-semibold">Method</label>
                                                <select name="payment_method" class="form-select erp-select">
                                                    <option value="cash">Cash</option>
                                                    <option value="bank_transfer">Bank transfer</option>
                                                    <option value="card">Card</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label fw-semibold">Reference no</label>
                                                <input type="text" name="reference_no" class="form-control erp-field" maxlength="80">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label fw-semibold">Note</label>
                                                <input type="text" name="note" class="form-control erp-field" maxlength="255">
                                            </div>
                                            <div class="col-12 col-md-1 d-grid">
                                                <button type="submit" class="btn btn-dark erp-btn">Create</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <div class="table-responsive">
                                    <table class="table table-sm po-compact-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Method</th>
                                                <th>Reference</th>
                                                <th class="text-end">Amount</th>
                                                <th>Status</th>
                                                <th>Note</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($payments === []): ?>
                                            <tr><td colspan="8" class="text-center text-secondary py-4">Chưa có payment.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($payment['payment_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>Voucher</td>
                                                    <td><?php echo htmlspecialchars((string) ($payment['payment_method_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($payment['reference_no'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo $fmt($payment['amount'] ?? 0); ?></td>
                                                    <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($payment['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) ($payment['status_label'] ?? 'Draft'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td><?php echo htmlspecialchars((string) ($payment['note'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end">
                                                        <?php if (!empty($payment['can_confirm'])): ?>
                                                            <form method="post" action="<?php echo htmlspecialchars(app_url('/payments/confirm?id=' . (int) ($payment['id'] ?? 0) . '&source=purchase_order&source_id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Confirm this payment?');">
                                                                <button type="submit" class="btn btn-dark btn-sm">Confirm</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-secondary small"><?php echo htmlspecialchars((string) ($payment['confirmed_by_display'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
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

                    <div class="accordion-item erp-card po-section-card" id="stock-in">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#poStockInCollapse" aria-expanded="true">6. Nhập kho</button>
                        </h2>
                        <div id="poStockInCollapse" class="accordion-collapse collapse show" data-bs-parent="#purchaseOrderWorkflowAccordion">
                            <div class="accordion-body">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if (!empty($actions['can_submit_stock_in'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/submit-stock-in?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="btn btn-outline-primary erp-btn">Trình duyệt nhập kho</button></form><?php endif; ?>
                                    <?php if (!empty($actions['can_stock_in_approve'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/approve-stock-in?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="btn btn-primary erp-btn">Duyệt nhập kho</button></form><?php endif; ?>
                                    <?php if (!empty($actions['can_close'])): ?><form method="post" action="<?php echo htmlspecialchars(app_url('/purchase-orders/close?id=' . (int) ($purchaseOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="btn btn-success erp-btn">Đóng đơn mua</button></form><?php endif; ?>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Trạng thái đơn mua</div><div class="fw-bold"><?php echo htmlspecialchars((string) ($purchaseOrder['status_label'] ?? $status), ENT_QUOTES, 'UTF-8'); ?></div></div></div>
                                    <div class="col-12 col-md-4"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Mã phiếu kho</div><div class="fw-bold"><?php echo htmlspecialchars((string) ($stockTransaction['txn_no'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div></div>
                                    <div class="col-12 col-md-4"><div class="erp-card-muted rounded-4 p-3"><div class="text-secondary small">Ngày phiếu</div><div class="fw-bold"><?php echo htmlspecialchars((string) ($stockTransaction['txn_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($actions['can_view_log'])): ?>
                        <div class="accordion-item erp-card po-section-card" id="activity-log">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#poLogCollapse" aria-expanded="false">7. Nhật ký hoạt động</button>
                            </h2>
                            <div id="poLogCollapse" class="accordion-collapse collapse" data-bs-parent="#purchaseOrderWorkflowAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm po-compact-table align-middle mb-0">
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
                                                        <td><?php echo htmlspecialchars((string) ($statusLabels[$log['old_status'] ?? ''] ?? ($log['old_status'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($statusLabels[$log['new_status'] ?? ''] ?? ($log['new_status'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
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
