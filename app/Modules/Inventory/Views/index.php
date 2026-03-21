<?php
$activeSidebar = $activeSidebar ?? 'inventory';
$pageTitle = $pageTitle ?? 'Stock Transactions';
$pageEyebrow = $pageEyebrow ?? 'Inventory management';
$search = $search ?? '';
$txnType = $txnType ?? '';
$txnTypes = $txnTypes ?? [];
$transactions = $transactions ?? [];

$txnTypeBadgeMap = [
    'import' => 'success',
    'export' => 'danger',
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
                <div class="erp-card p-4 p-xl-5 mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Inventory module</div>
                            <h3 class="h4 mb-0 fw-semibold">Stock Transaction Register</h3>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/stocks/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4"><i class="bi bi-plus-lg me-2"></i>New transaction</a>
                        </div>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 mb-4">
                        <div class="col-12 col-lg-5">
                            <div class="input-group shadow-sm rounded-4 overflow-hidden border bg-white">
                                <span class="input-group-text bg-white border-0 text-secondary"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-0 shadow-none" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search txn no or date">
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <select name="txn_type" class="form-select rounded-4 shadow-sm">
                                <option value="">All types</option>
                                <?php foreach ($txnTypes as $txnTypeOption): ?>
                                    <option value="<?php echo htmlspecialchars($txnTypeOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $txnType === $txnTypeOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($txnTypeOption), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <button type="submit" class="btn btn-outline-secondary rounded-4 px-4">Search</button>
                            <a href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4 ms-2">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Txn No</th>
                                    <th>Type</th>
                                    <th>Txn Date</th>
                                    <th>Reference</th>
                                    <th class="text-end">Items</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($transactions === []): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-5">No stock transactions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><span class="badge text-bg-light border px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) $transaction['txn_no'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><span class="badge text-bg-<?php echo $txnTypeBadgeMap[$transaction['txn_type']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(ucfirst((string) $transaction['txn_type']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?php echo htmlspecialchars((string) $transaction['txn_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) (($transaction['ref_type'] ?? '') !== null && ($transaction['ref_type'] ?? '') !== '' ? ((string) $transaction['ref_type'] . ' #' . (string) ($transaction['ref_id'] ?? '')) : '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end"><?php echo (int) $transaction['item_count']; ?></td>
                                        <td class="text-end fw-semibold"><?php echo number_format((float) $transaction['total_amount'], 2); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo htmlspecialchars(app_url('/stocks/show?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-light rounded-3">View</a>
                                                <a href="<?php echo htmlspecialchars(app_url('/stocks/edit?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary rounded-3">Edit</a>
                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/stocks/delete?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Delete this stock transaction?');">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Delete</button>
                                                </form>
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
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>