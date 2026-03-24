<?php
$activeSidebar = $activeSidebar ?? 'orders';
$pageTitle = $pageTitle ?? 'Biểu mẫu đơn bán';
$formAction = $formAction ?? '/orders/store';
$order = $order ?? [];
$customers = $customers ?? [];
$quotations = $quotations ?? [];
$quotationPayload = $quotationPayload ?? [];
$statuses = $statuses ?? [];
$priorities = $priorities ?? [];
$itemModes = $itemModes ?? [];
$itemPayload = $itemPayload ?? ['materials' => [], 'components' => []];
$suggestedCode = $suggestedCode ?? '';
$errors = $errors ?? [];
$items = $order['items'] ?? [];
$statusLabels = [
    'draft' => 'Nháp',
    'confirmed' => 'Đã xác nhận',
    'waiting_stock' => 'Chờ kiểm tồn',
    'waiting_production' => 'Chờ sản xuất',
    'ready_to_deliver' => 'Sẵn sàng giao',
    'partially_delivered' => 'Giao một phần',
    'delivered' => 'Đã giao',
    'closed' => 'Đã đóng',
    'cancelled' => 'Đã hủy',
];
$priorityLabels = [
    'low' => 'Thấp',
    'normal' => 'Bình thường',
    'high' => 'Cao',
    'urgent' => 'Khẩn',
];
$canApproveStatus = has_permission('sales_order.approve') || has_permission('sales_order.confirm');
$currentStatus = (string) ($order['status'] ?? 'draft');
$visibleStatuses = $canApproveStatus ? $statuses : [$currentStatus];

if ($items === []) {
    $items = [[
        'quotation_item_id' => '',
        'item_mode' => 'estimate',
        'component_id' => '',
        'material_id' => '',
        'temp_code' => '',
        'description' => '',
        'spec_summary' => '',
        'unit' => 'pcs',
        'quantity' => '1.00',
        'unit_price' => '0.00',
        'discount_amount' => '0.00',
        'total_amount' => '0.00',
    ]];
}

