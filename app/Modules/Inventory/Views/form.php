<?php
$activeSidebar = $activeSidebar ?? 'inventory';
$pageTitle = $pageTitle ?? 'Stock Transaction Form';
$pageEyebrow = $pageEyebrow ?? 'Inventory management';
$formAction = $formAction ?? '/stocks/store';
$transaction = $transaction ?? [];
$txnTypes = $txnTypes ?? [];
$materials = $materials ?? [];
$components = $components ?? [];
$itemPayload = $itemPayload ?? ['materials' => [], 'components' => []];
$errors = $errors ?? [];
$items = $transaction['items'] ?? [];

if ($items === []) {
    $items = [[
        'item_kind' => 'material',
        'material_id' => '',
        'component_id' => '',
        'quantity' => '1.00',
        'unit_cost' => '0.00',
        'line_total' => '0.00',
    ]];
}

$field = static function (string $key, string $default = '') use ($transaction): string {
    return htmlspecialchars((string) ($transaction[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};

$itemErrorFor = static function (int|string $index, string $key) use ($errors): ?string {
    return $errors["items.{$index}.{$key}"][0] ?? null;
};

$initialIndices = array_keys($items);
$nextIndex = $initialIndices === [] ? 1 : ((int) max($initialIndices)) + 1;
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
        <section class="py-4 py-xl-5">
            <div class="container-fluid px-4 px-xl-5">
                <div class="erp-card p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Inventory module</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                    </div>

                    <?php if ($errorFor('items')): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo htmlspecialchars($errorFor('items'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="stockForm" class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Transaction No</label>
                                        <input type="text" name="txn_no" class="form-control form-control-lg rounded-4 <?php echo $errorFor('txn_no') ? 'is-invalid' : ''; ?>" value="<?php echo $field('txn_no'); ?>" maxlength="30">
                                        <?php if ($errorFor('txn_no')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('txn_no'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Transaction Type</label>
                                        <select name="txn_type" class="form-select form-select-lg rounded-4 <?php echo $errorFor('txn_type') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($txnTypes as $txnTypeOption): ?>
                                                <option value="<?php echo htmlspecialchars($txnTypeOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($transaction['txn_type'] ?? 'import') === $txnTypeOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucfirst($txnTypeOption), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('txn_type')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('txn_type'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Transaction Date</label>
                                        <input type="date" name="txn_date" class="form-control form-control-lg rounded-4 <?php echo $errorFor('txn_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('txn_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('txn_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('txn_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label fw-semibold">Reference Type</label>
                                        <input type="text" name="ref_type" class="form-control rounded-4 <?php echo $errorFor('ref_type') ? 'is-invalid' : ''; ?>" value="<?php echo $field('ref_type'); ?>" maxlength="30" placeholder="order, production, manual...">
                                        <?php if ($errorFor('ref_type')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('ref_type'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label fw-semibold">Reference ID</label>
                                        <input type="number" min="0" name="ref_id" class="form-control rounded-4 <?php echo $errorFor('ref_id') ? 'is-invalid' : ''; ?>" value="<?php echo $field('ref_id'); ?>">
                                        <?php if ($errorFor('ref_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('ref_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Note</label>
                                        <textarea name="note" rows="4" class="form-control rounded-4 <?php echo $errorFor('note') ? 'is-invalid' : ''; ?>"><?php echo $field('note'); ?></textarea>
                                        <?php if ($errorFor('note')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('note'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Summary</div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Item Count</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="itemCountPreview" value="0" readonly>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Total Amount</label>
                                    <input type="text" class="form-control rounded-4 bg-light fw-semibold" id="totalAmountPreview" value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                                    <div>
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">Transaction items</div>
                                        <h4 class="h5 mb-0 fw-semibold">Line Items</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="addItemButton"><i class="bi bi-plus-lg me-2"></i>Add item</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 72px;">#</th>
                                                <th style="min-width: 150px;">Item Kind</th>
                                                <th style="min-width: 260px;">Material</th>
                                                <th style="min-width: 260px;">Component</th>
                                                <th style="min-width: 150px;">Quantity</th>
                                                <th style="min-width: 170px;">Unit Cost</th>
                                                <th style="min-width: 170px;">Line Total</th>
                                                <th style="width: 90px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="stockItemsBody" data-next-index="<?php echo (int) $nextIndex; ?>">
                                        <?php foreach ($items as $index => $item): ?>
                                            <tr data-item-row>
                                                <td class="text-secondary fw-semibold item-row-number"><?php echo (int) $index + 1; ?></td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][item_kind]" class="form-select rounded-4 item-kind <?php echo $itemErrorFor($index, 'item_kind') ? 'is-invalid' : ''; ?>">
                                                        <option value="material" <?php echo (string) ($item['item_kind'] ?? 'material') === 'material' ? 'selected' : ''; ?>>Material</option>
                                                        <option value="component" <?php echo (string) ($item['item_kind'] ?? '') === 'component' ? 'selected' : ''; ?>>Component</option>
                                                    </select>
                                                    <?php if ($itemErrorFor($index, 'item_kind')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'item_kind'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][material_id]" class="form-select rounded-4 item-material <?php echo $itemErrorFor($index, 'material_id') ? 'is-invalid' : ''; ?>">
                                                        <option value="">Select material</option>
                                                        <?php foreach ($materials as $material): ?>
                                                            <option value="<?php echo (int) $material['id']; ?>" <?php echo (string) ($item['material_id'] ?? '') === (string) $material['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars((string) $material['code'] . ' - ' . (string) $material['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?php if ($itemErrorFor($index, 'material_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'material_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][component_id]" class="form-select rounded-4 item-component <?php echo $itemErrorFor($index, 'component_id') ? 'is-invalid' : ''; ?>">
                                                        <option value="">Select component</option>
                                                        <?php foreach ($components as $component): ?>
                                                            <option value="<?php echo (int) $component['id']; ?>" <?php echo (string) ($item['component_id'] ?? '') === (string) $component['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?php if ($itemErrorFor($index, 'component_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'component_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][quantity]" class="form-control rounded-4 item-quantity <?php echo $itemErrorFor($index, 'quantity') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'quantity')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][unit_cost]" class="form-control rounded-4 item-unit-cost <?php echo $itemErrorFor($index, 'unit_cost') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit_cost'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'unit_cost')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'unit_cost'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control rounded-4 bg-light item-total" value="<?php echo htmlspecialchars(number_format((float) ($item['line_total'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Cancel</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Save transaction</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<template id="stockItemRowTemplate">
    <tr data-item-row>
        <td class="text-secondary fw-semibold item-row-number">1</td>
        <td>
            <select name="items[__INDEX__][item_kind]" class="form-select rounded-4 item-kind">
                <option value="material" selected>Material</option>
                <option value="component">Component</option>
            </select>
        </td>
        <td>
            <select name="items[__INDEX__][material_id]" class="form-select rounded-4 item-material">
                <option value="">Select material</option>
                <?php foreach ($materials as $material): ?>
                    <option value="<?php echo (int) $material['id']; ?>"><?php echo htmlspecialchars((string) $material['code'] . ' - ' . (string) $material['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="items[__INDEX__][component_id]" class="form-select rounded-4 item-component">
                <option value="">Select component</option>
                <?php foreach ($components as $component): ?>
                    <option value="<?php echo (int) $component['id']; ?>"><?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][quantity]" class="form-control rounded-4 item-quantity" value="1.00"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_cost]" class="form-control rounded-4 item-unit-cost" value="0.00"></td>
        <td><input type="text" class="form-control rounded-4 bg-light item-total" value="0.00" readonly></td>
        <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

<script id="stockItemPayloadData" type="application/json"><?php echo json_encode($itemPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const body = document.getElementById('stockItemsBody');
    const addButton = document.getElementById('addItemButton');
    const template = document.getElementById('stockItemRowTemplate');
    const itemCountPreview = document.getElementById('itemCountPreview');
    const totalAmountPreview = document.getElementById('totalAmountPreview');
    const payload = JSON.parse(document.getElementById('stockItemPayloadData')?.textContent || '{"materials":{},"components":{}}');

    const parseNumber = (value) => {
        const parsed = Number.parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatNumber = (value) => value.toFixed(2);

    const updateRowNumbers = () => {
        Array.from(body.querySelectorAll('[data-item-row]')).forEach((row, index) => {
            const cell = row.querySelector('.item-row-number');
            if (cell) {
                cell.textContent = String(index + 1);
            }
        });
    };

    const syncKind = (row, useDefaultCost = false) => {
        const kind = row.querySelector('.item-kind')?.value || 'material';
        const materialSelect = row.querySelector('.item-material');
        const componentSelect = row.querySelector('.item-component');
        const unitCostInput = row.querySelector('.item-unit-cost');

        if (materialSelect) {
            materialSelect.disabled = kind !== 'material';
            if (kind !== 'material') {
                materialSelect.value = '';
            }
        }

        if (componentSelect) {
            componentSelect.disabled = kind !== 'component';
            if (kind !== 'component') {
                componentSelect.value = '';
            }
        }

        if (!useDefaultCost || !unitCostInput) {
            return;
        }

        if (kind === 'material' && materialSelect && materialSelect.value && payload.materials[materialSelect.value]) {
            unitCostInput.value = payload.materials[materialSelect.value].standard_cost || '0.00';
        }

        if (kind === 'component' && componentSelect && componentSelect.value && payload.components[componentSelect.value]) {
            unitCostInput.value = payload.components[componentSelect.value].standard_cost || '0.00';
        }
    };

    const calculateTotals = () => {
        let totalAmount = 0;
        let itemCount = 0;

        Array.from(body.querySelectorAll('[data-item-row]')).forEach((row) => {
            const quantity = Math.max(parseNumber(row.querySelector('.item-quantity')?.value), 0);
            const unitCost = Math.max(parseNumber(row.querySelector('.item-unit-cost')?.value), 0);
            const lineTotal = quantity * unitCost;
            totalAmount += lineTotal;
            itemCount += 1;

            const lineTotalField = row.querySelector('.item-total');
            if (lineTotalField) {
                lineTotalField.value = formatNumber(lineTotal);
            }
        });

        itemCountPreview.value = String(itemCount);
        totalAmountPreview.value = formatNumber(totalAmount);
        updateRowNumbers();
    };

    const addRow = () => {
        const index = Number.parseInt(body.dataset.nextIndex || '0', 10);
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        body.insertAdjacentHTML('beforeend', html);
        body.dataset.nextIndex = String(index + 1);
        const row = body.lastElementChild;
        if (row) {
            syncKind(row, false);
        }
        calculateTotals();
    };

    addButton?.addEventListener('click', addRow);

    body?.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const removeButton = target.closest('.remove-item-button');
        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('[data-item-row]');
        if (row) {
            row.remove();
        }

        if (body.querySelectorAll('[data-item-row]').length === 0) {
            addRow();
            return;
        }

        calculateTotals();
    });

    body?.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const row = target.closest('[data-item-row]');
        if (!row) {
            return;
        }

        if (target.matches('.item-kind')) {
            syncKind(row, true);
        }

        if (target.matches('.item-material')) {
            syncKind(row, true);
        }

        if (target.matches('.item-component')) {
            syncKind(row, true);
        }

        calculateTotals();
    });

    document.getElementById('stockForm')?.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('.item-quantity, .item-unit-cost')) {
            calculateTotals();
        }
    });

    Array.from(body.querySelectorAll('[data-item-row]')).forEach((row) => syncKind(row, false));
    calculateTotals();
})();
</script>
</body>
</html>