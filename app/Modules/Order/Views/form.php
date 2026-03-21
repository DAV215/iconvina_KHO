<?php
$activeSidebar = $activeSidebar ?? 'orders';
$pageTitle = $pageTitle ?? 'Order Form';
$pageEyebrow = $pageEyebrow ?? 'Sales order management';
$formAction = $formAction ?? '/orders/store';
$order = $order ?? [];
$customers = $customers ?? [];
$quotations = $quotations ?? [];
$quotationPayload = $quotationPayload ?? [];
$statuses = $statuses ?? [];
$priorities = $priorities ?? [];
$errors = $errors ?? [];
$items = $order['items'] ?? [];

if ($items === []) {
    $items = [[
        'description' => '',
        'quantity' => '1.00',
        'unit_price' => '0.00',
        'total_amount' => '0.00',
    ]];
}

$field = static function (string $key, string $default = '') use ($order): string {
    return htmlspecialchars((string) ($order[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$selectedCustomerId = (string) ($order['customer_id'] ?? '');
$selectedQuotationId = (string) ($order['quotation_id'] ?? '');
$discountAmountValue = (string) ($order['discount_amount'] ?? '0.00');
$taxAmountValue = (string) ($order['tax_amount'] ?? '0.00');

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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Order module</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                    </div>

                    <?php if ($errorFor('items')): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo htmlspecialchars($errorFor('items'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="orderForm" class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Code</label>
                                        <input type="text" name="code" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30">
                                        <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Quotation</label>
                                        <select name="quotation_id" id="quotationSelect" class="form-select form-select-lg rounded-4 <?php echo $errorFor('quotation_id') ? 'is-invalid' : ''; ?>">
                                            <option value="">No quotation</option>
                                            <?php foreach ($quotations as $quotation): ?>
                                                <option value="<?php echo (int) $quotation['id']; ?>" <?php echo $selectedQuotationId === (string) $quotation['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $quotation['code'] . ' - ' . (string) $quotation['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('quotation_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('quotation_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Customer</label>
                                        <select name="customer_id" id="customerSelect" class="form-select form-select-lg rounded-4 <?php echo $errorFor('customer_id') ? 'is-invalid' : ''; ?>">
                                            <option value="">Select customer</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo (int) $customer['id']; ?>" <?php echo $selectedCustomerId === (string) $customer['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $customer['code'] . ' - ' . (string) $customer['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('customer_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('customer_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Order Date</label>
                                        <input type="date" name="order_date" class="form-control rounded-4 <?php echo $errorFor('order_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('order_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('order_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('order_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Due Date</label>
                                        <input type="date" name="due_date" class="form-control rounded-4 <?php echo $errorFor('due_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('due_date'); ?>">
                                        <?php if ($errorFor('due_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('due_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select rounded-4 <?php echo $errorFor('status') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($statuses as $statusOption): ?>
                                                <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($order['status'] ?? 'draft') === $statusOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $statusOption)), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('status')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('status'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <label class="form-label fw-semibold">Priority</label>
                                        <select name="priority" class="form-select rounded-4 <?php echo $errorFor('priority') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($priorities as $priorityOption): ?>
                                                <option value="<?php echo htmlspecialchars($priorityOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($order['priority'] ?? 'normal') === $priorityOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucfirst($priorityOption), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('priority')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('priority'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
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
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Financial summary</div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Subtotal</label>
                                    <input type="text" class="form-control rounded-4 bg-light" id="subtotalPreview" value="0.00" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Discount Amount</label>
                                    <input type="number" step="0.01" min="0" name="discount_amount" id="discountAmountInput" class="form-control rounded-4 <?php echo $errorFor('discount_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($discountAmountValue, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if ($errorFor('discount_amount')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('discount_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tax Amount</label>
                                    <input type="number" step="0.01" min="0" name="tax_amount" id="taxAmountInput" class="form-control rounded-4 <?php echo $errorFor('tax_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($taxAmountValue, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php if ($errorFor('tax_amount')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('tax_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                </div>
                                <div>
                                    <label class="form-label fw-semibold">Total Amount</label>
                                    <input type="text" class="form-control rounded-4 bg-light fw-semibold" id="totalPreview" value="0.00" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                                    <div>
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">Order items</div>
                                        <h4 class="h5 mb-0 fw-semibold">Line Items</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="addItemButton"><i class="bi bi-plus-lg me-2"></i>Add item</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 72px;">#</th>
                                                <th style="min-width: 320px;">Description</th>
                                                <th style="min-width: 150px;">Qty</th>
                                                <th style="min-width: 180px;">Unit Price</th>
                                                <th style="min-width: 180px;">Line Total</th>
                                                <th style="width: 90px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="orderItemsBody" data-next-index="<?php echo (int) $nextIndex; ?>">
                                        <?php foreach ($items as $index => $item): ?>
                                            <tr data-item-row>
                                                <td class="text-secondary fw-semibold item-row-number"><?php echo (int) $index + 1; ?></td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][description]" class="form-control rounded-4 <?php echo $itemErrorFor($index, 'description') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255">
                                                    <?php if ($itemErrorFor($index, 'description')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][quantity]" class="form-control rounded-4 item-quantity <?php echo $itemErrorFor($index, 'quantity') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'quantity')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][unit_price]" class="form-control rounded-4 item-unit-price <?php echo $itemErrorFor($index, 'unit_price') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit_price'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'unit_price')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'unit_price'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control rounded-4 bg-light item-total" value="<?php echo htmlspecialchars(number_format((float) ($item['total_amount'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
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
                            <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Cancel</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Save order</button>
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
        <td><input type="text" name="items[__INDEX__][description]" class="form-control rounded-4" maxlength="255"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][quantity]" class="form-control rounded-4 item-quantity" value="1.00"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" class="form-control rounded-4 item-unit-price" value="0.00"></td>
        <td><input type="text" class="form-control rounded-4 bg-light item-total" value="0.00" readonly></td>
        <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

<script id="quotationPayloadData" type="application/json"><?php echo json_encode($quotationPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const body = document.getElementById('orderItemsBody');
    const addButton = document.getElementById('addItemButton');
    const template = document.getElementById('orderItemRowTemplate');
    const quotationSelect = document.getElementById('quotationSelect');
    const customerSelect = document.getElementById('customerSelect');
    const discountAmountInput = document.getElementById('discountAmountInput');
    const taxAmountInput = document.getElementById('taxAmountInput');
    const subtotalPreview = document.getElementById('subtotalPreview');
    const totalPreview = document.getElementById('totalPreview');
    const quotationPayload = JSON.parse(document.getElementById('quotationPayloadData')?.textContent || '{}');

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

    const calculateTotals = () => {
        let subtotal = 0;

        Array.from(body.querySelectorAll('[data-item-row]')).forEach((row) => {
            const quantity = Math.max(parseNumber(row.querySelector('.item-quantity')?.value), 0);
            const unitPrice = Math.max(parseNumber(row.querySelector('.item-unit-price')?.value), 0);
            const lineTotal = quantity * unitPrice;
            subtotal += lineTotal;

            const lineTotalField = row.querySelector('.item-total');
            if (lineTotalField) {
                lineTotalField.value = formatNumber(lineTotal);
            }
        });

        const discountAmount = Math.max(parseNumber(discountAmountInput?.value), 0);
        const taxAmount = Math.max(parseNumber(taxAmountInput?.value), 0);
        subtotalPreview.value = formatNumber(subtotal);
        totalPreview.value = formatNumber(subtotal - discountAmount + taxAmount);
        updateRowNumbers();
    };

    const addRow = () => {
        const index = Number.parseInt(body.dataset.nextIndex || '0', 10);
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        body.insertAdjacentHTML('beforeend', html);
        body.dataset.nextIndex = String(index + 1);
        calculateTotals();
    };

    const replaceRows = (items) => {
        body.innerHTML = '';

        const safeItems = Array.isArray(items) && items.length > 0 ? items : [{ description: '', quantity: '1.00', unit_price: '0.00' }];
        let nextIndex = 0;

        safeItems.forEach((item) => {
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            body.insertAdjacentHTML('beforeend', html);
            const row = body.lastElementChild;
            if (row) {
                const descriptionInput = row.querySelector('input[name$="[description]"]');
                const quantityInput = row.querySelector('input[name$="[quantity]"]');
                const unitPriceInput = row.querySelector('input[name$="[unit_price]"]');
                if (descriptionInput) descriptionInput.value = item.description || '';
                if (quantityInput) quantityInput.value = item.quantity || '1.00';
                if (unitPriceInput) unitPriceInput.value = item.unit_price || '0.00';
            }
            nextIndex += 1;
        });

        body.dataset.nextIndex = String(nextIndex);
        calculateTotals();
    };

    const loadQuotation = (quotationId) => {
        if (!quotationId || !quotationPayload[quotationId]) {
            return;
        }

        const quotation = quotationPayload[quotationId];
        customerSelect.value = String(quotation.customer_id || '');
        discountAmountInput.value = quotation.discount_amount || '0.00';
        taxAmountInput.value = quotation.tax_amount || '0.00';
        replaceRows(quotation.items || []);
    };

    addButton?.addEventListener('click', addRow);

    quotationSelect?.addEventListener('change', () => {
        loadQuotation(quotationSelect.value);
    });

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

    document.getElementById('orderForm')?.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('.item-quantity, .item-unit-price, #discountAmountInput, #taxAmountInput')) {
            calculateTotals();
        }
    });

    calculateTotals();
})();
</script>
</body>
</html>