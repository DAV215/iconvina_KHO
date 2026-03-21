<?php
$activeSidebar = $activeSidebar ?? 'bom';
$pageTitle = $pageTitle ?? 'BOM';
$pageEyebrow = $pageEyebrow ?? 'Quản lý BOM';
$componentId = (int) ($componentId ?? 0);
$version = $version ?? '';
$components = $components ?? [];
$boms = $boms ?? [];
$status = $status ?? '';
$statusMap = [
    'created' => ['Tạo BOM thành công.', 'success'],
    'updated' => ['Cập nhật BOM thành công.', 'success'],
    'deleted' => ['Xóa BOM thành công.', 'success'],
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">BOM module</div>
                            <h3 class="h4 mb-0 fw-semibold">Danh sách BOM</h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/bom/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4"><i class="bi bi-plus-lg me-2"></i>Thêm BOM</a>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3 mb-4">
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Bán thành phẩm</label>
                            <select name="component_id" class="form-select rounded-4">
                                <option value="">Tất cả</option>
                                <?php foreach ($components as $component): ?>
                                    <option value="<?php echo (int) $component['id']; ?>" <?php echo $componentId === (int) $component['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Version</label>
                            <input type="text" name="version" class="form-control rounded-4" value="<?php echo htmlspecialchars((string) $version, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập version">
                        </div>
                        <div class="col-12 col-lg-auto d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-outline-secondary rounded-4 px-4">Lọc</button>
                            <a href="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>BOM</th>
                                    <th>Component</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($boms === []): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-5">Chưa có BOM nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($boms as $bom): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string) ($bom['bom_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-secondary small">Version: <?php echo htmlspecialchars((string) $bom['version'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars((string) $bom['component_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-secondary small"><?php echo htmlspecialchars((string) $bom['component_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td>
                                            <?php if ((int) $bom['is_active'] === 1): ?>
                                                <span class="badge text-bg-success rounded-pill px-3 py-2">Active</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary rounded-pill px-3 py-2">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo htmlspecialchars(app_url('/bom/show?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-light rounded-3">View</a>
                                                <a href="<?php echo htmlspecialchars(app_url('/bom/edit?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-secondary rounded-3">Edit</a>
                                                <form method="post" action="<?php echo htmlspecialchars(app_url('/bom/delete?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xóa BOM này?');">
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
