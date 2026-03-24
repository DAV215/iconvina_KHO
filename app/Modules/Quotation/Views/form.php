<?php
$activeSidebar = $activeSidebar ?? 'quotations';
$pageTitle = $pageTitle ?? 'Biểu mẫu báo giá';
$pageEyebrow = $pageEyebrow ?? 'Quản lý báo giá';
$formAction = $formAction ?? '/quotations/store';
$quotation = $quotation ?? [];
$customers = $customers ?? [];
$statuses = $statuses ?? [];
$statusLabels = $statusLabels ?? [];
$suggestedCode = $suggestedCode ?? '';
$itemModes = $itemModes ?? [];
$itemPayload = $itemPayload ?? ['materials' => [], 'components' => []];
$errors = $errors ?? [];
$items = $quotation['items'] ?? [];
$componentOptions = $itemPayload['components'] ?? [];
$materialOptions = $itemPayload['materials'] ?? [];

if ($items === []) {
    $items = [[
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

$field = static function (string $key, string $default = '') use ($quotation): string {
    return htmlspecialchars((string) ($quotation[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};

$itemErrorFor = static function (int|string $index, string $key) use ($errors): ?string {
    return $errors["items.{$index}.{$key}"][0] ?? null;
};

$selectedCustomerId = (string) ($quotation['customer_id'] ?? '');
$selectedStatus = (string) ($quotation['status'] ?? 'draft');
$taxAmountValue = (string) ($quotation['tax_percent'] ?? '0.00');
$isEdit = (int) ($quotation['id'] ?? 0) > 0;
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
        .quote-items-shell { overflow-x: auto; }
        .quote-items-table { min-width: 1320px; }
        .quote-items-table th, .quote-items-table td { font-size: 12.5px; vertical-align: top; white-space: nowrap; }
        .quote-items-table thead th { background: #f8fafc; color: #475569; border-bottom-color: rgba(15, 23, 42, .1); }
        .quote-items-table tbody td { padding: .55rem .6rem; border-color: rgba(15, 23, 42, .08); }
        .quote-items-table tbody tr:hover { background: rgba(15, 23, 42, .02); }
        .quote-items-table .form-control, .quote-items-table .form-select { font-size: 12.5px; min-height: 34px; }
        .quote-items-table tbody tr[data-item-row] { height: 78px; }
        .quote-items-table tbody tr[data-item-row] > td { height: 78px; vertical-align: top; }
        .quote-items-table .item-description,
        .quote-items-table .item-spec-summary { min-width: 240px; }
        .quote-items-table .item-master-stack,
        .quote-items-table .item-detail-stack { min-width: 220px; display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start; gap: .45rem; min-height: 66px; }
        .quote-items-table .item-master-stack .form-select,
        .quote-items-table .item-detail-stack .form-control { width: 100%; }
        .quote-items-table .item-master-stack .item-component.d-none,
        .quote-items-table .item-master-stack .item-material.d-none { display: none !important; }
        .quote-items-table .item-master-stack .item-material { margin-top: 0 !important; }
        .quote-items-table .item-detail-stack { position: relative; }
        .quote-items-table .item-row-number,
        .quote-items-table .remove-item-button { margin-top: .15rem; }
        .quote-items-table .item-temp-code,
        .quote-items-table .item-unit,
        .quote-items-table .item-quantity,
        .quote-items-table .item-unit-price,
        .quote-items-table .item-discount,
        .quote-items-table .item-total { margin-top: 0; }
        .quote-items-table .item-spec-summary {
            opacity: 0;
            max-height: 0;
            min-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            border-width: 0;
            margin-top: 0 !important;
            overflow: hidden;
            pointer-events: none;
            transition: opacity .15s ease, max-height .15s ease, margin .15s ease, padding .15s ease, border-width .15s ease;
        }
        .quote-items-table .item-detail-stack:hover .item-spec-summary,
        .quote-items-table .item-detail-stack:focus-within .item-spec-summary {
            opacity: 1;
            max-height: 52px;
            min-height: 34px;
            padding-top: .25rem;
            padding-bottom: .25rem;
            border-width: 1px;
            margin-top: .45rem !important;
            overflow: visible;
            pointer-events: auto;
        }
        .quote-items-table .item-total { min-width: 120px; }
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Báo giá / Estimate-first</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn">Quay lại</a>
                    </div>

                    <?php if ($errorFor('items')): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo htmlspecialchars($errorFor('items'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="quote-form" class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Mã báo giá</label>
                                        <input type="text" name="code" id="quote-code-input" class="form-control form-control-lg rounded-4 bg-light <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($quotation['code'] ?? $suggestedCode), ENT_QUOTES, 'UTF-8'); ?>" maxlength="30" readonly>
                                        <div class="form-text">Tự sinh theo mẫu QOmmYY-dd-xx.</div>
                                        <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Khách hàng</label>
                                        <select name="customer_id" class="form-select form-select-lg rounded-4 <?php echo $errorFor('customer_id') ? 'is-invalid' : ''; ?>">
                                            <option value="">Chọn khách hàng</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo (int) $customer['id']; ?>" <?php echo $selectedCustomerId === (string) $customer['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $customer['code'] . ' - ' . (string) $customer['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('customer_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('customer_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <label class="form-label fw-semibold">Ngày báo giá</label>
                                        <input type="date" name="quote_date" class="form-control rounded-4 <?php echo $errorFor('quote_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('quote_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('quote_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('quote_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <label class="form-label fw-semibold">Hiệu lực đến</label>
                                        <input type="date" name="expired_at" class="form-control rounded-4 <?php echo $errorFor('expired_at') ? 'is-invalid' : ''; ?>" value="<?php echo $field('expired_at'); ?>">
                                        <?php if ($errorFor('expired_at')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('expired_at'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold">Trạng thái workflow</label>
                                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($selectedStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="form-control rounded-4 bg-light"><?php echo htmlspecialchars((string) ($statusLabels[$selectedStatus] ?? $selectedStatus), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="form-text"><?php echo $isEdit ? 'Trình duyệt / duyệt / hủy xử lý ở trang chi tiết.' : 'Báo giá mới mặc định là nháp.'; ?></div>
                                    </div>
                                    <div class="col-12 col-lg-9">
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
                                    <input type="text" class="form-control rounded-4 bg-light" id="subtotalPreview" value="0" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Chiết khấu dòng</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="discountPreview" value="0" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Thuế (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="tax_amount" id="taxAmountInput" class="form-control rounded-4 <?php echo $errorFor('tax_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($taxAmountValue, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text" id="taxAmountPreview">Tiền thuế: 0</div>
                                    <?php if ($errorFor('tax_amount')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('tax_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Tổng cộng</label>
                                    <input type="text" class="form-control rounded-4 bg-light fw-semibold" id="totalPreview" value="0" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                                    <div>
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">Dòng báo giá</div>
                                        <h4 class="h5 mb-0 fw-semibold">Estimate item hoặc item master</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="quote-add-item-button"><i class="bi bi-plus-lg me-2"></i>Thêm dòng</button>
                                </div>

                                <div class="quote-items-shell">
                                    <table class="table table-sm quote-items-table align-middle mb-0">
                                        <colgroup>
                                            <col style="width:60px;">
                                            <col style="width:170px;">
                                            <col style="width:220px;">
                                            <col style="width:130px;">
                                            <col style="width:320px;">
                                            <col style="width:100px;">
                                            <col style="width:110px;">
                                            <col style="width:125px;">
                                            <col style="width:125px;">
                                            <col style="width:125px;">
                                            <col style="width:80px;">
                                        </colgroup>
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
                                                <th style="min-width:125px;">CK</th>
                                                <th style="min-width:125px;">Thành tiền</th>
                                                <th style="width:80px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="quote-items-body">
                                        <?php foreach ($items as $index => $item): ?>
                                            <tr data-item-row>
                                                <td class="text-secondary fw-semibold item-row-number"><?php echo (int) $index + 1; ?></td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][item_mode]" data-name-template="items[__INDEX__][item_mode]" class="form-select form-select-sm rounded-4 item-mode <?php echo $itemErrorFor($index, 'item_mode') ? 'is-invalid' : ''; ?>">
                                                        <?php foreach ($itemModes as $value => $label): ?>
                                                            <option value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($item['item_mode'] ?? 'estimate') === (string) $value ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="item-master-stack">
                                                        <select name="items[<?php echo (int) $index; ?>][component_id]" data-name-template="items[__INDEX__][component_id]" class="form-select form-select-sm rounded-4 item-component">
                                                            <option value="">Chọn bán thành phẩm</option>
                                                            <?php foreach ($componentOptions as $componentOption): ?>
                                                                <option value="<?php echo htmlspecialchars((string) ($componentOption['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($item['component_id'] ?? '') === (string) ($componentOption['id'] ?? '') ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars((string) ($componentOption['option_label'] ?? $componentOption['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <select name="items[<?php echo (int) $index; ?>][material_id]" data-name-template="items[__INDEX__][material_id]" class="form-select form-select-sm rounded-4 item-material">
                                                            <option value="">Chọn vật tư</option>
                                                            <?php foreach ($materialOptions as $materialOption): ?>
                                                                <option value="<?php echo htmlspecialchars((string) ($materialOption['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($item['material_id'] ?? '') === (string) ($materialOption['id'] ?? '') ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars((string) ($materialOption['option_label'] ?? $materialOption['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <?php if ($itemErrorFor($index, 'component_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'component_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                    <?php if ($itemErrorFor($index, 'material_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'material_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td><input type="text" name="items[<?php echo (int) $index; ?>][temp_code]" data-name-template="items[__INDEX__][temp_code]" class="form-control form-control-sm rounded-4 item-temp-code" value="<?php echo htmlspecialchars((string) ($item['temp_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" placeholder="CF-TMP"></td>
                                                <td>
                                                    <div class="item-detail-stack">
                                                        <input type="text" name="items[<?php echo (int) $index; ?>][description]" data-name-template="items[__INDEX__][description]" class="form-control form-control-sm rounded-4 item-description <?php echo $itemErrorFor($index, 'description') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" placeholder="Mô tả hàng / yêu cầu gia công">
                                                        <input type="text" name="items[<?php echo (int) $index; ?>][spec_summary]" data-name-template="items[__INDEX__][spec_summary]" class="form-control form-control-sm rounded-4 item-spec-summary" value="<?php echo htmlspecialchars((string) ($item['spec_summary'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" placeholder="Quy cách, màu, vật liệu...">
                                                    </div>
                                                    <?php if ($itemErrorFor($index, 'description')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][unit]" data-name-template="items[__INDEX__][unit]" class="form-control form-control-sm rounded-4 item-unit <?php echo $itemErrorFor($index, 'unit') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
                                                    <?php if ($itemErrorFor($index, 'unit')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'unit'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][quantity]" data-name-template="items[__INDEX__][quantity]" class="form-control form-control-sm rounded-4 item-quantity <?php echo $itemErrorFor($index, 'quantity') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity'] ?? '1.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'quantity')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][unit_price]" data-name-template="items[__INDEX__][unit_price]" class="form-control form-control-sm rounded-4 item-unit-price <?php echo $itemErrorFor($index, 'unit_price') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit_price'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'unit_price')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'unit_price'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][discount_amount]" data-name-template="items[__INDEX__][discount_amount]" class="form-control form-control-sm rounded-4 item-discount <?php echo $itemErrorFor($index, 'discount_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['discount_amount'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'discount_amount')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($itemErrorFor($index, 'discount_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td><input type="text" class="form-control rounded-4 bg-light item-total" value="<?php echo htmlspecialchars(number_format((float) ($item['total_amount'] ?? 0), 0, ',', '.'), ENT_QUOTES, 'UTF-8'); ?>" readonly></td>
                                                <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                        <tbody class="d-none">
                                            <tr id="quote-item-row-template" data-item-row hidden>
                                                <td class="text-secondary fw-semibold item-row-number">1</td>
                                                <td>
                                                    <select name="items[__INDEX__][item_mode]" data-name-template="items[__INDEX__][item_mode]" class="form-select form-select-sm rounded-4 item-mode">
                                                        <?php foreach ($itemModes as $value => $label): ?>
                                                            <option value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="item-master-stack">
                                                        <select name="items[__INDEX__][component_id]" data-name-template="items[__INDEX__][component_id]" class="form-select form-select-sm rounded-4 item-component">
                                                            <option value="">Chọn bán thành phẩm</option>
                                                            <?php foreach ($componentOptions as $componentOption): ?>
                                                                <option value="<?php echo htmlspecialchars((string) ($componentOption['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?php echo htmlspecialchars((string) ($componentOption['option_label'] ?? $componentOption['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <select name="items[__INDEX__][material_id]" data-name-template="items[__INDEX__][material_id]" class="form-select form-select-sm rounded-4 item-material">
                                                            <option value="">Chọn vật tư</option>
                                                            <?php foreach ($materialOptions as $materialOption): ?>
                                                                <option value="<?php echo htmlspecialchars((string) ($materialOption['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?php echo htmlspecialchars((string) ($materialOption['option_label'] ?? $materialOption['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td><input type="text" name="items[__INDEX__][temp_code]" data-name-template="items[__INDEX__][temp_code]" class="form-control form-control-sm rounded-4 item-temp-code" maxlength="50" placeholder="CF-TMP"></td>
                                                <td>
                                                    <div class="item-detail-stack">
                                                        <input type="text" name="items[__INDEX__][description]" data-name-template="items[__INDEX__][description]" class="form-control form-control-sm rounded-4 item-description" maxlength="255" placeholder="Mô tả hàng / yêu cầu gia công">
                                                        <input type="text" name="items[__INDEX__][spec_summary]" data-name-template="items[__INDEX__][spec_summary]" class="form-control form-control-sm rounded-4 item-spec-summary" maxlength="255" placeholder="Quy cách, màu, vật liệu...">
                                                    </div>
                                                </td>
                                                <td><input type="text" name="items[__INDEX__][unit]" data-name-template="items[__INDEX__][unit]" class="form-control form-control-sm rounded-4 item-unit" value="pcs" maxlength="50"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[__INDEX__][quantity]" data-name-template="items[__INDEX__][quantity]" class="form-control form-control-sm rounded-4 item-quantity" value="1.00"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" data-name-template="items[__INDEX__][unit_price]" class="form-control form-control-sm rounded-4 item-unit-price" value="0.00"></td>
                                                <td><input type="number" step="0.01" min="0" name="items[__INDEX__][discount_amount]" data-name-template="items[__INDEX__][discount_amount]" class="form-control form-control-sm rounded-4 item-discount" value="0.00"></td>
                                                <td><input type="text" class="form-control rounded-4 bg-light item-total" value="0" readonly></td>
                                                <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn">Hủy</a>
                            <button type="submit" class="btn btn-dark erp-btn">Lưu báo giá</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<script id="itemPayloadData" type="application/json"><?php echo json_encode($itemPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const initQuoteForm = () => {
    const quoteItemsBody = document.getElementById('quote-items-body');
    const quoteItemRowTemplate = document.getElementById('quote-item-row-template');
    const addQuoteItemButton = document.getElementById('quote-add-item-button');
    const quoteCodeInput = document.getElementById('quote-code-input');
    const quoteDateInput = document.querySelector('input[name="quote_date"]');
    const taxInput = document.getElementById('taxAmountInput');
    const taxAmountPreview = document.getElementById('taxAmountPreview');
    const subtotalPreview = document.getElementById('subtotalPreview');
    const discountPreview = document.getElementById('discountPreview');
    const totalPreview = document.getElementById('totalPreview');
    const payload = JSON.parse(document.getElementById('itemPayloadData')?.textContent || '{}');
    const components = payload.components || {};
    const materials = payload.materials || {};

    if (!(quoteItemsBody instanceof HTMLTableSectionElement) || !(quoteItemRowTemplate instanceof HTMLTableRowElement)) {
        return;
    }

    const parseNumber = (value) => {
        const parsed = Number.parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatMoney = (value) => new Intl.NumberFormat('vi-VN', {
        maximumFractionDigits: 0,
    }).format(Math.round(value || 0));
    const buildQuoteCode = (dateValue) => {
        const source = /^\d{4}-\d{2}-\d{2}$/.test(String(dateValue || ''))
            ? String(dateValue)
            : new Date().toISOString().slice(0, 10);
        const parts = source.split('-');
        return `QO${parts[1]}${parts[0].slice(2, 4)}-${parts[2]}-00`;
    };
    const getQuoteItemsBody = () => quoteItemsBody;
    const getQuoteRows = () => Array.from(getQuoteItemsBody().querySelectorAll('tr[data-item-row]')).filter((row) => !row.hidden);
    const applyQuoteRowNames = (row, index) => {
        row.querySelectorAll('[data-name-template]').forEach((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
                return;
            }
            field.name = String(field.getAttribute('data-name-template') || '').replaceAll('__INDEX__', String(index));
        });
    };
    const reindexQuoteRows = () => {
        getQuoteRows().forEach((row, index) => {
            applyQuoteRowNames(row, index);
            const rowNumber = row.querySelector('.item-row-number');
            if (rowNumber) {
                rowNumber.textContent = String(index + 1);
            }
        });
    };
    const disableQuoteTemplateFields = () => {
        quoteItemRowTemplate.querySelectorAll('input, select, textarea, button').forEach((field) => {
            if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement || field instanceof HTMLButtonElement) {
                field.disabled = true;
            }
        });
    };
    const resetQuoteRow = (row) => {
        row.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
        row.querySelectorAll('.invalid-feedback.d-block').forEach((field) => field.remove());
        const defaults = {
            '.item-mode': 'estimate',
            '.item-component': '',
            '.item-material': '',
            '.item-temp-code': '',
            '.item-description': '',
            '.item-spec-summary': '',
            '.item-unit': 'pcs',
            '.item-quantity': '1.00',
            '.item-unit-price': '0.00',
            '.item-discount': '0.00',
        };
        Object.entries(defaults).forEach(([selector, value]) => {
            const field = row.querySelector(selector);
            if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                field.value = value;
            }
        });
        const totalField = row.querySelector('.item-total');
        if (totalField instanceof HTMLInputElement) {
            totalField.value = '0';
        }
    };
    const fillRowFromMasterItem = (row, masterItem, mode, force = false) => {
        if (!masterItem) {
            return;
        }

        const descriptionInput = row.querySelector('.item-description');
        const unitInput = row.querySelector('.item-unit');
        const unitPriceInput = row.querySelector('.item-unit-price');
        const tempCodeInput = row.querySelector('.item-temp-code');

        if (descriptionInput instanceof HTMLInputElement && (force || !descriptionInput.value.trim())) {
            descriptionInput.value = masterItem.name || '';
        }

        if (unitInput instanceof HTMLInputElement && (force || !unitInput.value.trim())) {
            unitInput.value = masterItem.unit || '';
        }

        if (unitPriceInput instanceof HTMLInputElement) {
            unitPriceInput.value = String(masterItem.standard_cost || '0.00');
        }

        if (tempCodeInput instanceof HTMLInputElement && mode !== 'estimate') {
            tempCodeInput.value = '';
        }
    };

    const syncRow = (row, force = false) => {
        const mode = row.querySelector('.item-mode')?.value || 'estimate';
        const componentSelect = row.querySelector('.item-component');
        const materialSelect = row.querySelector('.item-material');
        const tempCodeInput = row.querySelector('.item-temp-code');
        if (!(componentSelect instanceof HTMLSelectElement) || !(materialSelect instanceof HTMLSelectElement) || !(tempCodeInput instanceof HTMLInputElement)) {
            return;
        }
        componentSelect.disabled = mode !== 'component';
        materialSelect.disabled = mode !== 'material';
        componentSelect.classList.toggle('d-none', mode !== 'component');
        materialSelect.classList.toggle('d-none', mode !== 'material');
        tempCodeInput.disabled = mode !== 'estimate';
        if (mode !== 'estimate') {
            tempCodeInput.value = '';
        }

        let item = null;
        if (mode === 'component' && componentSelect.value) {
            item = components[componentSelect.value] || null;
        }
        if (mode === 'material' && materialSelect.value) {
            item = materials[materialSelect.value] || null;
        }

        fillRowFromMasterItem(row, item, mode, force);

        calculateTotals();
    };

    const calculateTotals = () => {
        let subtotal = 0;
        let discount = 0;
        getQuoteRows().forEach((row, index) => {
            const quantity = Math.max(parseNumber(row.querySelector('.item-quantity')?.value), 0);
            const unitPrice = Math.max(parseNumber(row.querySelector('.item-unit-price')?.value), 0);
            const discountAmount = Math.max(parseNumber(row.querySelector('.item-discount')?.value), 0);
            const lineGross = quantity * unitPrice;
            subtotal += lineGross;
            discount += discountAmount;
            row.querySelector('.item-row-number').textContent = String(index + 1);
            row.querySelector('.item-total').value = formatMoney(Math.max(lineGross - discountAmount, 0));
        });

        const taxableAmount = Math.max(subtotal - discount, 0);
        const taxRate = Math.min(Math.max(parseNumber(taxInput?.value), 0), 100);
        const taxAmount = taxableAmount * taxRate / 100;
        subtotalPreview.value = formatMoney(subtotal);
        discountPreview.value = formatMoney(discount);
        totalPreview.value = formatMoney(taxableAmount + taxAmount);
        if (taxAmountPreview) {
            taxAmountPreview.textContent = `Tiền thuế: ${formatMoney(taxAmount)}`;
        }
    };

    const addQuoteItemRow = () => {
        const row = quoteItemRowTemplate.cloneNode(true);
        row.removeAttribute('id');
        row.removeAttribute('hidden');
        row.querySelectorAll('input, select, textarea, button').forEach((field) => {
            if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement || field instanceof HTMLButtonElement) {
                field.disabled = false;
            }
        });
        applyQuoteRowNames(row, getQuoteRows().length);
        resetQuoteRow(row);
        quoteItemsBody.appendChild(row);
        reindexQuoteRows();
        syncRow(row, false);
        return row;
    };

    const removeQuoteItemRow = (row) => {
        if (!(row instanceof HTMLTableRowElement) || !quoteItemsBody.contains(row)) {
            return;
        }
        row.remove();
        if (getQuoteRows().length === 0) {
            addQuoteItemRow();
        } else {
            reindexQuoteRows();
            calculateTotals();
        }
    };

    disableQuoteTemplateFields();
    reindexQuoteRows();
    Array.from(quoteItemsBody.querySelectorAll('[data-item-row]')).forEach((row) => {
        syncRow(row, false);
    });

    addQuoteItemButton?.addEventListener('click', addQuoteItemRow);
    window.addQuoteItemRow = addQuoteItemRow;
    window.removeQuoteItemRow = removeQuoteItemRow;
    window.reindexQuoteRows = reindexQuoteRows;
    quoteDateInput?.addEventListener('change', () => {
        if (quoteCodeInput && <?php echo $isEdit ? 'false' : 'true'; ?>) {
            quoteCodeInput.value = buildQuoteCode(quoteDateInput.value);
        }
    });

    quoteItemsBody?.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const row = target.closest('[data-item-row]');
        if (!row) return;
        if (target.matches('.item-mode, .item-component, .item-material')) {
            syncRow(row, true);
        }
    });

    quoteItemsBody?.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.matches('.item-quantity, .item-unit-price, .item-discount')) {
            calculateTotals();
        }
    });

    quoteItemsBody?.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const button = target.closest('.remove-item-button');
        if (!button) return;
        removeQuoteItemRow(button.closest('[data-item-row]'));
    });

    taxInput?.addEventListener('input', calculateTotals);
    calculateTotals();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuoteForm, { once: true });
} else {
    initQuoteForm();
}
</script>
</body>
</html>
