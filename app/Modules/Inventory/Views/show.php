<?php
$activeSidebar = $activeSidebar ?? 'inventory';
$pageTitle = $pageTitle ?? 'Stock Transaction Detail';
$pageEyebrow = $pageEyebrow ?? 'Inventory transaction profile';
$transaction = $transaction ?? [];
$status = $status ?? '';
$canUpdate = has_permission('stock.update');

$txnTypeBadgeMap = [
    'import' => 'success',
    'receipt' => 'success',
    'export' => 'danger',
    'issue' => 'danger',
    'adjustment' => 'warning',
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
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="py-4 py-xl-5">
            <div class="container-fluid px-4 px-xl-5">
                <div class="erp-card p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Inventory transaction profile</div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h3 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) $transaction['txn_no'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <span class="badge text-bg-<?php echo $txnTypeBadgeMap[$transaction['txn_type']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(ucfirst((string) $transaction['txn_type']), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="text-secondary">Transaction Date: <?php echo htmlspecialchars((string) $transaction['txn_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                            <?php if ($canUpdate): ?><a href="<?php echo htmlspecialchars(app_url('/stocks/edit?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Edit</a><?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Transaction info</div>
                                <dl class="row mb-0">
                                    <dt class="col-sm-3">Reference</dt><dd class="col-sm-9"><?php echo htmlspecialchars((string) (($transaction['ref_type'] ?? '') !== null && ($transaction['ref_type'] ?? '') !== '' ? ((string) $transaction['ref_type'] . ' #' . (string) ($transaction['ref_id'] ?? '')) : '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-3">Item Count</dt><dd class="col-sm-9"><?php echo (int) ($transaction['item_count'] ?? 0); ?></dd>
                                    <dt class="col-sm-3">Note</dt><dd class="col-sm-9"><?php echo nl2br(htmlspecialchars((string) ($transaction['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Financial summary</div>
                                <dl class="row mb-0">
                                    <dt class="col-6">Total Amount</dt><dd class="col-6 text-end fw-semibold"><?php echo number_format((float) ($transaction['total_amount'] ?? 0), 2); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="erp-card p-4">
                        <div class="small text-uppercase text-secondary fw-semibold mb-3">Transaction items</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Kind</th>
                                        <th>Item</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (($transaction['items'] ?? []) === []): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-5">No stock items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transaction['items'] as $index => $item): ?>
                                        <?php $itemLabel = (string) ($item['item_kind'] === 'material' ? (($item['material_code'] ?? '') . ' - ' . ($item['material_name'] ?? '')) : (($item['component_code'] ?? '') . ' - ' . ($item['component_name'] ?? ''))); ?>
                                        <tr>
                                            <td><?php echo (int) $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst((string) $item['item_kind']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($itemLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end"><?php echo number_format((float) $item['quantity'], 2); ?></td>
                                            <td class="text-end"><?php echo number_format((float) $item['unit_cost'], 2); ?></td>
                                            <td class="text-end fw-semibold"><?php echo number_format((float) $item['line_total'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
