<?php
$activeSidebar = $activeSidebar ?? 'customers';
$pageTitle = $pageTitle ?? 'Customers';
$pageEyebrow = $pageEyebrow ?? 'Customer management';
$search = $search ?? '';
$status = $status ?? '';
$customers = $customers ?? [];

$statusMap = [
    'created' => ['Customer created successfully.', 'success'],
    'updated' => ['Customer updated successfully.', 'success'],
    'deleted' => ['Customer deleted successfully.', 'success'],
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Customer module</div>
                            <h3 class="h4 mb-0 fw-semibold">Customer Directory</h3>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/customers/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4"><i class="bi bi-plus-lg me-2"></i>New customer</a>
                        </div>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/customers'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 mb-4">
                        <div class="col-12 col-lg-5">
                            <div class="input-group shadow-sm rounded-4 overflow-hidden border bg-white">
                                <span class="input-group-text bg-white border-0 text-secondary"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-0 shadow-none" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search code, name, contact, phone, email">
                            </div>
                        </div>
                        <div class="col-12 col-lg-auto">
                            <button type="submit" class="btn btn-outline-secondary rounded-4 px-4">Search</button>
                            <a href="<?php echo htmlspecialchars(app_url('/customers'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4 ms-2">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Tax Code</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($customers === []): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-5">No customers found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><span class="badge text-bg-light border px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) $customer['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars((string) $customer['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($customer['contact_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($customer['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($customer['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($customer['tax_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo htmlspecialchars(app_url('/customers/show?id=' . (int) $customer['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-light rounded-3">View</a>
                                                <a href="<?php echo htmlspecialchars(app_url('/customers/edit?id=' . (int) $customer['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary rounded-3">Edit</a>
                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/customers/delete?id=' . (int) $customer['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Delete this customer?');">
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
