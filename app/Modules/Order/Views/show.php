<?php
$activeSidebar = $activeSidebar ?? 'orders';
$pageTitle = $pageTitle ?? 'Chi tiết đơn bán';
$order = $order ?? [];
$status = (string) ($status ?? '');
$statusMap = [
    'created' => ['Tạo đơn hàng thành công.', 'success'],
    'updated' => ['Cập nhật đơn hàng thành công.', 'success'],
];
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
$modeBadgeMap = [
    'estimate' => 'warning',
    'component' => 'primary',
    'material' => 'info',
    'service' => 'secondary',
];
$canUpdate = has_permission('sales_order.update');
$canConfirm = has_permission('sales_order.confirm') || has_permission('sales_order.approve');
$canDeliver = has_permission('sales_order.deliver') || has_permission('stock.create') || has_permission('stock.approve');
$canViewLog = has_permission('sales_order.view_log') || $canUpdate || $canConfirm || $canDeliver;
$canCreateProduction = has_permission('production.create');
$canCreateComponent = has_permission('component.create');
$canCreateBom = has_permission('bom.create');
$payments = $order['payments'] ?? [];
$canCreatePayment = !empty($order['can_create_payment']);
$canViewPayments = !empty($order['can_view_payments']);
$processSteps = match ((string) ($order['status'] ?? 'draft')) {
    'delivered', 'closed' => [
        ['label' => 'Đã xác nhận', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['order_date'] ?? '-', 'icon' => 'clipboard-check'],
        ['label' => 'Kiểm tồn', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'boxes'],
        ['label' => 'Sản xuất', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'gear-wide-connected'],
        ['label' => 'Sẵn sàng giao', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'check2-circle'],
        ['label' => 'Đã giao', 'state' => 'completed', 'badge' => 'Đã giao', 'time' => $order['updated_at'] ?? '-', 'icon' => 'truck'],
    ],
    'ready_to_deliver' => [
        ['label' => 'Đã xác nhận', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['order_date'] ?? '-', 'icon' => 'clipboard-check'],
        ['label' => 'Kiểm tồn', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'boxes'],
        ['label' => 'Sản xuất', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'gear-wide-connected'],
        ['label' => 'Sẵn sàng giao', 'state' => 'current', 'badge' => 'Sẵn sàng', 'time' => $order['updated_at'] ?? '-', 'icon' => 'check2-circle'],
        ['label' => 'Đã giao', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chưa giao', 'icon' => 'truck'],
    ],
    'partially_delivered' => [
        ['label' => 'Đã xác nhận', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['order_date'] ?? '-', 'icon' => 'clipboard-check'],
        ['label' => 'Kiểm tồn', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'boxes'],
        ['label' => 'Sản xuất', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'gear-wide-connected'],
        ['label' => 'Sẵn sàng giao', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'check2-circle'],
        ['label' => 'Đã giao', 'state' => 'current', 'badge' => 'Một phần', 'time' => 'Đang giao nhiều đợt', 'icon' => 'truck'],
    ],
    'waiting_production' => [
        ['label' => 'Đã xác nhận', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['order_date'] ?? '-', 'icon' => 'clipboard-check'],
        ['label' => 'Kiểm tồn', 'state' => 'completed', 'badge' => 'Hoàn tất', 'time' => $order['updated_at'] ?? '-', 'icon' => 'boxes'],
        ['label' => 'Sản xuất', 'state' => 'current', 'badge' => 'Đang xử lý', 'time' => $order['updated_at'] ?? '-', 'icon' => 'gear-wide-connected'],
        ['label' => 'Sẵn sàng giao', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chờ sản xuất xong', 'icon' => 'check2-circle'],
        ['label' => 'Đã giao', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chưa giao', 'icon' => 'truck'],
    ],
    default => [
        ['label' => 'Đã xác nhận', 'state' => (string) ($order['status'] ?? 'draft') === 'draft' ? 'pending' : 'current', 'badge' => (string) ($order['status'] ?? 'draft') === 'draft' ? 'Chưa tới' : 'Đang xử lý', 'time' => $order['order_date'] ?? '-', 'icon' => 'clipboard-check'],
        ['label' => 'Kiểm tồn', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chưa kiểm tồn', 'icon' => 'boxes'],
        ['label' => 'Sản xuất', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chưa phát sinh', 'icon' => 'gear-wide-connected'],
        ['label' => 'Sẵn sàng giao', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chưa sẵn sàng', 'icon' => 'check2-circle'],
        ['label' => 'Đã giao', 'state' => 'pending', 'badge' => 'Chưa tới', 'time' => 'Chưa giao', 'icon' => 'truck'],
    ],
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
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?> body{font-size:13px}.so-items-table th,.so-items-table td,.so-deliveries-table th,.so-deliveries-table td,.so-log-table th,.so-log-table td{font-size:12.75px;vertical-align:top}.delivery-qty-input{max-width:110px}.so-action-bar{justify-content:center}.so-delivery-form-card,.so-payment-form-card{scroll-margin-top:128px}</style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-4 px-xl-5">
                <?php if ($success = get_flash('success')): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($error = get_flash('error')): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if (isset($statusMap[$status])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars((string) $statusMap[$status][1], ENT_QUOTES, 'UTF-8'); ?> rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $statusMap[$status][0], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <div class="erp-detail-section__eyebrow">Đơn bán / MTO</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <h2 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) ($order['code'] ?? 'SO'), ENT_QUOTES, 'UTF-8'); ?></h2>
                            <span class="badge text-bg-<?php echo htmlspecialchars((string) ($statusBadgeMap[$order['status'] ?? 'draft'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($order['status_label'] ?? ($order['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge text-bg-secondary px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($order['priority_label'] ?? 'Bình thường'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="text-secondary"><?php echo htmlspecialchars((string) (($order['customer_code'] ?? '') . ' - ' . ($order['customer_name'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap so-action-bar">
                        <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                        <?php if ($canConfirm && ($order['status'] ?? '') === 'draft'): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/confirm?id=' . (int) ($order['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xác nhận đơn bán hàng này?');">
                                <button type="submit" class="btn btn-outline-dark rounded-4 px-4">Xác nhận</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canDeliver && !empty($order['can_mark_ready'])): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/mark-ready?id=' . (int) ($order['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Đánh dấu đơn bán sẵn sàng giao?');">
                                <button type="submit" class="btn btn-outline-dark rounded-4 px-4">Đánh dấu sẵn sàng giao</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canDeliver && !empty($order['can_create_delivery'])): ?>
                            <button type="button" class="btn btn-dark rounded-4 px-4" data-bs-toggle="collapse" data-bs-target="#so-delivery-create-collapse" data-scroll-target="#so-delivery-create-collapse" aria-expanded="false" aria-controls="so-delivery-create-collapse">Tạo phiếu giao</button>
                        <?php endif; ?>
                        <?php if ($canCreatePayment): ?>
                            <button type="button" class="btn btn-outline-dark rounded-4 px-4" data-bs-toggle="collapse" data-bs-target="#so-payment-create-collapse" data-scroll-target="#so-payment-create-collapse" aria-expanded="false" aria-controls="so-payment-create-collapse">Create Receipt</button>
                        <?php endif; ?>
                        <?php if ($canViewPayments): ?>
                            <a href="#payments" class="btn btn-outline-secondary rounded-4 px-4">View Payments</a>
                        <?php endif; ?>
                        <?php if ($canUpdate): ?><a href="<?php echo htmlspecialchars(app_url('/orders/edit?id=' . (int) ($order['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Chỉnh sửa</a><?php endif; ?>
                    </div>
                </div>

                <?php
                $processTitle = 'Tracking thực hiện đơn bán';
                $processSubtitle = 'Từ xác nhận đơn, kiểm tồn, sản xuất hoặc thực hiện dịch vụ đến sẵn sàng giao.';
                include base_path('app/Modules/Home/Views/partials/process_timeline.php');
                ?>

                <div class="accordion d-grid gap-4" id="salesOrderAccordion">
                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#soInfoCollapse" aria-expanded="true">Thông tin đơn hàng</button></h2>
                        <div id="soInfoCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4"><strong>Ngày đơn:</strong> <?php echo htmlspecialchars((string) ($order['order_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Ngày giao:</strong> <?php echo htmlspecialchars((string) ($order['due_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Báo giá nguồn:</strong> <?php echo htmlspecialchars((string) ($order['quotation_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-6"><strong>Liên hệ:</strong> <?php echo htmlspecialchars((string) ($order['customer_contact_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-6"><strong>Điện thoại:</strong> <?php echo htmlspecialchars((string) ($order['customer_phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12"><strong>Ghi chú:</strong> <span class="text-secondary"><?php echo nl2br(htmlspecialchars((string) ($order['note'] ?? 'Chưa có ghi chú.'), ENT_QUOTES, 'UTF-8')); ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#soItemsCollapse" aria-expanded="true">Hàng hóa / Trạng thái giao</button></h2>
                        <div id="soItemsCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table so-items-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Mode</th>
                                                <th>Mã / Mô tả</th>
                                                <th class="text-end">SL đặt</th>
                                                <th class="text-end">SL sẵn sàng giao</th>
                                                <th class="text-end">SL đã giao</th>
                                                <th class="text-end">SL còn lại</th>
                                                <th class="text-end">Tồn hiện tại</th>
                                                <th class="text-end">Thiếu hụt</th>
                                                <th>Trạng thái giao</th>
                                                <th>Work Order</th>
                                                <th class="text-end">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (($order['items'] ?? []) === []): ?>
                                            <tr><td colspan="12" class="text-center text-secondary py-5">Chưa có dòng đơn hàng.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($order['items'] as $index => $item): ?>
                                                <tr>
                                                    <td><?php echo (int) $index + 1; ?></td>
                                                    <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($modeBadgeMap[$item['item_mode'] ?? 'estimate'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($item['item_mode_label'] ?? ($item['item_mode'] ?? 'estimate')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars((string) ($item['description'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="text-secondary small"><?php echo htmlspecialchars((string) (($item['master_code'] ?? '') !== '' ? $item['master_code'] : '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </td>
                                                    <td class="text-end"><?php echo number_format((float) ($item['ordered_qty'] ?? $item['quantity'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo ($item['item_mode'] ?? '') === 'service' ? '-' : number_format((float) ($item['ready_qty'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo ($item['item_mode'] ?? '') === 'service' ? '-' : number_format((float) ($item['delivered_qty'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo ($item['item_mode'] ?? '') === 'service' ? '-' : number_format((float) ($item['remaining_qty'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo ($item['item_mode'] ?? '') === 'service' ? '-' : number_format((float) ($item['available_qty'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo ($item['item_mode'] ?? '') === 'service' ? '-' : number_format((float) ($item['shortage_qty'] ?? 0), 2); ?></td>
                                                    <td>
                                                        <?php if (($item['engineering_status_label'] ?? '') !== '-'): ?>
                                                            <div class="small fw-semibold text-secondary"><?php echo htmlspecialchars((string) ($item['engineering_status_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                        <span class="badge text-bg-<?php echo htmlspecialchars((string) ($item['fulfillment_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($item['fulfillment_status_label'] ?? 'Chờ xử lý'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <div class="small text-secondary mt-1"><?php echo htmlspecialchars((string) ($item['stock_status_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php if (($item['fulfillment_status'] ?? '') === 'ready' && ($item['item_mode'] ?? '') !== 'service'): ?>
                                                            <div class="small text-secondary mt-1">Đã sẵn sàng giao, chờ kho xuất hàng.</div>
                                                        <?php endif; ?>
                                                        <?php if (($item['item_mode'] ?? '') === 'service'): ?>
                                                            <div class="small text-secondary mt-1">Dịch vụ không qua thao tác giao hàng và không tạo phiếu xuất kho.</div>
                                                            <div class="small text-secondary mt-1">Trạng thái thực hiện: <?php echo htmlspecialchars((string) ($item['service_order_status_label'] ?? 'Chưa tạo lệnh'), ENT_QUOTES, 'UTF-8'); ?><?php echo !empty($item['service_order_assigned_name']) ? ' / ' . htmlspecialchars((string) $item['service_order_assigned_name'], ENT_QUOTES, 'UTF-8') : ''; ?></div>
                                                        <?php endif; ?>
                                                        <?php if (($item['item_mode'] ?? '') === 'estimate'): ?>
                                                            <div class="small text-secondary mt-1">Cần tạo mã bán thành phẩm trước khi lập BOM và sản xuất.</div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (($item['item_mode'] ?? '') === 'service'): ?>
                                                            <?php if (!empty($item['service_order_url']) && !empty($item['can_view_service_order'])): ?>
                                                                <a href="<?php echo htmlspecialchars((string) $item['service_order_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm">Xem <?php echo htmlspecialchars((string) ($item['service_order_code'] ?? 'SVO'), ENT_QUOTES, 'UTF-8'); ?></a>
                                                            <?php else: ?>
                                                                <span class="text-secondary small"><?php echo htmlspecialchars((string) ($item['service_order_status_label'] ?? 'Chưa có lệnh dịch vụ'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php endif; ?>
                                                        <?php elseif (!empty($item['production_order_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars((string) $item['production_order_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm">Xem <?php echo htmlspecialchars((string) ($item['production_order_code'] ?? 'LSX'), ENT_QUOTES, 'UTF-8'); ?></a>
                                                        <?php else: ?>
                                                            <span class="text-secondary small"><?php echo htmlspecialchars((string) (($item['production_block_reason'] ?? '') !== '' ? $item['production_block_reason'] : 'Chưa có LSX'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-flex flex-column gap-2 align-items-end">
                                                            <?php if ($canCreateComponent && !empty($item['can_create_component'])): ?>
                                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/create-component?id=' . (int) ($order['id'] ?? 0) . '&item_id=' . (int) ($item['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Tạo mã bán thành phẩm cho dòng estimate này?');">
                                                                    <button type="submit" class="btn btn-outline-dark btn-sm">Tạo mã bán thành phẩm</button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['component_detail_url'])): ?><a href="<?php echo htmlspecialchars((string) $item['component_detail_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">Xem component</a><?php endif; ?>
                                                            <?php if ($canCreateBom && !empty($item['bom_create_url'])): ?><a href="<?php echo htmlspecialchars((string) $item['bom_create_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark btn-sm">Tạo BOM</a><?php endif; ?>
                                                            <?php if (!empty($item['bom_show_url'])): ?><a href="<?php echo htmlspecialchars((string) $item['bom_show_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">Xem BOM</a><?php endif; ?>
                                                            <?php if (($item['item_mode'] ?? '') === 'service' && !empty($item['service_order_url']) && !empty($item['can_view_service_order'])): ?><a href="<?php echo htmlspecialchars((string) $item['service_order_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-sm">Xem Service Order</a><?php endif; ?>
                                                            <?php if (($item['item_mode'] ?? '') === 'service' && !empty($item['can_start_service_order'])): ?>
                                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/service-orders/start?id=' . (int) ($item['service_order_id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bắt đầu thực hiện dịch vụ này?');">
                                                                    <button type="submit" class="btn btn-outline-dark btn-sm">Bắt đầu</button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <?php if (($item['item_mode'] ?? '') === 'service' && !empty($item['can_complete_service_order'])): ?>
                                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/service-orders/complete?id=' . (int) ($item['service_order_id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xác nhận hoàn thành dịch vụ này?');">
                                                                    <button type="submit" class="btn btn-dark btn-sm">Hoàn thành</button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <?php if ($canDeliver && !empty($item['can_create_delivery'])): ?>
                                                                <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="collapse" data-bs-target="#so-delivery-create-collapse" data-scroll-target="#so-delivery-create-collapse" aria-expanded="false" aria-controls="so-delivery-create-collapse">Tạo phiếu giao</button>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['can_create_production_order']) && $canCreateProduction): ?>
                                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/production-orders/create-from-sales-order?id=' . (int) ($order['id'] ?? 0) . '&item_id=' . (int) ($item['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Tạo lệnh sản xuất cho phần thiếu hụt này?');">
                                                                    <button type="submit" class="btn btn-dark btn-sm">Tạo lệnh sản xuất</button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
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

                    <div class="accordion-item erp-card border-0" id="payments">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#soFinanceCollapse" aria-expanded="true">Giao hàng / Lịch sử</button></h2>
                        <div id="soFinanceCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <?php if ($canDeliver && !empty($order['can_create_delivery'])): ?>
                                    <div id="so-delivery-create-collapse" class="collapse mb-4 so-delivery-form-card">
                                        <div class="border rounded-4 p-3">
                                            <div class="small text-uppercase text-secondary fw-semibold mb-3">Tạo phiếu giao nháp</div>
                                            <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/create-delivery?id=' . (int) ($order['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-semibold">Ngày giao</label>
                                                        <input type="date" name="delivery_date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-semibold">Chi phí vận chuyển (nếu có)</label>
                                                        <input type="number" step="0.01" min="0" name="shipping_cost" class="form-control" value="0.00">
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label fw-semibold">Ghi chú</label>
                                                        <input type="text" name="note" class="form-control" placeholder="Ghi chú phiếu giao">
                                                    </div>
                                                </div>
                                                <div class="table-responsive mb-3">
                                                    <table class="table so-deliveries-table align-middle mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Item</th>
                                                                <th class="text-end">SL sẵn sàng giao</th>
                                                                <th class="text-end">SL còn lại</th>
                                                                <th class="text-end">SL giao đợt này</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach (($order['items'] ?? []) as $index => $item): ?>
                                                            <?php if (($item['item_mode'] ?? '') === 'service' || (float) ($item['remaining_qty'] ?? 0) <= 0 || (float) ($item['ready_qty'] ?? 0) <= 0): continue; endif; ?>
                                                            <tr>
                                                                <td><?php echo (int) $index + 1; ?></td>
                                                                <td>
                                                                    <div class="fw-semibold"><?php echo htmlspecialchars((string) ($item['description'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                                    <div class="small text-secondary"><?php echo htmlspecialchars((string) ($item['master_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                                </td>
                                                                <td class="text-end"><?php echo number_format((float) ($item['ready_qty'] ?? 0), 2); ?></td>
                                                                <td class="text-end"><?php echo number_format((float) ($item['remaining_qty'] ?? 0), 2); ?></td>
                                                                <td class="text-end">
                                                                    <input type="number" step="0.01" min="0" max="<?php echo htmlspecialchars(number_format((float) min((float) ($item['ready_qty'] ?? 0), (float) ($item['remaining_qty'] ?? 0)), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" name="deliveries[<?php echo (int) ($item['id'] ?? 0); ?>][delivery_qty]" class="form-control form-control-sm text-end delivery-qty-input ms-auto" value="<?php echo htmlspecialchars(number_format((float) min((float) ($item['ready_qty'] ?? 0), (float) ($item['remaining_qty'] ?? 0)), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-dark btn-sm px-4">Tạo phiếu giao</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Phiếu giao hàng</div>
                                <div class="table-responsive mb-4">
                                    <table class="table so-deliveries-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mã phiếu</th>
                                                <th>Ngày giao</th>
                                                <th>Trạng thái</th>
                                                <th class="text-end">Dòng</th>
                                                <th class="text-end">Tổng SL</th>
                                                <th class="text-end">Chi phí vận chuyển</th>
                                                <th>Xuất kho</th>
                                                <th class="text-end">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (($order['deliveries'] ?? []) === []): ?>
                                            <tr><td colspan="8" class="text-center text-secondary py-4">Chưa có phiếu giao hàng.</td></tr>
                                        <?php else: ?>
                                            <?php foreach (($order['deliveries'] ?? []) as $delivery): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) ($delivery['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($delivery['delivery_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($delivery['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($delivery['status_label'] ?? 'Nháp'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td class="text-end"><?php echo (int) ($delivery['item_count'] ?? 0); ?></td>
                                                    <td class="text-end"><?php echo number_format((float) ($delivery['total_delivery_qty'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo (float) ($delivery['shipping_cost'] ?? 0) > 0 ? number_format((float) $delivery['shipping_cost'], 2) : '-'; ?></td>
                                                    <td>
                                                        <?php if (!empty($delivery['stock_transaction_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars((string) $delivery['stock_transaction_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm">Xem phiếu xuất</a>
                                                        <?php else: ?>
                                                            <span class="text-secondary small">Chưa xuất kho</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?php if ($canDeliver && ($delivery['status'] ?? '') === 'draft'): ?>
                                                            <div class="d-flex gap-2 justify-content-end">
                                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/confirm-delivery?id=' . (int) ($order['id'] ?? 0) . '&delivery_id=' . (int) ($delivery['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xác nhận giao hàng và xuất kho?');">
                                                                    <button type="submit" class="btn btn-dark btn-sm">Xác nhận giao</button>
                                                                </form>
                                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/cancel-delivery?id=' . (int) ($order['id'] ?? 0) . '&delivery_id=' . (int) ($delivery['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Hủy phiếu giao hàng này?');">
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hủy</button>
                                                                </form>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-secondary small"><?php echo htmlspecialchars((string) ($delivery['confirmed_by_name'] ?? $delivery['created_by_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-3"><strong>Tạm tính:</strong> <?php echo number_format((float) ($order['subtotal'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-3"><strong>Chiết khấu:</strong> <?php echo number_format((float) ($order['discount_amount'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-3"><strong>Thuế:</strong> <?php echo number_format((float) ($order['tax_amount'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-3"><strong>Tổng cộng:</strong> <?php echo number_format((float) ($order['total_amount'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-3"><strong>Đã thu:</strong> <?php echo number_format((float) ($order['paid_amount'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-3"><strong>Còn lại:</strong> <?php echo number_format((float) ($order['remaining_amount'] ?? 0), 2); ?></div>
                                    <div class="col-12 col-md-3"><strong>Trạng thái thanh toán:</strong> <span class="badge text-bg-<?php echo htmlspecialchars((string) ($order['payment_status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($order['payment_status_label'] ?? 'Chưa thanh toán'), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                    <div class="col-12 col-md-6"><strong>Tạo lúc:</strong> <?php echo htmlspecialchars((string) ($order['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-6"><strong>Cập nhật gần nhất:</strong> <?php echo htmlspecialchars((string) ($order['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>

                                <?php if ($canCreatePayment): ?>
                                    <div id="so-payment-create-collapse" class="collapse mt-4 so-payment-form-card">
                                        <div class="border rounded-4 p-3">
                                            <div class="small text-uppercase text-secondary fw-semibold mb-3">Create payment receipt</div>
                                            <form method="post" action="<?php echo htmlspecialchars(app_url('/payments/store-receipt?id=' . (int) ($order['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
                                                <div class="row g-3">
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-semibold">Amount</label>
                                                        <input type="number" step="0.01" min="0.01" max="<?php echo htmlspecialchars(number_format((float) ($order['remaining_amount'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" name="amount" class="form-control" value="<?php echo htmlspecialchars(number_format((float) ($order['remaining_amount'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-semibold">Payment date</label>
                                                        <input type="date" name="payment_date" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-semibold">Method</label>
                                                        <select name="payment_method" class="form-select">
                                                            <option value="cash">Cash</option>
                                                            <option value="bank_transfer">Bank transfer</option>
                                                            <option value="card">Card</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label fw-semibold">Reference no</label>
                                                        <input type="text" name="reference_no" class="form-control" maxlength="80">
                                                    </div>
                                                    <div class="col-12 col-md-9">
                                                        <label class="form-label fw-semibold">Note</label>
                                                        <input type="text" name="note" class="form-control" maxlength="255">
                                                    </div>
                                                    <div class="col-12 col-md-3 d-flex align-items-end justify-content-end">
                                                        <button type="submit" class="btn btn-dark btn-sm px-4">Create Receipt</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($canViewPayments): ?>
                                    <div class="mt-4">
                                        <div class="small text-uppercase text-secondary fw-semibold mb-3">Payment history</div>
                                        <div class="table-responsive">
                                            <table class="table so-log-table align-middle mb-0">
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
                                                            <td>Receipt</td>
                                                            <td><?php echo htmlspecialchars((string) ($payment['payment_method_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars((string) ($payment['reference_no'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-end"><?php echo number_format((float) ($payment['amount'] ?? 0), 2); ?></td>
                                                            <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($payment['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($payment['status_label'] ?? 'Draft'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                            <td class="text-secondary"><?php echo htmlspecialchars((string) ($payment['note'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-end">
                                                                <?php if (!empty($payment['can_confirm'])): ?>
                                                                    <form method="post" action="<?php echo htmlspecialchars(app_url('/payments/confirm?id=' . (int) ($payment['id'] ?? 0) . '&source=sales_order&source_id=' . (int) ($order['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Confirm this payment?');">
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
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($canViewLog): ?>
                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button collapsed rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#soLogCollapse" aria-expanded="false">Nhật ký hoạt động</button></h2>
                        <div id="soLogCollapse" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table so-log-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Thời gian</th>
                                                <th>Thao tác</th>
                                                <th>Người thực hiện</th>
                                                <th>Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (($order['logs'] ?? []) === []): ?>
                                            <tr><td colspan="4" class="text-center text-secondary py-4">Chưa có nhật ký.</td></tr>
                                        <?php else: ?>
                                            <?php foreach (($order['logs'] ?? []) as $log): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($log['acted_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) ($log['action_label'] ?? ($log['action'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($log['actor_name'] ?? 'Hệ thống'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-secondary"><?php echo htmlspecialchars((string) ($log['remark'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const deliveryCollapseElement = document.getElementById('so-delivery-create-collapse');
    const paymentCollapseElement = document.getElementById('so-payment-create-collapse');

    const bindCollapseScroll = (collapseElement, selector) => {
        if (!collapseElement) {
            return;
        }

        const scrollToCollapse = () => {
            window.setTimeout(() => {
                collapseElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 120);
        };

        document.querySelectorAll(selector).forEach((triggerButton) => {
            triggerButton.addEventListener('click', () => {
                const isShown = collapseElement.classList.contains('show');
                if (isShown) {
                    scrollToCollapse();
                }
            });
        });

        collapseElement.addEventListener('shown.bs.collapse', scrollToCollapse);
    };

    if (!deliveryCollapseElement && !paymentCollapseElement) {
        return;
    }

    bindCollapseScroll(deliveryCollapseElement, '[data-scroll-target="#so-delivery-create-collapse"]');
    bindCollapseScroll(paymentCollapseElement, '[data-scroll-target="#so-payment-create-collapse"]');
});
</script>
</body>
</html>
