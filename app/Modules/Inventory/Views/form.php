<?php
$activeSidebar = $activeSidebar ?? 'inventory';
$pageTitle = $pageTitle ?? 'Tạo phiếu nhập kho';
$pageEyebrow = $pageEyebrow ?? 'Kho / Nhập kho';
$formAction = $formAction ?? '/stocks/store';
$transaction = $transaction ?? [];
$txnTypes = $txnTypes ?? [];
$materials = $materials ?? [];
$components = $components ?? [];
$materialCategoryOptions = $materialCategoryOptions ?? [];
$itemPayload = $itemPayload ?? ['materials' => [], 'components' => []];
$errors = $errors ?? [];
$items = $transaction['items'] ?? [];
$hasExistingInput = $transaction !== [] || $errors !== [];
$canQuickCreateMaterial = has_permission('material.create');

if ($items === []) {
    $items = [[
        'item_kind' => 'material',
        'material_id' => '',
        'component_id' => '',
        'quantity' => '1.00',
        'unit_cost' => '0.00',
    ]];
}

$txnTypeLabels = [
    'import' => 'Nhập kho',
    'export' => 'Xuất kho',
    'adjustment' => 'Điều chỉnh',
];

$field = static function (string $key, string $default = '') use ($transaction): string {
    return htmlspecialchars((string) ($transaction[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};

$itemErrorFor = static function (int|string $index, string $key) use ($errors): ?string {
    return $errors["items.{$index}.{$key}"][0] ?? null;
};

$selectedTxnType = (string) ($transaction['txn_type'] ?? 'import');
$draftKey = 'iconvina.stock-import.draft';
$bodyError = $errorFor('items');

$renderItemRow = static function (int|string $index, int $rowNumber, array $item, bool $isTemplate = false) use ($materials, $components, $itemPayload, $itemErrorFor, $canQuickCreateMaterial): string {
    $kind = (string) ($item['item_kind'] ?? 'material');
    $materialId = (string) ($item['material_id'] ?? '');
    $componentId = (string) ($item['component_id'] ?? '');
    $quantity = htmlspecialchars((string) ($item['quantity'] ?? '1.00'), ENT_QUOTES, 'UTF-8');
    $unitCost = htmlspecialchars((string) ($item['unit_cost'] ?? '0.00'), ENT_QUOTES, 'UTF-8');
    $materialMeta = $materialId !== '' && isset($itemPayload['materials'][$materialId]) ? $itemPayload['materials'][$materialId] : null;
    $componentMeta = $componentId !== '' && isset($itemPayload['components'][$componentId]) ? $itemPayload['components'][$componentId] : null;
    $categoryText = $kind === 'material'
        ? trim((string) (($materialMeta['category_code'] ?? '') !== '' ? ($materialMeta['category_code'] . ' / ' . ($materialMeta['category_name'] ?? '')) : ($materialMeta['category_name'] ?? '')))
        : '';
    $unitText = $kind === 'material'
        ? (string) ($materialMeta['unit'] ?? '')
        : (string) ($componentMeta['unit'] ?? '');
    $selectedMeta = $kind === 'material' ? $materialMeta : $componentMeta;
    $lineTotal = number_format(((float) ($item['quantity'] ?? 0) * (float) ($item['unit_cost'] ?? 0)), 2, '.', '');
    $rowError = static function (string $key) use ($isTemplate, $itemErrorFor, $index): ?string {
        return $isTemplate ? null : $itemErrorFor($index, $key);
    };

    ob_start();
    ?>
    <tr class="erp-row-compact" data-item-row>
        <td class="erp-stock-table__index item-row-number"><?php echo $rowNumber; ?></td>
        <td>
            <select name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][item_kind]" class="form-select erp-select item-kind <?php echo $rowError('item_kind') ? 'is-invalid' : ''; ?>">
                <option value="material" <?php echo $kind === 'material' ? 'selected' : ''; ?>>Nguyên vật liệu</option>
                <option value="component" <?php echo $kind === 'component' ? 'selected' : ''; ?>>Bán thành phẩm</option>
            </select>
            <?php if ($rowError('item_kind')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('item_kind'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="erp-stock-cell--item">
            <div class="erp-stock-item-cell">
                <select class="form-select erp-select item-product-select <?php echo ($rowError('material_id') || $rowError('component_id')) ? 'is-invalid' : ''; ?>">
                    <option value=""><?php echo $kind === 'material' ? 'Chọn vật tư' : 'Chọn bán thành phẩm'; ?></option>
                    <?php if ($kind === 'material'): ?>
                        <?php foreach ($materials as $material): ?>
                            <option value="<?php echo (int) $material['id']; ?>" <?php echo $materialId === (string) $material['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $material['code'] . ' - ' . (string) $material['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($components as $component): ?>
                            <option value="<?php echo (int) $component['id']; ?>" <?php echo $componentId === (string) $component['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <input type="hidden" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][material_id]" class="item-material" value="<?php echo htmlspecialchars($materialId, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][component_id]" class="item-component" value="<?php echo htmlspecialchars($componentId, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($rowError('material_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('material_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if ($rowError('component_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('component_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if ($selectedMeta !== null): ?>
                    <div class="erp-cell-secondary text-truncate" style="max-width: 280px;"><?php echo htmlspecialchars((string) ($selectedMeta['option_label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        </td>
        <td class="erp-stock-cell--category"><div class="erp-stock-readonly item-category <?php echo $categoryText === '' ? 'is-empty' : ''; ?>"><?php echo htmlspecialchars($categoryText !== '' ? $categoryText : 'Không áp dụng', ENT_QUOTES, 'UTF-8'); ?></div></td>
        <td class="erp-stock-cell--unit"><div class="erp-stock-readonly item-unit <?php echo $unitText === '' ? 'is-empty' : ''; ?>"><?php echo htmlspecialchars($unitText !== '' ? $unitText : 'Tự động', ENT_QUOTES, 'UTF-8'); ?></div></td>
        <td class="erp-stock-cell--qty">
            <input type="number" step="0.01" min="0" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][quantity]" class="form-control erp-field erp-stock-number item-quantity <?php echo $rowError('quantity') ? 'is-invalid' : ''; ?>" value="<?php echo $quantity; ?>">
            <?php if ($rowError('quantity')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="erp-stock-cell--cost">
            <input type="number" step="0.01" min="0" name="items[<?php echo htmlspecialchars((string) $index, ENT_QUOTES, 'UTF-8'); ?>][unit_cost]" class="form-control erp-field erp-stock-number item-unit-cost <?php echo $rowError('unit_cost') ? 'is-invalid' : ''; ?>" value="<?php echo $unitCost; ?>">
            <?php if ($rowError('unit_cost')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $rowError('unit_cost'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        </td>
        <td class="erp-stock-cell--total"><div class="erp-stock-readonly justify-content-end item-line-total"><?php echo htmlspecialchars($lineTotal, ENT_QUOTES, 'UTF-8'); ?></div></td>
        <td class="text-end erp-stock-cell--action"><button type="button" class="btn btn-light erp-btn-sm remove-item-button" title="Xóa dòng"><i class="bi bi-trash"></i></button></td>
    </tr>
    <?php

    return (string) ob_get_clean();
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
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?></style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="stockEntryForm" novalidate>
                    <div class="erp-workflow-toolbar mb-4">
                        <div class="erp-workflow-toolbar__meta">
                            <div class="erp-breadcrumb mb-2">
                                <span>Kho</span>
                                <span class="erp-breadcrumb__sep">/</span>
                                <span>Nhập kho</span>
                            </div>
                            <h2 class="h3 mb-1 fw-bold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="erp-inline-note">Nhập nhanh vật tư, bán thành phẩm và điều chỉnh tăng tồn trên một màn hình.</div>
                        </div>
                        <div class="erp-workflow-toolbar__actions">
                            <a href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn" id="cancelStockEntry">Hủy</a>
                            <button type="button" class="btn erp-btn erp-btn-ghost" id="saveDraftButton">Lưu nháp</button>
                            <button type="submit" class="btn btn-dark erp-btn" id="submitStockEntry">Ghi nhận nhập kho</button>
                        </div>
                    </div>

                    <div class="erp-form-message mb-3" id="stockDraftMessage"></div>

                    <div class="erp-stock-layout">
                        <div class="erp-stock-main">
                            <div class="erp-card p-3 p-lg-4">
                                <div class="erp-section-heading">
                                    <div>
                                        <div class="erp-section-heading__eyebrow">Thông tin phiếu</div>
                                        <h3 class="erp-section-heading__title">Thông tin đầu phiếu</h3>
                                    </div>
                                </div>
                                <div class="erp-stock-fields">
                                    <div class="erp-stock-field erp-stock-field--span-3">
                                        <label class="erp-stock-label" for="stockTxnNo">Mã phiếu</label>
                                        <input id="stockTxnNo" type="text" name="txn_no" class="form-control erp-field <?php echo $errorFor('txn_no') ? 'is-invalid' : ''; ?>" value="<?php echo $field('txn_no'); ?>" maxlength="30" placeholder="PNK-20260321-001">
                                        <?php if ($errorFor('txn_no')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $errorFor('txn_no'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="erp-stock-field erp-stock-field--span-3">
                                        <label class="erp-stock-label" for="stockTxnType">Loại giao dịch</label>
                                        <select id="stockTxnType" name="txn_type" class="form-select erp-select <?php echo $errorFor('txn_type') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($txnTypes as $txnTypeOption): ?>
                                                <option value="<?php echo htmlspecialchars($txnTypeOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedTxnType === $txnTypeOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($txnTypeLabels[$txnTypeOption] ?? ucfirst($txnTypeOption), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('txn_type')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $errorFor('txn_type'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="erp-stock-field erp-stock-field--span-3">
                                        <label class="erp-stock-label" for="stockTxnDate">Ngày giao dịch</label>
                                        <input id="stockTxnDate" type="date" name="txn_date" class="form-control erp-field <?php echo $errorFor('txn_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('txn_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('txn_date')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $errorFor('txn_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="erp-stock-field erp-stock-field--span-3">
                                        <label class="erp-stock-label" for="stockRefType">Loại tham chiếu</label>
                                        <input id="stockRefType" type="text" name="ref_type" class="form-control erp-field <?php echo $errorFor('ref_type') ? 'is-invalid' : ''; ?>" value="<?php echo $field('ref_type'); ?>" maxlength="30" placeholder="PO / Sản xuất / Manual">
                                        <?php if ($errorFor('ref_type')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $errorFor('ref_type'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="erp-stock-field erp-stock-field--span-3">
                                        <label class="erp-stock-label" for="stockRefId">Mã tham chiếu</label>
                                        <input id="stockRefId" type="number" min="0" name="ref_id" class="form-control erp-field <?php echo $errorFor('ref_id') ? 'is-invalid' : ''; ?>" value="<?php echo $field('ref_id'); ?>" placeholder="ID chứng từ liên kết">
                                        <?php if ($errorFor('ref_id')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $errorFor('ref_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="erp-stock-field erp-stock-field--span-6">
                                        <label class="erp-stock-label" for="stockNote">Ghi chú phiếu</label>
                                        <textarea id="stockNote" name="note" rows="3" class="form-control erp-textarea <?php echo $errorFor('note') ? 'is-invalid' : ''; ?>" placeholder="Ghi chú chung cho phiếu nhập kho"><?php echo $field('note'); ?></textarea>
                                        <?php if ($errorFor('note')): ?><div class="invalid-feedback d-block"><?php echo htmlspecialchars((string) $errorFor('note'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="erp-card p-3 p-lg-4 erp-stock-table-card">
                                <div class="erp-stock-table-toolbar">
                                    <div>
                                        <div class="erp-section-heading__eyebrow">Dòng nhập kho</div>
                                        <h3 class="erp-section-heading__title">Danh sách hàng nhập</h3>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="erp-inline-note">Có thể tạo nhanh vật tư mới ngay trong phiếu nhập.</div>
                                        <button type="button" class="btn btn-light erp-btn" id="addItemButton"><i class="bi bi-plus-lg"></i>Thêm dòng</button>
                                    </div>
                                </div>

                                <?php if ($bodyError): ?>
                                    <div class="alert alert-danger mb-3"><?php echo htmlspecialchars((string) $bodyError, ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>

                                <div class="erp-table-shell p-2">
                                    <div class="erp-table-wrap">
                                        <table class="table erp-table erp-stock-table align-middle mb-0" id="stockEntryTable">
                                            <colgroup>
                                                <col style="width:56px;">
                                                <col style="width:148px;">
                                                <col style="width:320px;">
                                                <col style="width:190px;">
                                                <col style="width:120px;">
                                                <col style="width:118px;">
                                                <col style="width:140px;">
                                                <col style="width:150px;">
                                                <col style="width:64px;">
                                            </colgroup>
                                            <thead>
                                                <tr>
                                                    <th style="width:56px;">STT</th>
                                                    <th style="width:148px;">Loại hàng</th>
                                                    <th style="width:320px;">Mặt hàng</th>
                                                    <th style="width:190px;">Danh mục</th>
                                                    <th style="width:120px;">Đơn vị</th>
                                                    <th class="text-end" style="width:118px;">Số lượng</th>
                                                    <th class="text-end" style="width:140px;">Đơn giá nhập</th>
                                                    <th class="text-end" style="width:150px;">Thành tiền</th>
                                                    <th class="text-end" style="width:78px;">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody id="stockItemsBody" data-next-index="<?php echo count($items); ?>">
                                                <?php foreach (array_values($items) as $rowNumber => $item): ?>
                                                    <?php echo $renderItemRow($rowNumber, $rowNumber + 1, (array) $item); ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                                    <div class="erp-inline-note">Schema hiện tại chưa có `note` riêng cho từng dòng nên ghi chú dòng chưa được lưu tách biệt.</div>
                                    <?php if ($canQuickCreateMaterial): ?>
                                        <button type="button" class="btn btn-light erp-btn" id="quickCreateMaterialFooterButton"><i class="bi bi-plus-lg"></i>Tạo nhanh vật tư</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <aside class="erp-stock-summary">
                            <div class="erp-card p-3 p-lg-4 erp-stock-summary-card">
                                <div class="erp-section-heading mb-3">
                                    <div>
                                        <div class="erp-section-heading__eyebrow">Tóm tắt realtime</div>
                                        <h3 class="erp-section-heading__title">Kiểm soát nhanh</h3>
                                    </div>
                                </div>
                                <div class="erp-summary-grid">
                                    <div class="erp-summary-kpi">
                                        <div class="erp-summary-kpi__label">Loại giao dịch</div>
                                        <div class="erp-summary-kpi__value" id="summaryTxnType"><?php echo htmlspecialchars($txnTypeLabels[$selectedTxnType] ?? 'Nhập kho', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="erp-summary-kpi">
                                        <div class="erp-summary-kpi__label">Số dòng</div>
                                        <div class="erp-summary-kpi__value" id="summaryLineCount">0</div>
                                    </div>
                                    <div class="erp-summary-kpi">
                                        <div class="erp-summary-kpi__label">Tổng số lượng</div>
                                        <div class="erp-summary-kpi__value" id="summaryQuantity">0.00</div>
                                    </div>
                                    <div class="erp-summary-kpi">
                                        <div class="erp-summary-kpi__label">Tổng tiền nhập</div>
                                        <div class="erp-summary-kpi__value" id="summaryAmount">0.00</div>
                                    </div>
                                    <div class="erp-summary-kpi">
                                        <div class="erp-summary-kpi__label">Cảnh báo</div>
                                        <div class="erp-summary-kpi__value" id="summaryInvalidCount">0 dòng lỗi</div>
                                        <div class="erp-summary-note mt-2" id="summaryWarningText">Tất cả dòng đang hợp lệ ở mức giao diện.</div>
                                    </div>
                                    <div class="erp-summary-note">`Lưu nháp` lưu cục bộ trên trình duyệt hiện tại.</div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
<template id="stockItemRowTemplate"><?php echo $renderItemRow('__INDEX__', 1, ['item_kind' => 'material', 'material_id' => '', 'component_id' => '', 'quantity' => '1.00', 'unit_cost' => '0.00'], true); ?></template>

<div class="modal fade" id="quickMaterialModal" tabindex="-1" aria-labelledby="quickMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="erp-section-heading__eyebrow">Quick-create vật tư</div>
                    <h5 class="modal-title fw-bold" id="quickMaterialModalLabel">Tạo nhanh nguyên vật liệu</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="erp-form-message mb-3" id="quickMaterialMessage"></div>
                <form id="quickMaterialForm">
                    <div class="erp-modal-grid">
                        <div style="grid-column: span 4;">
                            <label class="erp-stock-label" for="quickMaterialCode">Mã</label>
                            <input id="quickMaterialCode" type="text" name="code" class="form-control erp-field" maxlength="30" placeholder="VT-NHAP-001">
                        </div>
                        <div style="grid-column: span 8;">
                            <label class="erp-stock-label" for="quickMaterialName">Tên vật tư</label>
                            <input id="quickMaterialName" type="text" name="name" class="form-control erp-field" maxlength="190" placeholder="Tên vật tư cần nhập">
                        </div>
                        <div style="grid-column: span 4;">
                            <label class="erp-stock-label" for="quickMaterialCategory">Danh mục</label>
                            <select id="quickMaterialCategory" name="category_id" class="form-select erp-select">
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($materialCategoryOptions as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars((string) ($category['label'] ?? $category['name']), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="grid-column: span 3;">
                            <label class="erp-stock-label" for="quickMaterialUnit">Đơn vị</label>
                            <input id="quickMaterialUnit" type="text" name="unit" class="form-control erp-field" maxlength="50" placeholder="Kg / Tấm / Cây">
                        </div>
                        <div style="grid-column: span 3;">
                            <label class="erp-stock-label" for="quickMaterialCost">Giá chuẩn</label>
                            <input id="quickMaterialCost" type="number" step="0.01" min="0" name="standard_cost" class="form-control erp-field text-end" value="0.00">
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="erp-stock-label" for="quickMaterialColor">Màu sắc</label>
                            <input id="quickMaterialColor" type="text" name="color" class="form-control erp-field" maxlength="100" placeholder="Đỏ / Trắng">
                        </div>
                        <div style="grid-column: span 12;">
                            <label class="erp-stock-label" for="quickMaterialSpecification">Quy cách</label>
                            <input id="quickMaterialSpecification" type="text" name="specification" class="form-control erp-field" maxlength="255" placeholder="VD: Tấm Alu 3mm, khổ 1220x2440">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light erp-btn" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-dark erp-btn" id="quickMaterialSubmitButton">Lưu vật tư</button>
            </div>
        </div>
    </div>
</div>

<script id="stockItemPayloadData" type="application/json"><?php echo json_encode($itemPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script>
window.stockEntryDraftConfig = {
    draftKey: <?php echo json_encode($draftKey, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
    shouldRestoreDraft: <?php echo $hasExistingInput ? 'false' : 'true'; ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const form = document.getElementById('stockEntryForm');
    const body = document.getElementById('stockItemsBody');
    const template = document.getElementById('stockItemRowTemplate');
    const txnTypeField = document.getElementById('stockTxnType');
    const addItemButton = document.getElementById('addItemButton');
    const quickCreateMaterialFooterButton = document.getElementById('quickCreateMaterialFooterButton');
    const saveDraftButton = document.getElementById('saveDraftButton');
    const draftMessage = document.getElementById('stockDraftMessage');
    const payload = JSON.parse(document.getElementById('stockItemPayloadData')?.textContent || '{"materials":{},"components":{}}');
    const draftConfig = window.stockEntryDraftConfig || {};
    const draftKey = draftConfig.draftKey || 'iconvina.stock-import.draft';
    const quickMaterialModalElement = document.getElementById('quickMaterialModal');
    const quickMaterialModal = quickMaterialModalElement ? new bootstrap.Modal(quickMaterialModalElement) : null;
    const quickMaterialForm = document.getElementById('quickMaterialForm');
    const quickMaterialMessage = document.getElementById('quickMaterialMessage');
    const quickMaterialSubmitButton = document.getElementById('quickMaterialSubmitButton');
    let quickCreateTargetRow = null;
    let activeRow = null;

    const txnTypeLabels = { import: 'Nhập kho', export: 'Xuất kho', adjustment: 'Điều chỉnh' };
    const summary = {
        txnType: document.getElementById('summaryTxnType'),
        lineCount: document.getElementById('summaryLineCount'),
        quantity: document.getElementById('summaryQuantity'),
        amount: document.getElementById('summaryAmount'),
        invalidCount: document.getElementById('summaryInvalidCount'),
        warningText: document.getElementById('summaryWarningText')
    };
    const numberFormatter = new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const parseNumber = (value) => {
        const parsed = Number.parseFloat(String(value ?? '').replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : 0;
    };
    const formatNumber = (value) => numberFormatter.format(value);
    const showFormMessage = (element, type, message) => {
        if (!element) return;
        element.className = 'erp-form-message is-visible is-' + type;
        element.textContent = message;
    };
    const clearFormMessage = (element) => {
        if (!element) return;
        element.className = 'erp-form-message';
        element.textContent = '';
    };
    const getRows = () => Array.from(body.querySelectorAll('[data-item-row]'));
    const nextRowIndex = () => {
        const current = Number.parseInt(body.dataset.nextIndex || '0', 10);
        body.dataset.nextIndex = String(current + 1);
        return current;
    };
    const createRowElement = (index) => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        return wrapper.firstElementChild;
    };
    const updateRowNumbers = () => {
        getRows().forEach((row, index) => {
            const cell = row.querySelector('.item-row-number');
            if (cell) cell.textContent = String(index + 1);
        });
    };
    const findSelectedMeta = (row) => {
        const kind = row.querySelector('.item-kind')?.value || 'material';
        const materialId = row.querySelector('.item-material')?.value || '';
        const componentId = row.querySelector('.item-component')?.value || '';
        if (kind === 'material') return materialId !== '' ? payload.materials[materialId] || null : null;
        return componentId !== '' ? payload.components[componentId] || null : null;
    };
    const rebuildProductOptions = (row) => {
        const kind = row.querySelector('.item-kind')?.value || 'material';
        const productSelect = row.querySelector('.item-product-select');
        const materialInput = row.querySelector('.item-material');
        const componentInput = row.querySelector('.item-component');
        if (!(productSelect instanceof HTMLSelectElement)) return;

        const selectedValue = kind === 'material'
            ? (materialInput instanceof HTMLInputElement ? materialInput.value : '')
            : (componentInput instanceof HTMLInputElement ? componentInput.value : '');

        const options = kind === 'material' ? Object.values(payload.materials) : Object.values(payload.components);
        productSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = kind === 'material' ? 'Chọn vật tư' : 'Chọn bán thành phẩm';
        productSelect.appendChild(placeholder);

        options.forEach((optionData) => {
            const option = document.createElement('option');
            option.value = String(optionData.id);
            option.textContent = optionData.option_label || ((optionData.code || '') + ' - ' + (optionData.name || '')).trim();
            option.selected = String(optionData.id) === selectedValue;
            productSelect.appendChild(option);
        });
    };
    const syncRowState = (row, options = {}) => {
        const kind = row.querySelector('.item-kind')?.value || 'material';
        const materialInput = row.querySelector('.item-material');
        const componentInput = row.querySelector('.item-component');
        const productSelect = row.querySelector('.item-product-select');
        const categoryField = row.querySelector('.item-category');
        const unitField = row.querySelector('.item-unit');
        const unitCostField = row.querySelector('.item-unit-cost');
        const resetCost = options.resetCost === true;

        if (materialInput instanceof HTMLInputElement && kind !== 'material') {
            materialInput.value = '';
        }

        if (componentInput instanceof HTMLInputElement && kind !== 'component') {
            componentInput.value = '';
        }

        rebuildProductOptions(row);

        const selectedMeta = findSelectedMeta(row);
        const categoryText = kind === 'material' && selectedMeta
            ? ((selectedMeta.category_code ? selectedMeta.category_code + ' / ' : '') + (selectedMeta.category_name || '')).trim()
            : '';
        const unitText = selectedMeta?.unit || '';

        if (categoryField) {
            categoryField.textContent = categoryText !== '' ? categoryText : (kind === 'material' ? 'Chưa chọn vật tư' : 'Không áp dụng');
            categoryField.classList.toggle('is-empty', categoryText === '');
        }

        if (unitField) {
            unitField.textContent = unitText !== '' ? unitText : 'Tự động';
            unitField.classList.toggle('is-empty', unitText === '');
        }

        if (resetCost && unitCostField instanceof HTMLInputElement) {
            unitCostField.value = selectedMeta?.standard_cost || '0.00';
        }

        if (productSelect instanceof HTMLSelectElement) {
            const selectedValue = kind === 'material'
                ? (materialInput instanceof HTMLInputElement ? materialInput.value : '')
                : (componentInput instanceof HTMLInputElement ? componentInput.value : '');
            productSelect.value = selectedValue;
        }
    };
    const calculateRow = (row) => {
        const quantity = Math.max(parseNumber(row.querySelector('.item-quantity')?.value), 0);
        const unitCost = Math.max(parseNumber(row.querySelector('.item-unit-cost')?.value), 0);
        const total = quantity * unitCost;
        const totalField = row.querySelector('.item-line-total');
        if (totalField) totalField.textContent = formatNumber(total);
        return { quantity, unitCost, total };
    };
    const rowIsInvalid = (row) => {
        const kind = row.querySelector('.item-kind')?.value || 'material';
        const quantity = parseNumber(row.querySelector('.item-quantity')?.value);
        const unitCost = parseNumber(row.querySelector('.item-unit-cost')?.value);
        const materialId = row.querySelector('.item-material')?.value || '';
        const componentId = row.querySelector('.item-component')?.value || '';
        if (kind === 'material' && materialId === '') return true;
        if (kind === 'component' && componentId === '') return true;
        return quantity <= 0 || unitCost < 0;
    };
    const updateSummary = () => {
        let totalQuantity = 0;
        let totalAmount = 0;
        let invalidLines = 0;
        const rows = getRows();

        rows.forEach((row) => {
            const totals = calculateRow(row);
            totalQuantity += totals.quantity;
            totalAmount += totals.total;
            if (rowIsInvalid(row)) invalidLines += 1;
        });

        if (summary.txnType) summary.txnType.textContent = txnTypeLabels[txnTypeField?.value || 'import'] || 'Nhập kho';
        if (summary.lineCount) summary.lineCount.textContent = String(rows.length);
        if (summary.quantity) summary.quantity.textContent = formatNumber(totalQuantity);
        if (summary.amount) summary.amount.textContent = formatNumber(totalAmount);
        if (summary.invalidCount) {
            summary.invalidCount.textContent = invalidLines + ' dòng lỗi';
            summary.invalidCount.classList.toggle('is-danger', invalidLines > 0);
        }
        if (summary.warningText) {
            summary.warningText.textContent = invalidLines > 0
                ? 'Cần kiểm tra lại mặt hàng, số lượng hoặc đơn giá ở các dòng chưa hợp lệ.'
                : 'Tất cả dòng đang hợp lệ ở mức giao diện.';
        }

        updateRowNumbers();
    };
    const addRow = (index = null) => {
        const row = createRowElement(index === null ? nextRowIndex() : index);
        body.appendChild(row);
        syncRowState(row, { resetCost: false });
        updateSummary();
        return row;
    };
    const resolveQuickCreateTargetRow = () => {
        if (activeRow instanceof HTMLElement && body.contains(activeRow)) {
            const kindSelect = activeRow.querySelector('.item-kind');
            if (kindSelect instanceof HTMLSelectElement) kindSelect.value = 'material';
            syncRowState(activeRow, { resetCost: false });
            return activeRow;
        }

        const firstMaterialRow = getRows().find((row) => (row.querySelector('.item-kind')?.value || 'material') === 'material');
        if (firstMaterialRow instanceof HTMLElement) {
            return firstMaterialRow;
        }

        const newRow = addRow();
        const kindSelect = newRow.querySelector('.item-kind');
        if (kindSelect instanceof HTMLSelectElement) kindSelect.value = 'material';
        syncRowState(newRow, { resetCost: true });

        return newRow;
    };
    const ensureAtLeastOneRow = () => {
        if (getRows().length === 0) addRow(0);
    };
    const serializeDraft = () => {
        const params = new URLSearchParams();
        new FormData(form).forEach((value, key) => params.append(key, String(value)));
        return params.toString();
    };
    const collectDraftIndices = (params) => {
        const indices = new Set();
        for (const key of params.keys()) {
            const match = key.match(/^items\[(.+?)\]\[/);
            if (match) indices.add(match[1]);
        }
        return Array.from(indices).sort((left, right) => Number(left) - Number(right));
    };
    const restoreDraft = (draftString) => {
        const params = new URLSearchParams(draftString);
        const indices = collectDraftIndices(params);
        body.innerHTML = '';
        if (indices.length === 0) {
            addRow(0);
            body.dataset.nextIndex = '1';
        } else {
            indices.forEach((index) => addRow(index));
            body.dataset.nextIndex = String(Math.max(...indices.map((index) => Number(index))) + 1);
        }
        for (const [key, value] of params.entries()) {
            const field = form.elements.namedItem(key);
            if (!field) continue;
            if (field instanceof RadioNodeList) {
                Array.from(field).forEach((node) => {
                    if (node instanceof HTMLInputElement || node instanceof HTMLSelectElement || node instanceof HTMLTextAreaElement) node.value = value;
                });
            } else if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                field.value = value;
            }
        }
        getRows().forEach((row) => syncRowState(row, { resetCost: false }));
        updateSummary();
    };
    const appendMaterialOption = (material) => {
        payload.materials[String(material.id)] = material;
        body.querySelectorAll('.item-material').forEach((select) => {
            const option = document.createElement('option');
            option.value = String(material.id);
            option.textContent = material.option_label;
            select.appendChild(option);
        });
    };
    const resetQuickMaterialForm = () => {
        if (!quickMaterialForm) return;
        quickMaterialForm.reset();
        const costField = quickMaterialForm.querySelector('[name="standard_cost"]');
        if (costField instanceof HTMLInputElement) costField.value = '0.00';
        clearFormMessage(quickMaterialMessage);
    };

    addItemButton?.addEventListener('click', () => addRow());
    quickCreateMaterialFooterButton?.addEventListener('click', () => {
        quickCreateTargetRow = resolveQuickCreateTargetRow();
        activeRow = quickCreateTargetRow;
        resetQuickMaterialForm();
        quickMaterialModal?.show();
    });
    txnTypeField?.addEventListener('change', updateSummary);

    body.addEventListener('focusin', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const row = target.closest('[data-item-row]');
        if (row instanceof HTMLElement) activeRow = row;
    });

    body.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const row = target.closest('[data-item-row]');
        if (row instanceof HTMLElement) activeRow = row;

        const removeButton = target.closest('.remove-item-button');
        if (removeButton) {
            removeButton.closest('[data-item-row]')?.remove();
            ensureAtLeastOneRow();
            updateSummary();
            return;
        }
    });

    body.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const row = target.closest('[data-item-row]');
        if (!row) return;
        if (target.matches('.item-kind')) syncRowState(row, { resetCost: true });
        updateSummary();
    });

    body.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement) || !target.matches('.item-product-select')) return;
        const row = target.closest('[data-item-row]');
        if (!row) return;
        const kind = row.querySelector('.item-kind')?.value || 'material';
        const materialInput = row.querySelector('.item-material');
        const componentInput = row.querySelector('.item-component');
        if (kind === 'material' && materialInput instanceof HTMLInputElement) {
            materialInput.value = target.value;
            if (componentInput instanceof HTMLInputElement) componentInput.value = '';
        }
        if (kind === 'component' && componentInput instanceof HTMLInputElement) {
            componentInput.value = target.value;
            if (materialInput instanceof HTMLInputElement) materialInput.value = '';
        }
        syncRowState(row, { resetCost: true });
        updateSummary();
    });

    body.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.matches('.item-quantity, .item-unit-cost')) updateSummary();
    });

    saveDraftButton?.addEventListener('click', () => {
        window.localStorage.setItem(draftKey, serializeDraft());
        showFormMessage(draftMessage, 'success', 'Đã lưu nháp phiếu nhập kho trên trình duyệt này.');
    });

    form.addEventListener('submit', () => {
        window.localStorage.removeItem(draftKey);
    });

    if (draftConfig.shouldRestoreDraft) {
        const existingDraft = window.localStorage.getItem(draftKey);
        if (existingDraft) {
            restoreDraft(existingDraft);
            showFormMessage(draftMessage, 'warning', 'Đã khôi phục bản nháp nhập kho gần nhất từ trình duyệt.');
        }
    }

    quickMaterialSubmitButton?.addEventListener('click', async () => {
        if (!quickMaterialForm) return;
        clearFormMessage(quickMaterialMessage);
        quickMaterialSubmitButton.disabled = true;
        quickMaterialSubmitButton.textContent = 'Đang lưu...';

        try {
            const response = await fetch(<?php echo json_encode(app_url('/api/materials/quick-create'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>, {
                method: 'POST',
                body: new FormData(quickMaterialForm)
            });
            const responsePayload = await response.json();
            if (!response.ok) {
                const firstError = responsePayload?.errors ? Object.values(responsePayload.errors).flat()[0] : null;
                throw new Error(firstError || responsePayload?.message || 'Không thể tạo nhanh vật tư.');
            }
            const material = responsePayload.data;
            appendMaterialOption(material);
            if (quickCreateTargetRow) {
                const materialInput = quickCreateTargetRow.querySelector('.item-material');
                if (materialInput instanceof HTMLInputElement) materialInput.value = String(material.id);
                const kindSelect = quickCreateTargetRow.querySelector('.item-kind');
                if (kindSelect instanceof HTMLSelectElement) kindSelect.value = 'material';
                syncRowState(quickCreateTargetRow, { resetCost: true });
                updateSummary();
            }
            resetQuickMaterialForm();
            quickMaterialModal?.hide();
            showFormMessage(draftMessage, 'success', 'Đã tạo nhanh vật tư mới và gán vào dòng hiện tại.');
        } catch (error) {
            showFormMessage(quickMaterialMessage, 'warning', error instanceof Error ? error.message : 'Không thể tạo nhanh vật tư.');
        } finally {
            quickMaterialSubmitButton.disabled = false;
            quickMaterialSubmitButton.textContent = 'Lưu vật tư';
        }
    });

    quickMaterialModalElement?.addEventListener('hidden.bs.modal', resetQuickMaterialForm);

    getRows().forEach((row) => syncRowState(row, { resetCost: false }));
    ensureAtLeastOneRow();
    updateSummary();
})();
</script>
</body>
</html>
