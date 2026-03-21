<?php
$activeSidebar = $activeSidebar ?? 'bom';
$pageTitle = $pageTitle ?? 'Chi tiết BOM';
$pageEyebrow = $pageEyebrow ?? 'Hồ sơ BOM';
$status = $status ?? '';
$bom = $bom ?? [];
$items = $bom['items'] ?? [];
$statusMap = [
    'created' => ['Tạo BOM thành công.', 'success'],
    'updated' => ['Cập nhật BOM thành công.', 'success'],
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">BOM profile</div>
                            <h3 class="h4 fw-semibold mb-1"><?php echo htmlspecialchars((string) ($bom['bom_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="text-secondary"><?php echo htmlspecialchars((string) $bom['component_code'] . ' - ' . (string) $bom['component_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                            <a href="<?php echo htmlspecialchars(app_url('/bom/edit?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Edit</a>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-lg-6">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Thông tin BOM</div>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">BOM</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($bom['bom_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Component</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) $bom['component_code'] . ' - ' . (string) $bom['component_name'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Loại</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($bom['component_type'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Version</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) $bom['version'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Active</dt><dd class="col-sm-8"><?php echo (int) $bom['is_active'] === 1 ? 'Có' : 'Không'; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="erp-card p-4">
                        <div class="small text-uppercase text-secondary fw-semibold mb-3">BOM items</div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Loại</th>
                                        <th>Tên item</th>
                                        <th>Qty</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($items === []): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-5">Chưa có BOM items.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $index => $item): ?>
                                        <?php $label = (string) $item['item_kind'] === 'component'
                                            ? ((string) ($item['child_component_code'] ?? '') . ' - ' . (string) ($item['child_component_name'] ?? ''))
                                            : ((string) ($item['material_code'] ?? '') . ' - ' . (string) ($item['material_name'] ?? '')); ?>
                                        <tr>
                                            <td><?php echo (int) $index + 1; ?></td>
                                            <td><span class="badge text-bg-light border px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) $item['item_kind'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars(number_format((float) $item['quantity'], 2, '.', ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars((string) ($item['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></td>
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
