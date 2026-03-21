<?php
$activeSidebar = $activeSidebar ?? 'quotations';
$pageTitle = $pageTitle ?? 'Quotations';
$pageEyebrow = $pageEyebrow ?? 'Quotation management';
$search = $search ?? '';
$status = $status ?? '';
$statuses = $statuses ?? [];
$quotations = $quotations ?? [];

$statusMap = [
    'created' => ['Quotation created successfully.', 'success'],
    'updated' => ['Quotation updated successfully.', 'success'],
    'deleted' => ['Quotation deleted successfully.', 'success'],
];

$statusBadgeMap = [
    'draft' => 'secondary',
    'sent' => 'info',
    'approved' => 'success',
    'rejected' => 'danger',
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
                <?php if (isset($statusMap[$status])): ?>
                    <div class="alert alert-<?php echo $statusMap[$status][1]; ?> rounded-4 border-0 shadow-sm mb-4"><?php echo $statusMap[$status][0]; ?></div>
                <?php endif; ?>

                <div class="erp-card p-4 p-xl-5 mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Quotation module</div>
                            <h3 class="h4 mb-0 fw-semibold">Quotation Register</h3>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/quotations/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4"><i class="bi bi-plus-lg me-2"></i>New quotation</a>
                        </div>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 mb-4">
                        <div class="col-12 col-lg-5">
                            <div class="input-group shadow-sm rounded-4 overflow-hidden border bg-white">
                                <span class="input-group-text bg-white border-0 text-secondary"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-0 shadow-none" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search code, customer code, customer name">
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <select name="status" class="form-select rounded-4 shadow-sm">
                                <option value="">All statuses</option>
                                <?php foreach ($statuses as $statusOption): ?>
                                    <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === $statusOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($statusOption), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <button type="submit" class="btn btn-outline-secondary rounded-4 px-4">Search</button>
                            <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4 ms-2">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Customer</th>
                                    <th>Quote Date</th>
                                    <th>Expired At</th>
                                    <th>Status</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($quotations === []): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-5">No quotations found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($quotations as $quotation): ?>
                                    <tr>
                                        <td><span class="badge text-bg-light border px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) $quotation['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string) $quotation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="small text-secondary"><?php echo htmlspecialchars((string) $quotation['customer_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) $quotation['quote_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($quotation['expired_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge text-bg-<?php echo $statusBadgeMap[$quotation['status']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(ucfirst((string) $quotation['status']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-end"><?php echo number_format((float) $quotation['subtotal'], 2); ?></td>
                                        <td class="text-end fw-semibold"><?php echo number_format((float) $quotation['total_amount'], 2); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo htmlspecialchars(app_url('/quotations/show?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-light rounded-3">View</a>
                                                <a href="<?php echo htmlspecialchars(app_url('/quotations/edit?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary rounded-3">Edit</a>
                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/quotations/delete?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Delete this quotation?');">
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