<?php
$activeSidebar = $activeSidebar ?? 'purchase-orders';
$pageTitle = $pageTitle ?? 'Đơn mua hàng';
$formAction = $formAction ?? app_url('/purchase-orders/store');
$purchaseOrder = $purchaseOrder ?? [];
$statuses = $statuses ?? [];
$statusLabels = $statusLabels ?? [];
$materials = $materials ?? [];
$materialPayload = $materialPayload ?? [];
$supplierPayload = $supplierPayload ?? [];
$materialCategoryOptions = $materialCategoryOptions ?? [];
$suggestedCode = $suggestedCode ?? '';
$errors = $errors ?? [];
$items = $purchaseOrder['items'] ?? [];
$currentStatus = (string) ($purchaseOrder['status'] ?? 'draft');
$canQuickCreateSupplier = has_permission('supplier.create');
$canQuickCreateMaterial = has_permission('material.create');

if ($items === []) {
    $items = [[
        'material_id' => '',
        'description' => '',
        'unit' => '',
        'quantity' => '1.00',
        'unit_price' => '0.00',
        'discount_amount' => '0.00',
        'total_amount' => '0.00',
    ]];
}

$field = static function (string $key, string $default = '') use ($purchaseOrder): string {
    return htmlspecialchars((string) ($purchaseOrder[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};

$itemErrorFor = static function (int|string $index, string $key) use ($errors): ?string {
    return $errors["items.{$index}.{$key}"][0] ?? null;
};

$bodyError = $errorFor('items');
$selectedSupplierLabel = (string) ($purchaseOrder['supplier_name'] ?? '');
$netAmountBase = max(((float) ($purchaseOrder['total_amount'] ?? 0)) - ((float) ($purchaseOrder['tax_amount'] ?? 0)), 0);
$derivedTaxPercent = $netAmountBase > 0
    ? number_format((((float) ($purchaseOrder['tax_amount'] ?? 0)) / $netAmountBase) * 100, 2, '.', '')
    : '0.00';

$renderItemRow = static function (int|string $index, int $rowNumber, array $item, bool $isTemplate = false) use ($materialPayload, $itemErrorFor): string {
    $materialId = (string) ($item['material_id'] ?? '');
    $materialMeta = $materialId !== '' && isset($materialPayload[$materialId]) ? $materialPayload[$materialId] : null;
    $description = (string) ($item['description'] ?? ($materialMeta['name'] ?? ''));
    $unit = (string) ($item['unit'] ?? ($materialMeta['unit'] ?? ''));
    $quantity = (string) ($item['quantity'] ?? '1.00');
    $unitPrice = (string) ($item['unit_price'] ?? ($materialMeta['standard_cost'] ?? '0.00'));
    $discountAmount = (string) ($item['discount_amount'] ?? '0.00');
    $lineTotal = number_format((float) ($item['total_amount'] ?? ((float) $quantity * (float) $unitPrice) - (float) $discountAmount), 2, '.', '');
    $optionLabel = (string) ($materialMeta['option_label'] ?? '');
    $rowError = static function (string $key) use ($isTemplate, $itemErrorFor, $index): ?string {
        return $isTemplate ? null : $itemErrorFor($index, $key);
    };

    ob_start();
    ?>
    <tr data-item-row class="po-item-row">
        <td class="text-secondary fw-semibold item-row-number align-middle"></td>
        <td class="po-col-material">
            <input type="hidden" data-name-key="material_id" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][material_id]" class="item-material" value="<?php echo htmlspecialchars($materialId, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="text" class="form-control form-control-sm rounded-3 item-material-search <?php echo $rowError('material_id') ? 'is-invalid' : ''; ?>" list="po-materials-list" value="<?php echo htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm mã, tên, danh mục, màu, quy cách">
            <?php if ($rowError('material_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('material_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="po-col-description">
            <input type="text" data-name-key="description" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][description]" class="form-control form-control-sm rounded-3 item-description <?php echo $rowError('description') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" placeholder="Mô tả vật tư">
            <?php if ($rowError('description')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="po-col-unit">
            <input type="text" data-name-key="unit" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][unit]" class="form-control form-control-sm rounded-3 item-unit text-center bg-light <?php echo $rowError('unit') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($unit, ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" placeholder="Đơn vị" readonly>
            <?php if ($rowError('unit')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('unit'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="po-col-qty">
            <input type="number" step="0.01" min="0" data-name-key="quantity" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][quantity]" class="form-control form-control-sm rounded-3 item-quantity text-end <?php echo $rowError('quantity') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($quantity, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($rowError('quantity')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="po-col-price">
            <input type="number" step="0.01" min="0" data-name-key="unit_price" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][unit_price]" class="form-control form-control-sm rounded-3 item-unit-price text-end <?php echo $rowError('unit_price') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($unitPrice, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($rowError('unit_price')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('unit_price'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="po-col-discount">
            <input type="number" step="0.01" min="0" data-name-key="discount_amount" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][discount_amount]" class="form-control form-control-sm rounded-3 item-discount text-end <?php echo $rowError('discount_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($discountAmount, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($rowError('discount_amount')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('discount_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="po-col-total">
            <input type="text" data-name-key="total_amount" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][total_amount]" class="form-control form-control-sm rounded-3 bg-light item-total text-end fw-semibold" value="<?php echo htmlspecialchars($lineTotal, ENT_QUOTES, 'UTF-8'); ?>" readonly>
        </td>
        <td class="text-end po-col-action">
            <button type="button" class="btn btn-light btn-sm rounded-3 remove-item-button"><i class="bi bi-trash"></i></button>
        </td>
    </tr>
    <?php

    return (string) ob_get_clean();
};
$templateRowHtml = str_replace('<tr data-item-row class="po-item-row">', '<tr id="poItemRowTemplate" data-item-row class="po-item-row" hidden>', $renderItemRow('__INDEX__', 1, [], true));
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
        #po-items-table thead th {
            background: rgba(15, 23, 42, .04);
            border-bottom: 1px solid rgba(15, 23, 42, .08);
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #475569;
            padding: .85rem .75rem;
            white-space: nowrap;
        }
        #po-items-table tbody td {
            padding: .7rem .75rem;
            border-color: rgba(15, 23, 42, .08);
            vertical-align: top;
        }
        #po-items-table tbody tr:hover {
            background: rgba(15, 23, 42, .02);
        }
        #po-items-table tbody tr.po-item-row.has-row-error {
            background: rgba(220, 38, 38, .04);
        }
        #po-items-table .form-control-sm {
            min-height: 36px;
        }
        #po-items-table .po-col-material { min-width: 280px; }
        #po-items-table .po-col-description { min-width: 240px; }
        #po-items-table .po-col-unit { min-width: 110px; }
        #po-items-table .po-col-qty,
        #po-items-table .po-col-price,
        #po-items-table .po-col-discount,
        #po-items-table .po-col-total { min-width: 130px; }
        #po-items-table .po-col-action { width: 64px; }
        #po-items-table .item-unit {
            font-weight: 600;
            color: #334155;
        }
        #po-items-table tfoot td {
            padding: 1rem .75rem 0;
            border: 0;
        }
        [x-cloak] {
            display: none !important;
        }
        .po-supplier-picker {
            position: relative;
        }
        .po-supplier-dropdown {
            position: absolute;
            top: calc(100% + .5rem);
            left: 0;
            right: 0;
            z-index: 1080;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, .12);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, .14);
            max-height: 320px;
            overflow-y: auto;
        }
        .po-supplier-dropdown[hidden] {
            display: none !important;
        }
        .po-supplier-option {
            width: 100%;
            border: 0;
            background: transparent;
            display: block;
            text-align: left;
            padding: .85rem 1rem;
        }
        .po-supplier-option + .po-supplier-option {
            border-top: 1px solid rgba(15, 23, 42, .06);
        }
        .po-supplier-option:hover,
        .po-supplier-option.is-active {
            background: rgba(15, 23, 42, .05);
        }
        .po-supplier-option-code {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .po-supplier-option-meta {
            font-size: .82rem;
            color: #64748b;
        }
        .po-supplier-empty {
            padding: .85rem 1rem;
            color: #64748b;
            font-size: .9rem;
        }
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
                <div class="erp-card p-3 p-lg-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Đơn mua hàng</div>
                            <h3 class="h4 fw-bold mb-1"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="text-secondary">Tạo đơn mua vật tư theo luồng nhập mua hiện tại.</div>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/purchase-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-dark erp-btn rounded-4 px-4 po-back-btn">Quay lại</a>
                    </div>

                    <?php if ($bodyError): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo htmlspecialchars($bodyError, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="po-form" class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Mã đơn</label>
                                        <input type="text" name="code" id="po-code-field" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code', (string) $suggestedCode); ?>" maxlength="30" placeholder="PO0326-22-00">
                                        <div class="form-text">Định dạng: POMMYY-NGÀY-STT, ví dụ: <code>PO0326-22-00</code>.</div>
                                        <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-8">
                                        <label class="form-label fw-semibold">Nhà cung cấp</label>
                                        <div class="po-supplier-picker" id="po-supplier-picker" x-data="window.createPoSupplierPicker()" x-init="init()" @click.outside="close()">
                                            <div class="input-group">
                                                <input type="hidden" name="supplier_id" id="supplier-id-field" value="<?php echo $field('supplier_id'); ?>">
                                                <input type="hidden" name="supplier_name" id="supplier-name-field" value="<?php echo $field('supplier_name'); ?>">
                                                <input type="text" id="supplier-search-field" x-ref="search" x-model="query" @focus="openDropdown()" @input.debounce.200ms="handleInput()" @keydown.arrow-down.prevent="highlightNext()" @keydown.arrow-up.prevent="highlightPrev()" @keydown.enter.prevent="selectActive()" @keydown.escape.prevent="close()" @blur="handleBlur()" :aria-expanded="open ? 'true' : 'false'" class="form-control form-control-lg rounded-start-4 <?php echo $errorFor('supplier_name') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($selectedSupplierLabel, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm theo mã, tên, liên hệ, điện thoại, email" autocomplete="off" spellcheck="false" aria-autocomplete="list">
                                                <?php if ($canQuickCreateSupplier): ?><button type="button" class="btn btn-outline-secondary rounded-end-4" id="open-quick-supplier-button">+ Tạo NCC</button><?php endif; ?>
                                            </div>
                                            <div class="po-supplier-dropdown" id="supplier-results-dropdown" x-cloak x-show="open">
                                                <template x-if="loading">
                                                    <div class="po-supplier-empty">Đang tải nhà cung cấp...</div>
                                                </template>
                                                <template x-if="!loading && results.length === 0">
                                                    <div class="po-supplier-empty">Không tìm thấy nhà cung cấp phù hợp.</div>
                                                </template>
                                                <template x-for="(supplier, index) in results" :key="supplier.id || supplier.option_label || index">
                                                    <button type="button" class="po-supplier-option" :class="{ 'is-active': index === activeIndex }" @mousedown.prevent="selectSupplier(supplier)" @click.prevent="selectSupplier(supplier)">
                                                        <div class="po-supplier-option-code" x-text="supplier.code || 'NCC'"></div>
                                                        <div class="fw-semibold" x-text="supplier.name || ''"></div>
                                                        <div class="po-supplier-option-meta" x-show="supplierMeta(supplier)" x-text="supplierMeta(supplier)"></div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <?php if ($errorFor('supplier_name')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars($errorFor('supplier_name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Người liên hệ</label>
                                        <input type="text" name="supplier_contact" id="supplier-contact-field" class="form-control rounded-4 <?php echo $errorFor('supplier_contact') ? 'is-invalid' : ''; ?>" value="<?php echo $field('supplier_contact'); ?>" maxlength="150">
                                        <?php if ($errorFor('supplier_contact')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('supplier_contact'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Điện thoại</label>
                                        <input type="text" name="supplier_phone" id="supplier-phone-field" class="form-control rounded-4 <?php echo $errorFor('supplier_phone') ? 'is-invalid' : ''; ?>" value="<?php echo $field('supplier_phone'); ?>" maxlength="30">
                                        <?php if ($errorFor('supplier_phone')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('supplier_phone'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="text" name="supplier_email" id="supplier-email-field" class="form-control rounded-4 <?php echo $errorFor('supplier_email') ? 'is-invalid' : ''; ?>" value="<?php echo $field('supplier_email'); ?>" maxlength="150">
                                        <?php if ($errorFor('supplier_email')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('supplier_email'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Mã số thuế</label>
                                        <input type="text" id="supplier-tax-code-field" class="form-control rounded-4 bg-light" value="" readonly>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Ngày đặt</label>
                                        <input type="date" name="order_date" id="po-date-field" class="form-control rounded-4 <?php echo $errorFor('order_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('order_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('order_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('order_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Ngày dự kiến nhận</label>
                                        <input type="date" name="expected_date" class="form-control rounded-4 <?php echo $errorFor('expected_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('expected_date'); ?>">
                                        <?php if ($errorFor('expected_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('expected_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Trạng thái</label>
                                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="form-control rounded-4 bg-light"><?php echo htmlspecialchars((string) ($statusLabels[$currentStatus] ?? $currentStatus), ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="form-text">Trạng thái chỉ được thay đổi bằng các nút thao tác ở màn hình chi tiết.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Địa chỉ NCC</label>
                                        <textarea id="supplier-address-field" rows="2" class="form-control rounded-4 bg-light" readonly></textarea>
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
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Tổng hợp giá trị</div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tạm tính</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="subtotal-preview" value="0.00" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Chiết khấu</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="discount-preview" value="0.00" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Thuế (%)</label>
                                    <input type="number" step="0.01" min="0" name="tax_percent" id="tax-percent-input" class="form-control rounded-4 <?php echo $errorFor('tax_percent') ? 'is-invalid' : ''; ?>" value="<?php echo $field('tax_percent', $derivedTaxPercent); ?>">
                                    <?php if ($errorFor('tax_percent')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('tax_percent'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    <div class="form-text">Nhập thuế theo phần trăm, hệ thống tự tính tiền thuế.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tiền thuế</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="tax-amount-preview" value="0.00" readonly>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Tổng tiền</label>
                                    <input type="text" class="form-control rounded-4 bg-light fw-semibold" id="total-preview" value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                                    <div>
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">Vật tư mua</div>
                                        <h4 class="h5 mb-0 fw-semibold">Danh sách dòng mua hàng</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="add-item-button"><i class="bi bi-plus-lg me-2"></i>Thêm dòng</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" id="po-items-table">
                                        <colgroup>
                                            <col style="width:56px;">
                                            <col style="width:320px;">
                                            <col style="width:260px;">
                                            <col style="width:120px;">
                                            <col style="width:120px;">
                                            <col style="width:140px;">
                                            <col style="width:140px;">
                                            <col style="width:140px;">
                                            <col style="width:64px;">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Material</th>
                                                <th>Mô tả</th>
                                                <th>Unit</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Unit price</th>
                                                <th class="text-end">Chiết khấu</th>
                                                <th class="text-end">Thành tiền</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="poItemsBody">
                                            <?php foreach ($items as $index => $item): ?>
                                                <?php echo $renderItemRow($index, (int) $index + 1, $item); ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tbody class="d-none">
                                            <?php echo $templateRowHtml; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="9">
                                                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                                        <?php if ($canQuickCreateMaterial): ?><button type="button" class="btn btn-outline-secondary rounded-4 px-4 align-self-start" id="open-quick-material-button"><i class="bi bi-box-seam me-2"></i>Tạo nhanh vật tư</button><?php endif; ?>
                                                        <div class="small text-secondary align-self-center">Chọn vật tư theo mã hoặc tên, hệ thống sẽ tự điền đơn vị và giá chuẩn.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/purchase-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Lưu đơn mua hàng</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<datalist id="po-materials-list">
    <?php foreach ($materialPayload as $material): ?>
        <option value="<?php echo htmlspecialchars((string) ($material['option_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></option>
    <?php endforeach; ?>
</datalist>

<div class="modal fade" id="quick-supplier-modal" tabindex="-1" aria-labelledby="quick-supplier-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="text-uppercase small fw-semibold text-secondary mb-1">Tạo nhanh nhà cung cấp</div>
                    <h5 class="modal-title fw-bold" id="quick-supplier-modal-label">Tạo nhanh nhà cung cấp</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="erp-form-message mb-3" id="quick-supplier-message"></div>
                <form id="quick-supplier-form" class="row g-3">
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Mã</label><input type="text" name="code" class="form-control rounded-4" maxlength="30"></div>
                    <div class="col-12 col-md-8"><label class="form-label fw-semibold">Tên NCC</label><input type="text" name="name" class="form-control rounded-4" maxlength="190"></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Người liên hệ</label><input type="text" name="contact_name" class="form-control rounded-4" maxlength="150"></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Điện thoại</label><input type="text" name="phone" class="form-control rounded-4" maxlength="30"></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control rounded-4" maxlength="150"></div>
                    <div class="col-12 col-md-6"><label class="form-label fw-semibold">Mã số thuế</label><input type="text" name="tax_code" class="form-control rounded-4" maxlength="50"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Địa chỉ</label><textarea name="address" rows="2" class="form-control rounded-4"></textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Ghi chú</label><textarea name="note" rows="3" class="form-control rounded-4"></textarea></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-4 px-4" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-dark rounded-4 px-4" id="quick-supplier-submit-button">Lưu NCC</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quick-material-modal" tabindex="-1" aria-labelledby="quick-material-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="text-uppercase small fw-semibold text-secondary mb-1">Tạo nhanh vật tư</div>
                    <h5 class="modal-title fw-bold" id="quick-material-modal-label">Tạo nhanh vật tư</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="erp-form-message mb-3" id="quick-material-message"></div>
                <form id="quick-material-form" class="row g-3">
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Mã</label><input type="text" name="code" class="form-control rounded-4" maxlength="30"></div>
                    <div class="col-12 col-md-8"><label class="form-label fw-semibold">Tên vật tư</label><input type="text" name="name" class="form-control rounded-4" maxlength="190"></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Danh mục</label><select name="category_id" class="form-select rounded-4"><option value="">Chọn danh mục</option><?php foreach ($materialCategoryOptions as $category): ?><option value="<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars((string) ($category['label'] ?? $category['name']), ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Đơn vị</label><input type="text" name="unit" class="form-control rounded-4" maxlength="50"></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Giá chuẩn</label><input type="number" step="0.01" min="0" name="standard_cost" class="form-control rounded-4" value="0.00"></div>
                    <div class="col-12 col-md-4"><label class="form-label fw-semibold">Màu</label><input type="text" name="color" class="form-control rounded-4" maxlength="100"></div>
                    <div class="col-12 col-md-8"><label class="form-label fw-semibold">Quy cách</label><input type="text" name="specification" class="form-control rounded-4" maxlength="255"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Mô tả</label><textarea name="description" rows="3" class="form-control rounded-4"></textarea></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-4 px-4" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-dark rounded-4 px-4" id="quick-material-submit-button">Lưu vật tư</button>
            </div>
        </div>
    </div>
</div>

<script id="po-supplier-payload" type="application/json"><?php echo json_encode($supplierPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script id="po-material-payload" type="application/json"><?php echo json_encode($materialPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
<script>
window.createPoSupplierPicker = function () {
    const supplierPayloadElement = document.getElementById('po-supplier-payload');
    const supplierPayload = JSON.parse(supplierPayloadElement ? supplierPayloadElement.textContent : '{}');
    const searchUrl = <?php echo json_encode(app_url('/api/suppliers/search'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const normalizeText = (value) => String(value || '').trim().toLowerCase();
    const supplierCache = {};
    const normalizeSupplier = (supplier) => {
        const normalizedSupplier = supplier && typeof supplier === 'object' ? supplier : {};
        const code = String(normalizedSupplier.code || '').trim();
        const name = String(normalizedSupplier.name || '').trim();
        const contactName = String(normalizedSupplier.contact_name || '').trim();
        const phone = String(normalizedSupplier.phone || '').trim();
        const email = String(normalizedSupplier.email || '').trim();

        return {
            ...normalizedSupplier,
            id: normalizedSupplier.id ?? '',
            code,
            name,
            contact_name: contactName,
            phone,
            email,
            tax_code: String(normalizedSupplier.tax_code || '').trim(),
            address: String(normalizedSupplier.address || '').trim(),
            option_label: String(normalizedSupplier.option_label || '').trim() || [code, name].filter(Boolean).join(' - '),
            search_text: normalizeText(normalizedSupplier.search_text || [code, name, contactName, phone, email].join(' ')),
        };
    };

    Object.values(supplierPayload || {}).forEach((supplier) => {
        const normalizedSupplier = normalizeSupplier(supplier);
        if (String(normalizedSupplier.id || '') !== '') {
            supplierCache[String(normalizedSupplier.id)] = normalizedSupplier;
        }
    });

    return {
        query: '',
        results: [],
        open: false,
        loading: false,
        activeIndex: -1,
        requestToken: 0,
        init() {
            const searchField = this.searchField();
            const hiddenNameField = this.hiddenNameField();
            this.query = searchField ? searchField.value : (hiddenNameField ? hiddenNameField.value : '');
            this.registerGlobalApi();

            const initialSupplier = this.findInitialSupplier();
            if (initialSupplier) {
                this.selectSupplier(initialSupplier, false);
                return;
            }

            this.syncFormValue();
            this.clearDetails();
        },
        searchField() {
            return this.$refs.search instanceof HTMLInputElement ? this.$refs.search : document.getElementById('supplier-search-field');
        },
        hiddenIdField() {
            return document.getElementById('supplier-id-field');
        },
        hiddenNameField() {
            return document.getElementById('supplier-name-field');
        },
        detailField(id) {
            return document.getElementById(id);
        },
        allSuppliers() {
            return Object.values(supplierCache);
        },
        upsertSupplier(supplier) {
            const normalizedSupplier = normalizeSupplier(supplier);
            if (String(normalizedSupplier.id || '') !== '') {
                supplierCache[String(normalizedSupplier.id)] = normalizedSupplier;
            }

            return normalizedSupplier;
        },
        supplierMeta(supplier) {
            return [supplier.contact_name, supplier.phone, supplier.email].filter(Boolean).join(' • ');
        },
        clearDetails() {
            ['supplier-contact-field', 'supplier-phone-field', 'supplier-email-field', 'supplier-tax-code-field', 'supplier-address-field'].forEach((id) => {
                const field = this.detailField(id);
                if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                    field.value = '';
                }
            });
        },
        applySupplier(supplier) {
            const hiddenIdField = this.hiddenIdField();
            const hiddenNameField = this.hiddenNameField();
            const searchField = this.searchField();

            if (hiddenIdField instanceof HTMLInputElement) {
                hiddenIdField.value = String(supplier.id || '');
            }
            if (hiddenNameField instanceof HTMLInputElement) {
                hiddenNameField.value = String(supplier.name || '');
            }
            if (searchField instanceof HTMLInputElement) {
                searchField.value = String(supplier.option_label || supplier.name || '');
            }

            this.query = String(supplier.option_label || supplier.name || '');

            const contactField = this.detailField('supplier-contact-field');
            const phoneField = this.detailField('supplier-phone-field');
            const emailField = this.detailField('supplier-email-field');
            const taxCodeField = this.detailField('supplier-tax-code-field');
            const addressField = this.detailField('supplier-address-field');

            if (contactField instanceof HTMLInputElement) {
                contactField.value = String(supplier.contact_name || '');
            }
            if (phoneField instanceof HTMLInputElement) {
                phoneField.value = String(supplier.phone || '');
            }
            if (emailField instanceof HTMLInputElement) {
                emailField.value = String(supplier.email || '');
            }
            if (taxCodeField instanceof HTMLInputElement) {
                taxCodeField.value = String(supplier.tax_code || '');
            }
            if (addressField instanceof HTMLTextAreaElement) {
                addressField.value = String(supplier.address || '');
            }
        },
        resetSelection() {
            const hiddenIdField = this.hiddenIdField();
            const hiddenNameField = this.hiddenNameField();

            if (hiddenIdField instanceof HTMLInputElement) {
                hiddenIdField.value = '';
            }
            if (hiddenNameField instanceof HTMLInputElement) {
                hiddenNameField.value = this.query.trim();
            }

            this.clearDetails();
        },
        syncFormValue() {
            const hiddenIdField = this.hiddenIdField();
            if (hiddenIdField instanceof HTMLInputElement && hiddenIdField.value.trim() !== '') {
                return;
            }

            const hiddenNameField = this.hiddenNameField();
            if (hiddenNameField instanceof HTMLInputElement) {
                hiddenNameField.value = this.query.trim();
            }
        },
        localResults(query) {
            const normalizedQuery = normalizeText(query);
            const suppliers = this.allSuppliers();
            if (normalizedQuery === '') {
                return suppliers.slice(0, 8);
            }

            return suppliers
                .filter((supplier) => supplier.search_text.includes(normalizedQuery) || normalizeText(supplier.option_label).includes(normalizedQuery))
                .slice(0, 8);
        },
        findInitialSupplier() {
            const hiddenIdField = this.hiddenIdField();
            const initialId = hiddenIdField instanceof HTMLInputElement ? hiddenIdField.value.trim() : '';
            if (initialId !== '' && supplierCache[initialId]) {
                return supplierCache[initialId];
            }

            const candidate = this.query.trim();
            if (candidate === '') {
                return null;
            }

            return this.allSuppliers().find((supplier) => {
                const normalizedCandidate = normalizeText(candidate);
                return normalizeText(supplier.option_label) === normalizedCandidate
                    || normalizeText(supplier.name) === normalizedCandidate
                    || normalizeText(supplier.code) === normalizedCandidate;
            }) || null;
        },
        findBestMatch(query) {
            const normalizedQuery = normalizeText(query);
            if (normalizedQuery === '') {
                return null;
            }

            return this.allSuppliers().find((supplier) => {
                return normalizeText(supplier.option_label) === normalizedQuery
                    || normalizeText(supplier.name) === normalizedQuery
                    || normalizeText(supplier.code) === normalizedQuery
                    || supplier.search_text.includes(normalizedQuery);
            }) || null;
        },
        async fetchResults(query, openWhenEmpty = true) {
            const normalizedQuery = query.trim();

            if (normalizedQuery === '') {
                this.results = this.localResults('');
                this.activeIndex = this.results.length > 0 ? 0 : -1;
                this.open = openWhenEmpty && this.results.length > 0;
                this.loading = false;
                return;
            }

            this.loading = true;
            const token = ++this.requestToken;

            try {
                const response = await fetch(searchUrl + '?q=' + encodeURIComponent(normalizedQuery), {
                    headers: {
                        Accept: 'application/json',
                    },
                });
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error((payload && payload.message) || 'Không thể tải nhà cung cấp.');
                }

                if (token !== this.requestToken) {
                    return;
                }

                const suppliers = Array.isArray(payload.data) ? payload.data : [];
                this.results = suppliers.map((supplier) => this.upsertSupplier(supplier));
            } catch (error) {
                if (token !== this.requestToken) {
                    return;
                }

                this.results = this.localResults(normalizedQuery);
            } finally {
                if (token === this.requestToken) {
                    this.loading = false;
                    this.activeIndex = this.results.length > 0 ? 0 : -1;
                    this.open = openWhenEmpty || this.results.length > 0;
                }
            }
        },
        async openDropdown() {
            await this.fetchResults(this.query, true);
        },
        async handleInput() {
            this.resetSelection();
            this.syncFormValue();
            await this.fetchResults(this.query, true);
        },
        selectSupplier(supplier, closeAfterSelect = true) {
            const normalizedSupplier = this.upsertSupplier(supplier);
            this.applySupplier(normalizedSupplier);
            if (closeAfterSelect) {
                this.close();
            }

            return normalizedSupplier;
        },
        selectActive() {
            if (this.activeIndex < 0 || this.activeIndex >= this.results.length) {
                const matchedSupplier = this.findBestMatch(this.query);
                if (matchedSupplier) {
                    this.selectSupplier(matchedSupplier);
                    return;
                }

                this.syncFormValue();
                this.close();
                return;
            }

            this.selectSupplier(this.results[this.activeIndex]);
        },
        highlightNext() {
            if (!this.open) {
                this.openDropdown();
                return;
            }

            if (this.results.length === 0) {
                return;
            }

            this.activeIndex = Math.min(this.activeIndex + 1, this.results.length - 1);
        },
        highlightPrev() {
            if (!this.open || this.results.length === 0) {
                return;
            }

            this.activeIndex = Math.max(this.activeIndex - 1, 0);
        },
        handleBlur() {
            window.setTimeout(() => {
                const matchedSupplier = this.findBestMatch(this.query);
                if (matchedSupplier) {
                    this.selectSupplier(matchedSupplier);
                    return;
                }

                this.syncFormValue();
                this.close();
            }, 120);
        },
        close() {
            this.open = false;
            this.activeIndex = -1;
        },
        focusSearch() {
            const searchField = this.searchField();
            if (searchField instanceof HTMLInputElement) {
                searchField.focus();
                searchField.select();
            }
        },
        registerGlobalApi() {
            window.PurchaseOrderSupplierPicker = {
                closeDropdown: () => this.close(),
                focusSearch: () => this.focusSearch(),
                syncFormValue: () => this.syncFormValue(),
                selectSupplier: (supplier) => this.selectSupplier(supplier),
                upsertSupplier: (supplier) => this.upsertSupplier(supplier),
            };
        },
    };
};

const initPurchaseOrderForm = () => {
    const body = document.getElementById('poItemsBody');
    const templateRow = document.getElementById('poItemRowTemplate');
    const addButton = document.getElementById('add-item-button');
    const form = document.getElementById('po-form');
    const codeField = document.getElementById('po-code-field');
    const orderDateField = document.getElementById('po-date-field');
    const taxPercentInput = document.getElementById('tax-percent-input');
    const taxAmountPreview = document.getElementById('tax-amount-preview');
    const subtotalPreview = document.getElementById('subtotal-preview');
    const discountPreview = document.getElementById('discount-preview');
    const totalPreview = document.getElementById('total-preview');
    const materialPayloadElement = document.getElementById('po-material-payload');
    const materialPayload = JSON.parse(materialPayloadElement ? materialPayloadElement.textContent : '{}');
    const materialOptions = Object.values(materialPayload);
    const supplierPicker = window.PurchaseOrderSupplierPicker || {
        closeDropdown() {},
        focusSearch() {},
        selectSupplier() {},
        syncFormValue() {},
    };
    const createModalInstance = (element) => {
        if (!element || typeof window.bootstrap === 'undefined' || !window.bootstrap.Modal) {
            return null;
        }

        return window.bootstrap.Modal.getOrCreateInstance(element);
    };
    const showModal = (element, instance) => {
        if (!element) {
            return;
        }

        if (instance) {
            instance.show();
            return;
        }

        element.classList.add('show');
        element.style.display = 'block';
        element.removeAttribute('aria-hidden');
        element.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');

        let backdrop = document.querySelector('[data-po-modal-backdrop]');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.setAttribute('data-po-modal-backdrop', 'true');
            document.body.appendChild(backdrop);
        }
    };
    const hideModal = (element, instance) => {
        if (!element) {
            return;
        }

        if (instance) {
            instance.hide();
            return;
        }

        element.classList.remove('show');
        element.style.display = 'none';
        element.setAttribute('aria-hidden', 'true');
        element.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');

        const backdrop = document.querySelector('[data-po-modal-backdrop]');
        if (backdrop) {
            backdrop.remove();
        }
    };
    const quickSupplierModalElement = document.getElementById('quick-supplier-modal');
    const quickSupplierModal = createModalInstance(quickSupplierModalElement);
    const quickSupplierForm = document.getElementById('quick-supplier-form');
    const quickSupplierMessage = document.getElementById('quick-supplier-message');
    const quickSupplierSubmitButton = document.getElementById('quick-supplier-submit-button');
    const quickMaterialModalElement = document.getElementById('quick-material-modal');
    const quickMaterialModal = createModalInstance(quickMaterialModalElement);
    const quickMaterialForm = document.getElementById('quick-material-form');
    const quickMaterialMessage = document.getElementById('quick-material-message');
    const quickMaterialSubmitButton = document.getElementById('quick-material-submit-button');
    let quickMaterialTargetRow = null;
    let autoCodeValue = <?php echo json_encode((string) $suggestedCode, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

    const parseNumber = (value) => {
        const normalizedValue = value === undefined || value === null ? '' : String(value);
        const parsed = Number.parseFloat(normalizedValue.replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : 0;
    };
    const formatNumber = (value) => value.toFixed(2);
    const getRows = () => body ? Array.from(body.querySelectorAll('tr[data-item-row]')) : [];
    const buildPoCode = (dateValue) => {
        const matched = String(dateValue || '').match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!matched) {
            return '';
        }

        const year = matched[1].slice(-2);
        const month = matched[2];
        const day = matched[3];

        return 'PO' + month + year + '-' + day + '-00';
    };
    const showMessage = (element, type, message) => {
        if (!element) return;
        element.className = 'erp-form-message alert alert-' + type;
        element.textContent = message;
    };
    const clearMessage = (element) => {
        if (!element) return;
        element.className = 'erp-form-message';
        element.textContent = '';
    };
    const normalizeText = (value) => String(value || '').trim().toLowerCase();
    const findMaterialByLabel = (label) => {
        const normalized = normalizeText(label);
        return materialOptions.find((material) => String(material.option_label || '').trim().toLowerCase() === normalized) || null;
    };

    const updateRowNumbers = () => {
        getRows().forEach((row, index) => {
            const cell = row.querySelector('.item-row-number');
            if (cell) {
                cell.textContent = String(index + 1);
            }
        });
    };
    const applyRowNames = (row, index) => {
        row.dataset.index = String(index);
        row.querySelectorAll('[data-name-key]').forEach((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                return;
            }

            field.disabled = false;
            field.name = 'items[' + index + '][' + field.dataset.nameKey + ']';
        });
    };
    const disableRowFields = (row) => {
        row.querySelectorAll('[data-name-key]').forEach((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                return;
            }

            field.disabled = true;
        });
    };
    const reindexRows = () => {
        getRows().forEach((row, index) => {
            applyRowNames(row, index);
        });
        updateRowNumbers();
    };
    const nextRowIndex = () => body ? body.querySelectorAll('tr[data-item-row]').length : 0;
    const rowFieldValue = (row, selector) => {
        const field = row.querySelector(selector);
        return field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement
            ? field.value.trim()
            : '';
    };
    const isRowEmpty = (row) => {
        return rowFieldValue(row, '.item-material') === ''
            && rowFieldValue(row, '.item-material-search') === ''
            && rowFieldValue(row, '.item-description') === ''
            && rowFieldValue(row, '.item-unit') === ''
            && rowFieldValue(row, '.item-quantity') === ''
            && rowFieldValue(row, '.item-unit-price') === ''
            && rowFieldValue(row, '.item-discount') === '';
    };
    const setClientFieldError = (row, fieldSelector, errorKey, message) => {
        const field = row.querySelector(fieldSelector);
        if (!(field instanceof HTMLElement)) {
            return;
        }

        const container = field.parentElement || row;
        let feedback = container.querySelector('.js-field-error[data-error-for="' + errorKey + '"]');
        if (message === '') {
            field.classList.remove('is-invalid');
            if (feedback instanceof HTMLElement) {
                feedback.remove();
            }
            return;
        }

        field.classList.add('is-invalid');
        if (!(feedback instanceof HTMLElement)) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block js-field-error';
            feedback.setAttribute('data-error-for', errorKey);
            container.appendChild(feedback);
        }
        feedback.textContent = message;
    };
    const clearClientErrors = (row) => {
        row.classList.remove('has-row-error');
        row.querySelectorAll('.js-field-error').forEach((element) => element.remove());
        row.querySelectorAll('.item-material-search, .item-quantity, .item-unit-price').forEach((field) => {
            if (field instanceof HTMLElement) {
                field.classList.remove('is-invalid');
            }
        });
    };
    const validateDuplicateMaterials = () => {
        const seenMaterials = new Map();
        let hasDuplicate = false;

        getRows().forEach((row) => {
            setClientFieldError(row, '.item-material-search', 'duplicate-material', '');
            if (isRowEmpty(row)) {
                return;
            }

            const materialId = rowFieldValue(row, '.item-material');
            if (materialId === '') {
                return;
            }

            if (!seenMaterials.has(materialId)) {
                seenMaterials.set(materialId, row);
                return;
            }

            const materialField = row.querySelector('.item-material');
            const materialSearchField = row.querySelector('.item-material-search');
            if (materialField instanceof HTMLInputElement) {
                materialField.value = '';
            }
            if (materialSearchField instanceof HTMLInputElement) {
                materialSearchField.value = '';
            }
            syncRowMaterial(row, false);
            row.classList.add('has-row-error');
            setClientFieldError(row, '.item-material-search', 'duplicate-material', 'Vật tư đã tồn tại ở dòng khác');
            hasDuplicate = true;
        });

        return !hasDuplicate;
    };
    const validateRowsBeforeSubmit = () => {
        const errors = [];

        getRows().forEach((row) => {
            clearClientErrors(row);
            if (isRowEmpty(row)) {
                return;
            }

            const materialId = rowFieldValue(row, '.item-material');
            const quantity = parseNumber(rowFieldValue(row, '.item-quantity'));
            const unitPrice = parseNumber(rowFieldValue(row, '.item-unit-price'));

            if (materialId === '') {
                row.classList.add('has-row-error');
                setClientFieldError(row, '.item-material-search', 'material-required', 'Vui lòng chọn vật tư');
                errors.push(row.querySelector('.item-material-search'));
            }

            if (quantity <= 0) {
                row.classList.add('has-row-error');
                setClientFieldError(row, '.item-quantity', 'quantity-invalid', 'Số lượng phải lớn hơn 0');
                errors.push(row.querySelector('.item-quantity'));
            }

            if (unitPrice < 0) {
                row.classList.add('has-row-error');
                setClientFieldError(row, '.item-unit-price', 'unit-price-invalid', 'Đơn giá phải lớn hơn hoặc bằng 0');
                errors.push(row.querySelector('.item-unit-price'));
            }
        });

        const nonEmptyRows = getRows().filter((row) => !isRowEmpty(row));
        if (nonEmptyRows.length === 0 && body instanceof HTMLElement) {
            errors.push(body);
        }

        if (!validateDuplicateMaterials()) {
            const duplicateField = body ? body.querySelector('.js-field-error[data-error-for="duplicate-material"]') : null;
            if (duplicateField) {
                errors.push(duplicateField);
            }
        }

        return errors;
    };
    const resetRow = (row) => {
        row.querySelectorAll('input, textarea, select').forEach((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                return;
            }

            if (field.classList.contains('item-quantity')) {
                field.value = '1.00';
                return;
            }

            if (field.classList.contains('item-unit-price') || field.classList.contains('item-discount') || field.classList.contains('item-total')) {
                field.value = '0.00';
                return;
            }

            field.value = '';
        });
    };

    const syncRowMaterial = (row, resetPrice = false) => {
        const materialIdField = row.querySelector('.item-material');
        const materialId = materialIdField instanceof HTMLInputElement ? materialIdField.value : '';
        const materialSearchField = row.querySelector('.item-material-search');
        const descriptionField = row.querySelector('.item-description');
        const unitField = row.querySelector('.item-unit');
        const unitPriceField = row.querySelector('.item-unit-price');
        const material = materialId !== '' ? materialPayload[materialId] || null : null;

        if (material && descriptionField instanceof HTMLInputElement && descriptionField.value.trim() === '') {
            descriptionField.value = material.name || '';
        }

        if (material && unitField instanceof HTMLInputElement) {
            unitField.value = material.unit || '';
        }

        if (material && resetPrice && unitPriceField instanceof HTMLInputElement) {
            unitPriceField.value = material.standard_cost || '0.00';
        }

        if (materialSearchField instanceof HTMLInputElement) {
            materialSearchField.value = material ? (material.option_label || '') : '';
        }
    };

    const calculateRowTotal = (row) => {
        const quantityField = row.querySelector('.item-quantity');
        const unitPriceField = row.querySelector('.item-unit-price');
        const discountField = row.querySelector('.item-discount');
        const quantity = Math.max(parseNumber(quantityField instanceof HTMLInputElement ? quantityField.value : 0), 0);
        const unitPrice = Math.max(parseNumber(unitPriceField instanceof HTMLInputElement ? unitPriceField.value : 0), 0);
        const discount = Math.max(parseNumber(discountField instanceof HTMLInputElement ? discountField.value : 0), 0);
        const lineGross = quantity * unitPrice;
        const lineDiscount = Math.min(discount, lineGross);
        const lineTotal = Math.max(lineGross - lineDiscount, 0);
        const totalField = row.querySelector('.item-total');

        if (totalField instanceof HTMLInputElement) {
            totalField.value = formatNumber(lineTotal);
        }

        return {
            lineGross,
            lineDiscount,
            lineTotal,
        };
    };
    const calculateTotals = () => {
        let subtotal = 0;
        let discountAmount = 0;

        getRows().forEach((row) => {
            const totals = calculateRowTotal(row);
            subtotal += totals.lineGross;
            discountAmount += totals.lineDiscount;
        });

        const netAmount = subtotal - discountAmount;
        const taxPercent = Math.max(parseNumber(taxPercentInput instanceof HTMLInputElement ? taxPercentInput.value : 0), 0);
        const taxAmount = netAmount * (taxPercent / 100);
        if (subtotalPreview instanceof HTMLInputElement) {
            subtotalPreview.value = formatNumber(subtotal);
        }
        if (discountPreview instanceof HTMLInputElement) {
            discountPreview.value = formatNumber(discountAmount);
        }
        if (taxAmountPreview instanceof HTMLInputElement) {
            taxAmountPreview.value = formatNumber(taxAmount);
        }
        if (totalPreview instanceof HTMLInputElement) {
            totalPreview.value = formatNumber(netAmount + taxAmount);
        }
        updateRowNumbers();
    };

    const addRow = () => {
        if (!body || !(templateRow instanceof HTMLTableRowElement)) {
            return null;
        }

        const index = nextRowIndex();
        const row = templateRow.cloneNode(true);
        row.removeAttribute('id');
        row.removeAttribute('hidden');
        applyRowNames(row, index);
        resetRow(row);
        body.appendChild(row);
        syncRowMaterial(row, false);
        reindexRows();
        calculateTotals();

        return row;
    };
    window.addPoItemRow = addRow;

    if (templateRow instanceof HTMLTableRowElement) {
        disableRowFields(templateRow);
    }

    if (addButton) addButton.addEventListener('click', addRow);
    const openQuickMaterialButton = document.getElementById('open-quick-material-button');
    const openQuickSupplierButton = document.getElementById('open-quick-supplier-button');
    if (openQuickSupplierButton) openQuickSupplierButton.addEventListener('click', () => {
        if (quickSupplierForm) quickSupplierForm.reset();
        clearMessage(quickSupplierMessage);
        showModal(quickSupplierModalElement, quickSupplierModal);
        supplierPicker.closeDropdown();
    });
    if (openQuickMaterialButton) openQuickMaterialButton.addEventListener('click', () => {
        if (quickMaterialTargetRow && body && !body.contains(quickMaterialTargetRow)) {
            quickMaterialTargetRow = null;
        }

        if (!quickMaterialTargetRow) {
            quickMaterialTargetRow = getRows()[getRows().length - 1] || addRow();
        }

        if (quickMaterialForm) {
            quickMaterialForm.reset();
        }
        const costField = quickMaterialForm ? quickMaterialForm.querySelector('[name="standard_cost"]') : null;
        if (costField instanceof HTMLInputElement) {
            costField.value = '0.00';
        }
        clearMessage(quickMaterialMessage);
        showModal(quickMaterialModalElement, quickMaterialModal);
    });

    if (body) body.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const removeButton = target.closest('.remove-item-button');
        if (!removeButton) {
            return;
        }

        const targetRow = removeButton.closest('[data-item-row]');
        if (targetRow) {
            targetRow.remove();
        }

        reindexRows();
        calculateTotals();
        return;
    });

    if (body) body.addEventListener('focusin', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const row = target.closest('[data-item-row]');
        if (row) {
            quickMaterialTargetRow = row;
        }
    });

    if (body) body.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const row = target.closest('[data-item-row]');
        if (!row) {
            return;
        }

        if (target.matches('.item-material-search')) {
            const material = findMaterialByLabel(target.value);
            const materialField = row.querySelector('.item-material');
            if (materialField instanceof HTMLInputElement) {
                materialField.value = material ? String(material.id) : '';
            }
            setClientFieldError(row, '.item-material-search', 'material-required', '');
            syncRowMaterial(row, true);
            validateDuplicateMaterials();
            calculateTotals();
        }
    });
    if (body) body.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (!target.matches('.item-quantity, .item-unit-price, .item-discount')) {
            return;
        }

        const row = target.closest('[data-item-row]');
        if (!row) {
            return;
        }

        calculateRowTotal(row);
        calculateTotals();
    });

    if (form) form.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('.item-quantity, .item-unit-price, .item-discount, #tax-percent-input')) {
            calculateTotals();
        }
    });
    if (form) form.addEventListener('submit', (event) => {
        supplierPicker.syncFormValue();
        const supplierIdField = document.getElementById('supplier-id-field');
        const supplierSearchField = document.getElementById('supplier-search-field');
        const errors = validateRowsBeforeSubmit();

        if (!(supplierIdField instanceof HTMLInputElement) || supplierIdField.value.trim() === '') {
            if (supplierSearchField instanceof HTMLInputElement) {
                supplierSearchField.classList.add('is-invalid');
                supplierSearchField.focus();
            }
            event.preventDefault();
            return;
        }

        if (supplierSearchField instanceof HTMLInputElement) {
            supplierSearchField.classList.remove('is-invalid');
        }

        if (errors.length > 0) {
            event.preventDefault();
            const firstError = errors[0];
            if (firstError instanceof HTMLElement) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstError.focus === 'function') {
                    firstError.focus();
                }
            }
        }
    });
    if (codeField instanceof HTMLInputElement) {
        codeField.dataset.autoGenerated = codeField.value.trim() === autoCodeValue ? '1' : '0';
        codeField.addEventListener('input', () => {
            codeField.dataset.autoGenerated = codeField.value.trim() === '' || codeField.value.trim() === autoCodeValue ? '1' : '0';
        });
    }
    if (orderDateField instanceof HTMLInputElement && codeField instanceof HTMLInputElement) {
        orderDateField.addEventListener('change', () => {
            const nextSuggestedCode = buildPoCode(orderDateField.value);
            const isAutoGenerated = codeField.dataset.autoGenerated === '1' || codeField.value.trim() === '';

            autoCodeValue = nextSuggestedCode !== '' ? nextSuggestedCode : autoCodeValue;
            if (isAutoGenerated && nextSuggestedCode !== '') {
                codeField.value = nextSuggestedCode;
                codeField.dataset.autoGenerated = '1';
            }
        });
    }

    const firstErrorFrom = (payload) => {
        if (!payload || !payload.errors) {
            return null;
        }

        const groups = Object.values(payload.errors);
        for (let i = 0; i < groups.length; i++) {
            const group = groups[i];
            if (Array.isArray(group) && group.length > 0) {
                return group[0];
            }
        }

        return null;
    };

    if (quickSupplierSubmitButton) quickSupplierSubmitButton.addEventListener('click', async () => {
        if (!quickSupplierForm) return;
        clearMessage(quickSupplierMessage);
        quickSupplierSubmitButton.disabled = true;
        quickSupplierSubmitButton.textContent = 'Đang lưu...';

        try {
            const response = await fetch(<?php echo json_encode(app_url('/api/suppliers/quick-create'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>, {
                method: 'POST',
                body: new FormData(quickSupplierForm)
            });
            const responsePayload = await response.json();
            if (!response.ok) {
                const firstError = firstErrorFrom(responsePayload);
                throw new Error(firstError || (responsePayload && responsePayload.message) || 'Không thể tạo nhanh nhà cung cấp.');
            }
            const supplier = responsePayload.data;
            supplierPicker.selectSupplier(supplier);
            hideModal(quickSupplierModalElement, quickSupplierModal);
            supplierPicker.focusSearch();
        } catch (error) {
            showMessage(quickSupplierMessage, 'warning', error instanceof Error ? error.message : 'Không thể tạo nhanh nhà cung cấp.');
        } finally {
            quickSupplierSubmitButton.disabled = false;
            quickSupplierSubmitButton.textContent = 'Lưu NCC';
        }
    });

    if (quickMaterialSubmitButton) quickMaterialSubmitButton.addEventListener('click', async () => {
        if (quickMaterialTargetRow && body && !body.contains(quickMaterialTargetRow)) {
            quickMaterialTargetRow = null;
        }
        if (!quickMaterialForm) return;
        if (!quickMaterialTargetRow) {
            quickMaterialTargetRow = getRows()[getRows().length - 1] || addRow();
        }
        if (!quickMaterialTargetRow) return;
        clearMessage(quickMaterialMessage);
        quickMaterialSubmitButton.disabled = true;
        quickMaterialSubmitButton.textContent = 'Đang lưu...';

        try {
            const response = await fetch(<?php echo json_encode(app_url('/api/materials/quick-create'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>, {
                method: 'POST',
                body: new FormData(quickMaterialForm)
            });
            const responsePayload = await response.json();
            if (!response.ok) {
                const firstError = firstErrorFrom(responsePayload);
                throw new Error(firstError || (responsePayload && responsePayload.message) || 'Không thể tạo nhanh vật tư.');
            }
            const material = responsePayload.data;
            materialPayload[String(material.id)] = material;
            materialOptions.unshift(material);
            const materialField = quickMaterialTargetRow.querySelector('.item-material');
            if (materialField instanceof HTMLInputElement) {
                materialField.value = String(material.id);
            }
            syncRowMaterial(quickMaterialTargetRow, true);
            calculateTotals();
            hideModal(quickMaterialModalElement, quickMaterialModal);
        } catch (error) {
            showMessage(quickMaterialMessage, 'warning', error instanceof Error ? error.message : 'Không thể tạo nhanh vật tư.');
        } finally {
            quickMaterialSubmitButton.disabled = false;
            quickMaterialSubmitButton.textContent = 'Lưu vật tư';
        }
    });

    reindexRows();
    getRows().forEach((row) => syncRowMaterial(row, false));
    validateDuplicateMaterials();
    calculateTotals();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPurchaseOrderForm, { once: true });
} else {
    initPurchaseOrderForm();
}
</script>
</body>
</html>
