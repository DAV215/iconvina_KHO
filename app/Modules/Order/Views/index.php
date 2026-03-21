<?php
$activeSidebar = $activeSidebar ?? 'orders';
$pageTitle = $pageTitle ?? 'Orders';
$pageEyebrow = $pageEyebrow ?? 'Sales order management';
$search = $search ?? '';
$status = $status ?? '';
$statuses = $statuses ?? [];
$orders = $orders ?? [];

$statusMap = [
    'created' => ['Order created successfully.', 'success'],
    'updated' => ['Order updated successfully.', 'success'],
    'deleted' => ['Order deleted successfully.', 'success'],
];

$statusBadgeMap = [
    'draft' => 'secondary',
    'confirmed' => 'info',
    'in_progress' => 'warning',
    'done' => 'success',
];

$priorityBadgeMap = [
    'low' => 'light',
    'normal' => 'secondary',
    'high' => 'warning',
    'urgent' => 'danger',
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Order module</div>
                            <h3 class="h4 mb-0 fw-semibold">Sales Order Register</h3>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/orders/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4"><i class="bi bi-plus-lg me-2"></i>New order</a>
                        </div>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 mb-4">
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
                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $statusOption)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <button type="submit" class="btn btn-outline-secondary rounded-4 px-4">Search</button>
                            <a href="<?php echo htmlspecialchars(app_url('/orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4 ms-2">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Customer</th>
                                    <th>Quotation</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($orders === []): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-secondary py-5">No orders found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><span class="badge text-bg-light border px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) $order['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string) $order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="small text-secondary"><?php echo htmlspecialchars((string) $order['customer_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) ($order['quotation_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $order['order_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge text-bg-<?php echo $statusBadgeMap[$order['status']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $order['status'])), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><span class="badge text-bg-<?php echo $priorityBadgeMap[$order['priority']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(ucfirst((string) $order['priority']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-end fw-semibold"><?php echo number_format((float) $order['total_amount'], 2); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo htmlspecialchars(app_url('/orders/show?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-light rounded-3">View</a>
                                                <a href="<?php echo htmlspecialchars(app_url('/orders/edit?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary rounded-3">Edit</a>
                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/orders/delete?id=' . (int) $order['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Delete this order?');">
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