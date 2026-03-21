<?php
$activeSidebar = $activeSidebar ?? 'quotations';
$pageTitle = $pageTitle ?? 'Quotation Form';
$pageEyebrow = $pageEyebrow ?? 'Quotation management';
$formAction = $formAction ?? '/quotations/store';
$quotation = $quotation ?? [];
$customers = $customers ?? [];
$statuses = $statuses ?? [];
$errors = $errors ?? [];
$items = $quotation['items'] ?? [];

if ($items === []) {
    $items = [[
        'item_type' => '',
        'description' => '',
        'unit' => '',
        'quantity' => '1.00',
        'unit_price' => '0.00',
        'discount_amount' => '0.00',
        'total_amount' => '0.00',
    ]];
}

$field = static function (string $key, string $default = '') use ($quotation): string {
    return htmlspecialchars((string) ($quotation[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$selectedCustomerId = (string) ($quotation['customer_id'] ?? '');
$taxAmountValue = (string) ($quotation['tax_amount'] ?? '0.00');

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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Quotation module</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                    </div>

                    <?php if ($errorFor('items')): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo htmlspecialchars($errorFor('items'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="quotationForm" class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Code</label>
                                        <input type="text" name="code" class="form-control form-control-lg rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30">
                                        <?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-8">
                                        <label class="form-label fw-semibold">Customer</label>
                                        <select name="customer_id" class="form-select form-select-lg rounded-4 <?php echo $errorFor('customer_id') ? 'is-invalid' : ''; ?>">
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
                                        <label class="form-label fw-semibold">Quote Date</label>
                                        <input type="date" name="quote_date" class="form-control rounded-4 <?php echo $errorFor('quote_date') ? 'is-invalid' : ''; ?>" value="<?php echo $field('quote_date', date('Y-m-d')); ?>">
                                        <?php if ($errorFor('quote_date')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('quote_date'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Expired At</label>
                                        <input type="date" name="expired_at" class="form-control rounded-4 <?php echo $errorFor('expired_at') ? 'is-invalid' : ''; ?>" value="<?php echo $field('expired_at'); ?>">
                                        <?php if ($errorFor('expired_at')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('expired_at'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select rounded-4 <?php echo $errorFor('status') ? 'is-invalid' : ''; ?>">
                                            <?php foreach ($statuses as $statusOption): ?>
                                                <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($quotation['status'] ?? 'draft') === $statusOption ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucfirst($statusOption), ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('status')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('status'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
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
                                    <input type="text" class="form-control rounded-4 bg-light" id="discountPreview" value="0.00" readonly>
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
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">Quotation items</div>
                                        <h4 class="h5 mb-0 fw-semibold">Line Items</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" id="addItemButton"><i class="bi bi-plus-lg me-2"></i>Add item</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="quotationItemsTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 72px;">#</th>
                                                <th style="min-width: 150px;">Item Type</th>
                                                <th style="min-width: 260px;">Description</th>
                                                <th style="min-width: 120px;">Unit</th>
                                                <th style="min-width: 140px;">Qty</th>
                                                <th style="min-width: 160px;">Unit Price</th>
                                                <th style="min-width: 170px;">Discount</th>
                                                <th style="min-width: 160px;">Line Total</th>
                                                <th style="width: 90px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="quotationItemsBody" data-next-index="<?php echo (int) $nextIndex; ?>">
                                        <?php foreach ($items as $index => $item): ?>
                                            <tr data-item-row>
                                                <td class="text-secondary fw-semibold item-row-number"><?php echo (int) ($item['line_no'] ?? 0) ?: 1; ?></td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][item_type]" class="form-control rounded-4 <?php echo $itemErrorFor($index, 'item_type') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['item_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" placeholder="product, service...">
                                                    <?php if ($itemErrorFor($index, 'item_type')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'item_type'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][description]" class="form-control rounded-4 <?php echo $itemErrorFor($index, 'description') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255">
                                                    <?php if ($itemErrorFor($index, 'description')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'description'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][unit]" class="form-control rounded-4 <?php echo $itemErrorFor($index, 'unit') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['unit'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
                                                    <?php if ($itemErrorFor($index, 'unit')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'unit'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
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
                                                    <input type="number" step="0.01" min="0" name="items[<?php echo (int) $index; ?>][discount_amount]" class="form-control rounded-4 item-discount <?php echo $itemErrorFor($index, 'discount_amount') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['discount_amount'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'discount_amount')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'discount_amount'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
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
                            <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Cancel</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Save quotation</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<template id="quotationItemRowTemplate">
    <tr data-item-row>
        <td class="text-secondary fw-semibold item-row-number">1</td>
        <td><input type="text" name="items[__INDEX__][item_type]" class="form-control rounded-4" maxlength="50" placeholder="product, service..."></td>
        <td><input type="text" name="items[__INDEX__][description]" class="form-control rounded-4" maxlength="255"></td>
        <td><input type="text" name="items[__INDEX__][unit]" class="form-control rounded-4" maxlength="50"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][quantity]" class="form-control rounded-4 item-quantity" value="1.00"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" class="form-control rounded-4 item-unit-price" value="0.00"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][discount_amount]" class="form-control rounded-4 item-discount" value="0.00"></td>
        <td><input type="text" class="form-control rounded-4 bg-light item-total" value="0.00" readonly></td>
        <td class="text-end"><button type="button" class="btn btn-light rounded-3 remove-item-button"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const body = document.getElementById('quotationItemsBody');
    const addButton = document.getElementById('addItemButton');
    const template = document.getElementById('quotationItemRowTemplate');
    const subtotalPreview = document.getElementById('subtotalPreview');
    const discountPreview = document.getElementById('discountPreview');
    const totalPreview = document.getElementById('totalPreview');
    const taxAmountInput = document.getElementById('taxAmountInput');

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
        let discountAmount = 0;

        Array.from(body.querySelectorAll('[data-item-row]')).forEach((row) => {
            const quantity = Math.max(parseNumber(row.querySelector('.item-quantity')?.value), 0);
            const unitPrice = Math.max(parseNumber(row.querySelector('.item-unit-price')?.value), 0);
            const discountInput = Math.max(parseNumber(row.querySelector('.item-discount')?.value), 0);
            const lineGross = quantity * unitPrice;
            const lineDiscount = Math.min(discountInput, lineGross);
            const lineTotal = Math.max(lineGross - lineDiscount, 0);

            subtotal += lineGross;
            discountAmount += lineDiscount;

            const lineTotalField = row.querySelector('.item-total');
            if (lineTotalField) {
                lineTotalField.value = formatNumber(lineTotal);
            }
        });

        const taxAmount = Math.max(parseNumber(taxAmountInput?.value), 0);
        subtotalPreview.value = formatNumber(subtotal);
        discountPreview.value = formatNumber(discountAmount);
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

    document.getElementById('quotationForm')?.addEventListener('input', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.matches('.item-quantity, .item-unit-price, .item-discount, #taxAmountInput')) {
            calculateTotals();
        }
    });

    calculateTotals();
})();
</script>
</body>
</html>