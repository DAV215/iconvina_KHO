<?php
$activeSidebar = $activeSidebar ?? 'quotations';
$pageTitle = $pageTitle ?? 'Quotation Detail';
$pageEyebrow = $pageEyebrow ?? 'Quotation profile';
$quotation = $quotation ?? [];
$status = $status ?? '';

$statusMap = [
    'created' => ['Quotation created successfully.', 'success'],
    'updated' => ['Quotation updated successfully.', 'success'],
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

                <div class="erp-card p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Quotation profile</div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h3 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) $quotation['code'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <span class="badge text-bg-<?php echo $statusBadgeMap[$quotation['status']] ?? 'secondary'; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars(ucfirst((string) $quotation['status']), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="text-secondary">
                                Customer:
                                <a href="<?php echo htmlspecialchars(app_url('/customers/show?id=' . (int) $quotation['customer_id']), ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars((string) $quotation['customer_code'] . ' - ' . (string) $quotation['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/quotations'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                            <a href="<?php echo htmlspecialchars(app_url('/quotations/edit?id=' . (int) $quotation['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Edit</a>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-xl-8">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Quotation info</div>
                                <div class="row g-4">
                                    <div class="col-12 col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Quote Date</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) $quotation['quote_date'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-sm-5">Expired At</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['expired_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-sm-5">Customer Contact</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['customer_contact_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-sm-5">Phone</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['customer_phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </dl>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Email</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['customer_email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-sm-5">Tax Code</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['customer_tax_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-sm-5">Created At</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            <dt class="col-sm-5">Updated At</dt><dd class="col-sm-7"><?php echo htmlspecialchars((string) ($quotation['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </dl>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="fw-semibold mb-2">Customer Address</div>
                                    <div class="text-secondary"><?php echo nl2br(htmlspecialchars((string) ($quotation['customer_address'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div>
                                </div>

                                <div class="mt-4">
                                    <div class="fw-semibold mb-2">Note</div>
                                    <div class="text-secondary"><?php echo nl2br(htmlspecialchars((string) ($quotation['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Financial summary</div>
                                <dl class="row mb-0">
                                    <dt class="col-6">Subtotal</dt><dd class="col-6 text-end"><?php echo number_format((float) $quotation['subtotal'], 2); ?></dd>
                                    <dt class="col-6">Discount</dt><dd class="col-6 text-end"><?php echo number_format((float) $quotation['discount_amount'], 2); ?></dd>
                                    <dt class="col-6">Tax</dt><dd class="col-6 text-end"><?php echo number_format((float) $quotation['tax_amount'], 2); ?></dd>
                                    <dt class="col-6 fw-semibold">Total</dt><dd class="col-6 text-end fw-semibold"><?php echo number_format((float) $quotation['total_amount'], 2); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="erp-card p-4">
                        <div class="small text-uppercase text-secondary fw-semibold mb-3">Quotation items</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Type</th>
                                        <th>Description</th>
                                        <th>Unit</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (($quotation['items'] ?? []) === []): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-secondary py-5">No quotation items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($quotation['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo (int) $item['line_no']; ?></td>
                                            <td><?php echo htmlspecialchars((string) $item['item_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars((string) $item['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars((string) $item['unit'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end"><?php echo number_format((float) $item['quantity'], 2); ?></td>
                                            <td class="text-end"><?php echo number_format((float) $item['unit_price'], 2); ?></td>
                                            <td class="text-end"><?php echo number_format((float) $item['discount_amount'], 2); ?></td>
                                            <td class="text-end fw-semibold"><?php echo number_format((float) $item['total_amount'], 2); ?></td>
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