$field = static function (string $key, string $default = '') use ($order): string {
    return htmlspecialchars((string) ($order[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};

$itemErrorFor = static function (int|string $index, string $key) use ($errors): ?string {
    return $errors["items.{$index}.{$key}"][0] ?? null;
};

$selectedCustomerId = (string) ($order['customer_id'] ?? '');
$selectedQuotationId = (string) ($order['quotation_id'] ?? '');
$discountAmountValue = (string) ($order['discount_amount'] ?? '0.00');
$taxAmountValue = (string) ($order['tax_amount'] ?? '0.00');
$nextIndex = $items === [] ? 1 : ((int) max(array_keys($items))) + 1;
$isEdit = (int) ($order['id'] ?? 0) > 0;
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
        body { font-size: 13px; }
        .so-items-table th, .so-items-table td { font-size: 12.75px; vertical-align: top; }
        .so-items-table .form-control, .so-items-table .form-select { font-size: 12.75px; min-height: 38px; }
        .so-mode-badge { font-size: 11px; }
    </style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="py-4 py-xl-5">
            <div class="container-fluid px-4 px-xl-5">
                <div class="erp-card p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Sales Order / Engineering handoff</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                    </div>

                    <?php if ($errorFor('items')): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo htmlspecialchars($errorFor('items'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="orderForm" class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Mã đơn bán</label>
                                        <input type="text" name="code" id="orderCodeInput" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($order['code'] ?? $suggestedCode), ENT_QUOTES, 'UTF-8'); ?>" maxlength="30">
                                        <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Báo giá</label>
                                        <select name="quotation_id" id="quotationSelect" class="form-select form-select-lg rounded-4 <?php echo $errorFor('quotation_id') ? 'is-invalid' : ''; ?>">
                                            <option value="">Không lấy từ báo giá</option>
                                            <?php foreach ($quotations as $quotation): ?>
                                                <option value="<?php echo (int) $quotation['id']; ?>" <?php echo $selectedQuotationId === (string) $quotation['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $quotation['code'] . ' - ' . $quotation['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('quotation_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('quotation_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Khách hàng</label>
                                        <select name="customer_id" id="customerSelect" class="form-select form-select-lg rounded-4 <?php echo $errorFor('customer_id') ? 'is-invalid' : ''; ?>">
                                            <option value="">Chọn khách hàng</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo (int) $customer['id']; ?>" <?php echo $selectedCustomerId === (string) $customer['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $customer['code'] . ' - ' . $customer['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('customer_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('customer_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Ngày đơn</label>
                                        <input type="date" name="order_date" id="orderDateInput" class="form-control rounded-4 <?php echo $errorFor('order_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('order_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('order_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('order_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Ngày giao dự kiến</label>
                                        <input type="date" name="due_date" class="form-control rounded-4 <?php echo $errorFor('due_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('due_date'); ?>">
                                        <?php if ($errorFor('due_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('due_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Trạng thái</label>
                                        <select name="status" class="form-select rounded-4 <?php echo $errorFor('status') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($visibleStatuses as $statusOption): ?>
                                                <option value="<?php echo htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $currentStatus === (string) $statusOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) ($statusLabels[$statusOption] ?? $statusOption), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('status')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('status'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Ưu tiên</label>
                                        <select name="priority" class="form-select rounded-4 <?php echo $errorFor('priority') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($priorities as $priorityOption): ?>
                                                <option value="<?php echo htmlspecialchars((string) $priorityOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($order['priority'] ?? 'normal') === (string) $priorityOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) ($priorityLabels[$priorityOption] ?? $priorityOption), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('priority')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('priority'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Ghi chú</label>
                                        <textarea name="note" rows="4" class="form-control rounded-4 <?php echo $errorFor('note') ? 'is-invalid' : ''; ?>"><?php echo $field('note'); ?></textarea>
                                        <?php if ($errorFor('note')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('note'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Tóm tắt tài chính</div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tạm tính</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="subtotalPreview" value="0.00" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Chiết khấu</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountAmountInput" class="form-control rounded-4 <?php echo $errorFor('discount_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($discountAmountValue, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if ($errorFor('discount_amount')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('discount_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Thuế</label>
                                    <input type="number" step="0.01" min="0" name="tax_amount" id="taxAmountInput" class="form-control rounded-4 <?php echo $errorFor('tax_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($taxAmountValue, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if ($errorFor('tax_amount')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('tax_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Tổng cộng</label>
                                    <input type="text" class="form-control rounded-4 bg-light fw-semibold" id="totalPreview" value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                                    <div>
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">Dòng đơn bán</div>
                                        <h4 class="h5 mb-0 fw-semibold">Estimate giữ nguyên cho đến khi chuẩn hóa kỹ thuật</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="addOrderItemButton"><i class="bi bi-plus-lg me-2"></i>Thêm dòng</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table so-items-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:60px;">#</th>
                                                <th style="min-width:170px;">Mode</th>
                                                <th style="min-width:220px;">Master item</th>
                                                <th style="min-width:130px;">Mã tạm</th>
                                                <th style="min-width:280px;">Mô tả / Quy cách</th>
                                                <th style="min-width:100px;">ĐVT</th>
                                                <th style="min-width:110px;">SL</th>
                                                <th style="min-width:125px;">Đơn giá</th>
                                                <th style="min-width:125px;">CK dòng</th>
                                                <th style="min-width:125px;">Thành tiền</th>
                                                <th style="width:80px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="orderItemsBody" data-next-index="<?php echo (int) $nextIndex; ?>">
                                        <?php foreach ($items as $index => $item): ?>
                                            <tr data-item-row>
                                                <td class="text-secondary fw-semibold item-row-number"><?php echo (int) $index + 1; ?></td>
                                                <td>
                                                    <input type="hidden" name="items[<?php echo (int) $index; ?>][quotation_item_id]" class="item-quotation-item-id" value="<?php echo htmlspecialchars((string) ($item['quotation_item_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <select name="items[<?php echo (int) $index; ?>][item_mode]" class="form-select rounded-4 item-mode <?php echo $itemErrorFor($index, 'item_mode') ? 'is-invalid' : ''; ?>">
                                                        <?php foreach ($itemModes as $value => $label): ?>
                                                            <option value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($item['item_mode'] ?? 'estimate') === (string) $value ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="mt-2"><span class="badge text-bg-light rounded-pill so-mode-badge item-mode-badge">Mode</span></div>
                                                </td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][component_id]" class="form-select rounded-4 item-component" data-selected-value="<?php echo htmlspecialchars((string) ($item['component_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></select>
                                                    <select name="items[<?php echo (int) $index; ?>][material_id]" class="form-select rounded-4 item-material mt-2" data-selected-value="<?php echo htmlspecialchars((string) ($item['material_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></select>
                                                    <?php if ($itemErrorFor($index, 'component_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'component_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                    <?php if ($itemErrorFor($index, 'material_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'material_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td><input type="text" name="items[<?php echo (int) $index; ?>][temp_code]" class="form-control rounded-4 item-temp-code" value="<?php echo htmlspecialchars((string) ($item['temp_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" placeholder="CF-TMP"></td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][description]" class="form-control rounded-4 mb-2 item-description <?php echo $itemErrorFor($index, 'description') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" placeholder="Mô tả hàng / bán thành phẩm">
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][spec_summary]" class="form-control rounded-4 item-spec-summary" value="<?php echo htmlspecialchars((string) ($item['spec_summary'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" placeholder="Quy cách, màu, vật liệu...">
                                                    <?php if ($itemErrorFor($index, 'description')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][unit]" class="form-control rounded-4 item-unit <?php echo $itemErrorFor($index, 'unit') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
                                                    <?php if ($itemErrorFor($index, 'unit')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'unit'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][quantity]" class="form-control rounded-4 item-quantity <?php echo $itemErrorFor($index, 'quantity') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity'] ?? '1.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'quantity')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][unit_price]" class="form-control rounded-4 item-unit-price <?php echo $itemErrorFor($index, 'unit_price') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit_price'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'unit_price')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'unit_price'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][discount_amount]" class="form-control rounded-4 item-discount <?php echo $itemErrorFor($index, 'discount_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['discount_amount'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'discount_amount')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'discount_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td><input type="text" class="form-control rounded-4 bg-light item-total" value="<?php echo htmlspecialchars(number_format((float) ($item['total_amount'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly></td>
                                                <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Lưu đơn bán</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<template id="orderItemRowTemplate">
    <tr data-item-row>
        <td class="text-secondary fw-semibold item-row-number">1</td>
        <td>
            <input type="hidden" name="items[__INDEX__][quotation_item_id]" class="item-quotation-item-id">
            <select name="items[__INDEX__][item_mode]" class="form-select rounded-4 item-mode">
                <?php foreach ($itemModes as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-2"><span class="badge text-bg-light rounded-pill so-mode-badge item-mode-badge">Mode</span></div>
        </td>
        <td>
            <select name="items[__INDEX__][component_id]" class="form-select rounded-4 item-component"></select>
            <select name="items[__INDEX__][material_id]" class="form-select rounded-4 item-material mt-2"></select>
        </td>
        <td><input type="text" name="items[__INDEX__][temp_code]" class="form-control rounded-4 item-temp-code" maxlength="50" placeholder="CF-TMP"></td>
        <td>
            <input type="text" name="items[__INDEX__][description]" class="form-control rounded-4 mb-2 item-description" maxlength="255" placeholder="Mô tả hàng / bán thành phẩm">
            <input type="text" name="items[__INDEX__][spec_summary]" class="form-control rounded-4 item-spec-summary" maxlength="255" placeholder="Quy cách, màu, vật liệu...">
        </td>
        <td><input type="text" name="items[__INDEX__][unit]" class="form-control rounded-4 item-unit" value="pcs" maxlength="50"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][quantity]" class="form-control rounded-4 item-quantity" value="1.00"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" class="form-control rounded-4 item-unit-price" value="0.00"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][discount_amount]" class="form-control rounded-4 item-discount" value="0.00"></td>
        <td><input type="text" class="form-control rounded-4 bg-light item-total" value="0.00" readonly></td>
        <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>
<script id="quotationPayloadData" type="application/json"><?php echo json_encode($quotationPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script id="itemPayloadData" type="application/json"><?php echo json_encode($itemPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script id="itemModeLabelsData" type="application/json"><?php echo json_encode($itemModes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const body = document.getElementById('orderItemsBody');
    const template = document.getElementById('orderItemRowTemplate');
    const addButton = document.getElementById('addOrderItemButton');
    const quotationSelect = document.getElementById('quotationSelect');
    const customerSelect = document.getElementById('customerSelect');
    const orderCodeInput = document.getElementById('orderCodeInput');
    const orderDateInput = document.getElementById('orderDateInput');
    const discountInput = document.getElementById('discountAmountInput');
    const taxInput = document.getElementById('taxAmountInput');
    const subtotalPreview = document.getElementById('subtotalPreview');
    const totalPreview = document.getElementById('totalPreview');
    const quotationPayload = JSON.parse(document.getElementById('quotationPayloadData')?.textContent || '{}');
    const itemPayload = JSON.parse(document.getElementById('itemPayloadData')?.textContent || '{}');
    const modeLabels = JSON.parse(document.getElementById('itemModeLabelsData')?.textContent || '{}');
    const components = itemPayload.components || {};
    const materials = itemPayload.materials || {};

    const parseNumber = (value) => {
        const parsed = Number.parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatNumber = (value) => value.toFixed(2);
    const buildOrderCode = (dateValue) => {
        const source = /^\d{4}-\d{2}-\d{2}$/.test(String(dateValue || ''))
            ? String(dateValue)
            : new Date().toISOString().slice(0, 10);
        const [year, month, day] = source.split('-');
        return `SO${month}${year.slice(2, 4)}${day}-00`;
    };

    const buildOptions = (items, placeholder, selected) => {
        let html = `<option value="">${placeholder}</option>`;
        Object.values(items).forEach((item) => {
            html += `<option value="${item.id}"${String(item.id) === String(selected || '') ? ' selected' : ''}>${item.option_label || item.name}</option>`;
        });

        return html;
    };

    const syncRow = (row, force = false) => {
        const mode = row.querySelector('.item-mode')?.value || 'estimate';
        const componentSelect = row.querySelector('.item-component');
        const materialSelect = row.querySelector('.item-material');
        const tempCodeInput = row.querySelector('.item-temp-code');
        const descriptionInput = row.querySelector('.item-description');
        const unitInput = row.querySelector('.item-unit');
        const unitPriceInput = row.querySelector('.item-unit-price');
        const badge = row.querySelector('.item-mode-badge');

        if (badge) {
            badge.textContent = modeLabels[mode] || mode;
            badge.className = `badge rounded-pill so-mode-badge item-mode-badge ${mode === 'estimate' ? 'text-bg-warning' : (mode === 'service' ? 'text-bg-info' : 'text-bg-light')}`;
        }

        const componentSelected = componentSelect.value || componentSelect.dataset.selectedValue || '';
        const materialSelected = materialSelect.value || materialSelect.dataset.selectedValue || '';
        componentSelect.innerHTML = buildOptions(components, 'Chọn bán thành phẩm', componentSelected);
        materialSelect.innerHTML = buildOptions(materials, 'Chọn vật tư', materialSelected);
        componentSelect.dataset.selectedValue = '';
        materialSelect.dataset.selectedValue = '';

        componentSelect.disabled = mode !== 'component';
        materialSelect.disabled = mode !== 'material';
        componentSelect.classList.toggle('d-none', mode !== 'component');
        materialSelect.classList.toggle('d-none', mode !== 'material');
        tempCodeInput.disabled = mode !== 'estimate';
        if (mode !== 'estimate') {
            tempCodeInput.value = '';
        }

        let item = null;
        if (mode === 'component' && componentSelect.value) item = components[componentSelect.value] || null;
        if (mode === 'material' && materialSelect.value) item = materials[materialSelect.value] || null;

        if (item) {
            if (force || !descriptionInput.value.trim()) descriptionInput.value = item.name || '';
            if (force || !unitInput.value.trim()) unitInput.value = item.unit || '';
            if (force || parseNumber(unitPriceInput.value) <= 0) unitPriceInput.value = item.standard_cost || '0.00';
        }

        calculateTotals();
    };

    const calculateTotals = () => {
        let subtotal = 0;
        Array.from(body.querySelectorAll('[data-item-row]')).forEach((row, index) => {
            const quantity = Math.max(parseNumber(row.querySelector('.item-quantity')?.value), 0);
            const unitPrice = Math.max(parseNumber(row.querySelector('.item-unit-price')?.value), 0);
            const lineDiscount = Math.max(parseNumber(row.querySelector('.item-discount')?.value), 0);
            const gross = quantity * unitPrice;
            row.querySelector('.item-row-number').textContent = String(index + 1);
            row.querySelector('.item-total').value = formatNumber(Math.max(gross - lineDiscount, 0));
            subtotal += Math.max(gross - lineDiscount, 0);
        });

        subtotalPreview.value = formatNumber(subtotal);
        totalPreview.value = formatNumber(subtotal - Math.max(parseNumber(discountInput?.value), 0) + Math.max(parseNumber(taxInput?.value), 0));
    };

    const hydrateRow = (row, data) => {
        row.querySelector('.item-quotation-item-id').value = data.quotation_item_id || '';
        row.querySelector('.item-mode').value = data.item_mode || 'estimate';
        row.querySelector('.item-component').dataset.selectedValue = data.component_id || '';
        row.querySelector('.item-material').dataset.selectedValue = data.material_id || '';
        row.querySelector('.item-temp-code').value = data.temp_code || '';
        row.querySelector('.item-description').value = data.description || '';
        row.querySelector('.item-spec-summary').value = data.spec_summary || '';
        row.querySelector('.item-unit').value = data.unit || 'pcs';
        row.querySelector('.item-quantity').value = data.quantity || '1.00';
        row.querySelector('.item-unit-price').value = data.unit_price || '0.00';
        row.querySelector('.item-discount').value = data.discount_amount || '0.00';
        syncRow(row, false);
    };

    const addRow = (data = null) => {
        const index = Number.parseInt(body.dataset.nextIndex || '0', 10);
        body.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index)));
        body.dataset.nextIndex = String(index + 1);
        const row = body.lastElementChild;
        if (row && data) hydrateRow(row, data);
        if (row && !data) syncRow(row, false);
    };

    const replaceRows = (items) => {
        body.innerHTML = '';
        const safeItems = Array.isArray(items) && items.length > 0 ? items : [{ item_mode: 'estimate', quantity: '1.00', unit_price: '0.00', discount_amount: '0.00' }];
        safeItems.forEach((item) => addRow(item));
        calculateTotals();
    };

    const loadQuotation = (quotationId) => {
        if (!quotationId || !quotationPayload[quotationId]) return;
        const quotation = quotationPayload[quotationId];
        customerSelect.value = String(quotation.customer_id || '');
        discountInput.value = quotation.discount_amount || '0.00';
        taxInput.value = quotation.tax_amount || '0.00';
        replaceRows(quotation.items || []);
    };

    Array.from(body.querySelectorAll('[data-item-row]')).forEach((row) => syncRow(row, false));

    addButton?.addEventListener('click', () => addRow());
    quotationSelect?.addEventListener('change', () => loadQuotation(quotationSelect.value));

    body?.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const row = target.closest('[data-item-row]');
        if (!row) return;
        if (target.matches('.item-mode, .item-component, .item-material')) syncRow(row, true);
    });

    body?.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.matches('.item-quantity, .item-unit-price, .item-discount')) calculateTotals();
    });

    body?.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const button = target.closest('.remove-item-button');
        if (!button) return;
        button.closest('[data-item-row]')?.remove();
        if (body.querySelectorAll('[data-item-row]').length === 0) addRow();
        calculateTotals();
    });

    discountInput?.addEventListener('input', calculateTotals);
    taxInput?.addEventListener('input', calculateTotals);
    orderDateInput?.addEventListener('change', () => {
        if (orderCodeInput && <?php echo $isEdit ? 'false' : 'true'; ?>) {
            orderCodeInput.value = buildOrderCode(orderDateInput.value);
        }
    });
    const rows = Array.from(body.querySelectorAll('[data-item-row]'));
    const shouldHydrateFromQuotation = quotationSelect?.value
        && rows.length === 1
        && !rows[0].querySelector('.item-quotation-item-id')?.value
        && !rows[0].querySelector('.item-description')?.value;
    if (shouldHydrateFromQuotation) {
        loadQuotation(quotationSelect.value);
        return;
    }
    calculateTotals();
})();
</script>
</body>
</html>
?>
