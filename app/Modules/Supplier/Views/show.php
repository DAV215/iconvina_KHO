<?php
$activeSidebar = $activeSidebar ?? 'suppliers';
$pageTitle = $pageTitle ?? 'Chi tiết nhà cung cấp';
$supplier = $supplier ?? [];
$statusBadge = (int) ($supplier['is_active'] ?? 1) === 1 ? 'success' : 'secondary';
$statusLabel = (int) ($supplier['is_active'] ?? 1) === 1 ? 'Đang dùng' : 'Ngưng dùng';
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Nhà cung cấp</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <h3 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) $supplier['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <span class="badge text-bg-<?php echo $statusBadge; ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="text-secondary">Mã NCC: <?php echo htmlspecialchars((string) $supplier['code'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/suppliers'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                            <a href="<?php echo htmlspecialchars(app_url('/suppliers/edit?id=' . (int) $supplier['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Chỉnh sửa</a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Thông tin chính</div>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Người liên hệ</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($supplier['contact_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Điện thoại</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($supplier['phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($supplier['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Mã số thuế</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($supplier['tax_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Địa chỉ & ghi chú</div>
                                <div class="mb-3"><strong>Địa chỉ</strong><div class="text-secondary mt-2"><?php echo nl2br(htmlspecialchars((string) ($supplier['address'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div></div>
                                <div><strong>Ghi chú</strong><div class="text-secondary mt-2"><?php echo nl2br(htmlspecialchars((string) ($supplier['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div></div>
                            </div>
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
