<?php
$activeSidebar = $activeSidebar ?? 'quotations';
$pageTitle = $pageTitle ?? 'Chi tiết báo giá';
$quotation = $quotation ?? [];
$logs = $quotation['logs'] ?? [];
$workflow = $quotation['workflow'] ?? [];
$trackingSteps = $quotation['tracking_steps'] ?? [];
$items = $quotation['items'] ?? [];
$canViewLog = has_permission('quotation.view_log');
$canEdit = has_permission('quotation.update') && !empty($workflow['can_edit']);
$canSubmit = has_permission('quotation.submit') && !empty($workflow['can_submit']);
$canApprove = has_permission('quotation.approve') && !empty($workflow['can_approve']);
$canReject = has_permission('quotation.reject') && !empty($workflow['can_reject']);
$canCancel = has_permission('quotation.cancel') && !empty($workflow['can_cancel']);
$canConvert = has_permission('sales_order.create') && !empty($workflow['can_convert']);
$successFlash = get_flash('success');
$errorFlash = get_flash('error');
$modeBadgeMap = [
    'estimate' => 'warning',
    'component' => 'primary',
    'material' => 'info',
    'service' => 'secondary',
];
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
    <style>
        <?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?>
        body { font-size: 12.9px; }
        .quote-detail-wrap { font-size: 12.9px; }
        .quote-section .accordion-button { font-size: 13px; font-weight: 600; padding: 12px 16px; }
        .quote-section .accordion-button:not(.collapsed) { background: #f7f8fa; color: #111827; box-shadow: none; }
        .quote-section .accordion-body { padding: 16px; }
        .quote-items-table th, .quote-items-table td, .quote-log-table th, .quote-log-table td { font-size: 12.6px; vertical-align: top; }
        .quote-action-bar { position: sticky; top: 82px; z-index: 15; }
        .quote-summary-card dt, .quote-summary-card dd { font-size: 12.7px; }
        .quote-meta-grid strong { color: #111827; }
    </style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="py-4 py-xl-5 quote-detail-wrap">
            <div class="container-fluid px-3 px-xl-4">
                <?php if ($successFlash !== null): ?>
                    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($successFlash, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($errorFlash !== null): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($errorFlash, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php
                $processTitle = 'Theo dõi quy trình báo giá';
                $processSubtitle = 'Báo giá ' . (string) ($quotation['code'] ?? '');
                $processSteps = $trackingSteps;
                include base_path('app/Modules/Home/Views/partials/process_timeline.php');
                ?>

                <div class="erp-card p-3 p-lg-4 quote-action-bar mb-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-1">Báo giá</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h3 class="h5 mb-0 fw-semibold"><?php echo htmlspecialchars((string) ($quotation['code'] ?? 'QT'), ENT_QUOTES, 'UTF-8'); ?></h3>
                                <span class="badge text-bg-<?php echo htmlspecialchars((string) ($quotation['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($quotation['status_label'] ?? 'Nháp'), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn">Quay lại</a>
                            <?php if ($canEdit): ?>
                                <a href="<?php echo htmlspecialchars(app_url('/quotations/edit?id=' . (int) ($quotation['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn">Chỉnh sửa</a>
                            <?php endif; ?>
                            <?php if ($canSubmit): ?>
                                <form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/submit?id=' . (int) ($quotation['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="d-inline">
                                    <button type="submit" class="btn btn-dark erp-btn">Trình duyệt</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canApprove): ?>
                                <form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/approve?id=' . (int) ($quotation['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="d-inline">
                                    <button type="submit" class="btn btn-success erp-btn">Duyệt</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canReject): ?>
                                <form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/reject?id=' . (int) ($quotation['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="d-inline" onsubmit="return confirm('Xác nhận từ chối báo giá này?');">
                                    <button type="submit" class="btn btn-outline-danger erp-btn">Từ chối</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canCancel): ?>
                                <form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/cancel?id=' . (int) ($quotation['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="d-inline" onsubmit="return confirm('Xác nhận hủy báo giá này?');">
                                    <button type="submit" class="btn btn-outline-secondary erp-btn">Hủy</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canConvert): ?>
                                <a href="<?php echo htmlspecialchars(app_url('/orders/create?quotation_id=' . (int) ($quotation['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary erp-btn">Tạo đơn bán hàng</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="accordion quote-section" id="quote-detail-accordion">
                    <div class="accordion-item border-0 erp-card mb-3" id="quote-info">
                        <h2 class="accordion-header">
                            <button class="accordion-button rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#quote-info-collapse" aria-expanded="true" aria-controls="quote-info-collapse">Thông tin báo giá</button>
                        </h2>
                        <div id="quote-info-collapse" class="accordion-collapse collapse show" data-bs-parent="#quote-detail-accordion">
                            <div class="accordion-body">
                                <div class="row g-4">
                                    <div class="col-12 col-xl-8">
                                        <div class="row g-3 quote-meta-grid">
                                            <div class="col-12 col-md-4"><strong>Mã báo giá:</strong><div><?php echo htmlspecialchars((string) ($quotation['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                            <div class="col-12 col-md-4"><strong>Ngày báo giá:</strong><div><?php echo htmlspecialchars((string) ($quotation['quote_date'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                            <div class="col-12 col-md-4"><strong>Hiệu lực đến:</strong><div><?php echo htmlspecialchars((string) ($quotation['expired_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                            <div class="col-12 col-md-4"><strong>Trạng thái:</strong><div><?php echo htmlspecialchars((string) ($quotation['status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                            <div class="col-12 col-md-8"><strong>Ghi chú:</strong><div><?php echo nl2br(htmlspecialchars((string) ($quotation['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-xl-4">
                                        <div class="erp-section-panel p-3 quote-summary-card">
                                            <dl class="row mb-0">
                                                <dt class="col-6">Tạm tính</dt><dd class="col-6 text-end"><?php echo htmlspecialchars($money($quotation['subtotal'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></dd>
                                                <dt class="col-6">Chiết khấu</dt><dd class="col-6 text-end"><?php echo htmlspecialchars($money($quotation['discount_amount'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></dd>
                                                <dt class="col-6">Thuế (<?php echo htmlspecialchars(number_format((float) ($quotation['tax_percent'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>%)</dt><dd class="col-6 text-end"><?php echo htmlspecialchars($money($quotation['tax_amount'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></dd>
                                                <dt class="col-6 fw-semibold">Tổng cộng</dt><dd class="col-6 text-end fw-semibold"><?php echo htmlspecialchars($money($quotation['total_amount'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 erp-card mb-3" id="quote-customer-info">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#quote-customer-collapse" aria-expanded="false" aria-controls="quote-customer-collapse">Khách hàng</button>
                        </h2>
                        <div id="quote-customer-collapse" class="accordion-collapse collapse" data-bs-parent="#quote-detail-accordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4"><strong>Mã / tên khách hàng:</strong><div><?php echo htmlspecialchars((string) (($quotation['customer_code'] ?? '') . ' - ' . ($quotation['customer_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12 col-md-4"><strong>Liên hệ:</strong><div><?php echo htmlspecialchars((string) ($quotation['customer_contact_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12 col-md-4"><strong>Điện thoại:</strong><div><?php echo htmlspecialchars((string) ($quotation['customer_phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12 col-md-4"><strong>Email:</strong><div><?php echo htmlspecialchars((string) ($quotation['customer_email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12 col-md-4"><strong>Mã số thuế:</strong><div><?php echo htmlspecialchars((string) ($quotation['customer_tax_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12"><strong>Địa chỉ:</strong><div><?php echo nl2br(htmlspecialchars((string) ($quotation['customer_address'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 erp-card mb-3" id="quote-items">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#quote-items-collapse" aria-expanded="false" aria-controls="quote-items-collapse">Danh sách dòng báo giá</button>
                        </h2>
                        <div id="quote-items-collapse" class="accordion-collapse collapse" data-bs-parent="#quote-detail-accordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table quote-items-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Loại dòng</th>
                                                <th>Mã</th>
                                                <th>Mô tả</th>
                                                <th>Quy cách</th>
                                                <th>ĐVT</th>
                                                <th class="text-end">SL</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-end">CK</th>
                                                <th class="text-end">Thành tiền</th>
                                                <th>Trạng thái kỹ thuật</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($items === []): ?>
                                            <tr><td colspan="11" class="text-center text-secondary py-4">Chưa có dòng báo giá.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($items as $item): ?>
                                                <tr>
                                                    <td><?php echo (int) ($item['line_no'] ?? 0); ?></td>
                                                    <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($modeBadgeMap[$item['item_mode'] ?? 'estimate'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($item['item_mode_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td><?php echo htmlspecialchars((string) (($item['master_code'] ?? '') !== '' ? $item['master_code'] : '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="fw-semibold"><?php echo htmlspecialchars((string) ($item['description'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($item['spec_summary'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($item['unit'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo number_format((float) ($item['quantity'] ?? 0), 2); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars($money($item['unit_price'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end"><?php echo htmlspecialchars($money($item['discount_amount'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="text-end fw-semibold"><?php echo htmlspecialchars($money($item['total_amount'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if (!empty($item['is_component_ready'])): ?>
                                                            <span class="badge text-bg-success rounded-pill"><?php echo htmlspecialchars((string) ($item['engineering_status_label'] ?? 'Đã chuẩn hóa kỹ thuật'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php elseif (!empty($item['is_estimate_item'])): ?>
                                                            <span class="badge text-bg-warning rounded-pill"><?php echo htmlspecialchars((string) ($item['engineering_status_label'] ?? 'Chưa chuẩn hóa kỹ thuật'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-secondary">Không áp dụng</span>
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

                    <div class="accordion-item border-0 erp-card mb-3" id="quote-workflow">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#quote-workflow-collapse" aria-expanded="false" aria-controls="quote-workflow-collapse">Phê duyệt / Quy trình</button>
                        </h2>
                        <div id="quote-workflow-collapse" class="accordion-collapse collapse" data-bs-parent="#quote-detail-accordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-12 col-lg-4"><strong>Trạng thái hiện tại:</strong><div><?php echo htmlspecialchars((string) ($quotation['status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12 col-lg-4"><strong>Cập nhật lần cuối:</strong><div><?php echo htmlspecialchars((string) ($quotation['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12 col-lg-4"><strong>Tạo lúc:</strong><div><?php echo htmlspecialchars((string) ($quotation['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php if ($canSubmit): ?><span class="badge text-bg-dark">Có thể trình duyệt</span><?php endif; ?>
                                            <?php if ($canApprove): ?><span class="badge text-bg-success">Có thể duyệt</span><?php endif; ?>
                                            <?php if ($canReject): ?><span class="badge text-bg-danger">Có thể từ chối</span><?php endif; ?>
                                            <?php if ($canCancel): ?><span class="badge text-bg-secondary">Có thể hủy</span><?php endif; ?>
                                            <?php if ($canConvert): ?><span class="badge text-bg-primary">Có thể tạo đơn bán</span><?php endif; ?>
                                            <?php if (!$canSubmit && !$canApprove && !$canReject && !$canCancel && !$canConvert): ?><span class="text-secondary">Không có thao tác workflow khả dụng ở trạng thái hiện tại.</span><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($canViewLog): ?>
                        <div class="accordion-item border-0 erp-card mb-3" id="quote-activity-log">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#quote-log-collapse" aria-expanded="false" aria-controls="quote-log-collapse">Nhật ký hoạt động</button>
                            </h2>
                            <div id="quote-log-collapse" class="accordion-collapse collapse" data-bs-parent="#quote-detail-accordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table quote-log-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Thời gian</th>
                                                    <th>Người thao tác</th>
                                                    <th>Hành động</th>
                                                    <th>Chuyển trạng thái</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if ($logs === []): ?>
                                                <tr><td colspan="5" class="text-center text-secondary py-4">Chưa có nhật ký hoạt động.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($logs as $log): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) ($log['acted_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['actor_name'] ?? 'Hệ thống'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['action_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <?php if (!empty($log['new_status'])): ?>
                                                                <?php echo htmlspecialchars((string) ($log['old_status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                                                <i class="bi bi-arrow-right mx-1"></i>
                                                                <?php echo htmlspecialchars((string) ($log['new_status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php else: ?>
                                                                <span class="text-secondary">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo nl2br(htmlspecialchars((string) ($log['remark'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></td>
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
