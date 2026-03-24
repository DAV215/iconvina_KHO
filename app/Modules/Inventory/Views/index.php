<?php
$activeSidebar = $activeSidebar ?? 'inventory';
$pageTitle = $pageTitle ?? 'Stock Transactions';
$pageEyebrow = $pageEyebrow ?? 'Inventory management';
$search = $search ?? '';
$txnType = $txnType ?? '';
$txnTypes = $txnTypes ?? [];
$transactions = $transactions ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$txnTypeBadgeMap = [
    'import' => 'is-active',
    'receipt' => 'is-active',
    'export' => 'is-inactive',
    'issue' => 'is-inactive',
    'adjustment' => '',
];
$canCreate = has_permission('stock.create');
$canUpdate = has_permission('stock.update');
$canDelete = has_permission('stock.delete');
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
                <div class="erp-card p-3 p-lg-4 p-xl-5">
                    <div class="erp-toolbar mb-4">
                        <div class="erp-toolbar__meta">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Kho</div>
                            <h3 class="h4 fw-bold mb-1">Sổ giao dịch kho</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#stockFilterCollapse" aria-expanded="true" aria-controls="stockFilterCollapse"><i class="bi bi-funnel"></i>Bộ lọc</button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-layout-three-columns"></i>Hiển thị cột</button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="stocksTable">
                                    <?php foreach (['txn_no' => 'Mã phiếu', 'type' => 'Loại', 'date' => 'Ngày', 'reference' => 'Tham chiếu', 'items' => 'Items', 'total' => 'Tổng tiền'] as $key => $label): ?>
                                        <label class="form-check"><input class="form-check-input" type="checkbox" value="<?php echo $key; ?>" checked><span class="form-check-label"><?php echo $label; ?></span></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if ($canCreate): ?><a href="<?php echo htmlspecialchars(app_url('/stocks/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>New transaction</a><?php endif; ?>
                        </div>
                    </div>
                    <div class="collapse show mb-4" id="stockFilterCollapse" data-filter-collapse="stocks">
                    <form method="get" action="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search txn no or date">
                            </div>
                            <div class="col-12 col-lg-3">
                                <label class="form-label fw-semibold">Type</label>
                                <select name="txn_type" class="form-select erp-select">
                                    <option value="">All types</option>
                                    <?php foreach ($txnTypes as $txnTypeOption): ?>
                                        <option value="<?php echo htmlspecialchars($txnTypeOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $txnType === $txnTypeOption ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucfirst($txnTypeOption), ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-2">
                                <label class="form-label fw-semibold">Hiển thị</label>
                                <select name="per_page" class="form-select erp-select">
                                    <?php foreach ([10, 25, 50, 100] as $size): ?>
                                        <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="d-flex gap-2 justify-content-lg-end">
                                    <a href="<?php echo htmlspecialchars(app_url('/stocks'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Reset</a>
                                    <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="stocksTable">
                                <thead>
                                    <tr>
                                        <th data-col="txn_no">Txn No</th>
                                        <th data-col="type">Type</th>
                                        <th data-col="date">Txn Date</th>
                                        <th data-col="reference">Reference</th>
                                        <th data-col="items" class="text-end">Items</th>
                                        <th data-col="total" class="text-end">Total</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($transactions === []): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-secondary">No stock transactions found.</td></tr>
                                <?php else: foreach ($transactions as $transaction): ?>
                                    <tr class="erp-row-compact">
                                        <td data-col="txn_no"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $transaction['txn_no'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td data-col="type"><span class="erp-status-badge <?php echo ($txnTypeBadgeMap[$transaction['txn_type']] ?? '') ?: 'is-inactive'; ?>"><?php echo htmlspecialchars(ucfirst((string) $transaction['txn_type']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td data-col="date"><?php echo htmlspecialchars((string) $transaction['txn_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td data-col="reference" class="erp-cell-compact"><?php echo htmlspecialchars((string) (($transaction['ref_type'] ?? '') !== null && ($transaction['ref_type'] ?? '') !== '' ? ((string) $transaction['ref_type'] . ' #' . (string) ($transaction['ref_id'] ?? '')) : '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td data-col="items" class="text-end"><?php echo (int) $transaction['item_count']; ?></td>
                                        <td data-col="total" class="text-end fw-semibold"><?php echo number_format((float) $transaction['total_amount'], 2); ?></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/stocks/show?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>">View</a></li>
                                                    <?php if ($canUpdate): ?><li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/stocks/edit?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>">Edit</a></li><?php endif; ?>
                                                    <?php if ($canDelete): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/stocks/delete?id=' . (int) $transaction['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Delete this stock transaction?');"><button type="submit" class="dropdown-item text-danger">Delete</button></form></li><?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php include base_path('app/Modules/Home/Views/partials/list_pagination.php'); ?>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars(app_url('/assets/js/erp-list.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
