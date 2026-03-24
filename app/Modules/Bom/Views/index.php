<?php
$activeSidebar = $activeSidebar ?? 'bom';
$pageTitle = $pageTitle ?? 'BOM';
$pageEyebrow = $pageEyebrow ?? 'Quản lý BOM';
$componentId = (int) ($componentId ?? 0);
$version = $version ?? '';
$components = $components ?? [];
$boms = $boms ?? [];
$status = $status ?? '';
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$statusMap = ['created' => ['Tạo BOM thành công.', 'success'], 'updated' => ['Cập nhật BOM thành công.', 'success'], 'deleted' => ['Xóa BOM thành công.', 'success']];
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
                <?php if (isset($statusMap[$status])): ?><div class="alert alert-<?php echo $statusMap[$status][1]; ?> shadow-sm mb-4"><?php echo $statusMap[$status][0]; ?></div><?php endif; ?>
                <div class="erp-card p-3 p-lg-4 p-xl-5">
                    <div class="erp-toolbar mb-4">
                        <div class="erp-toolbar__meta">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">BOM</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách BOM</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#bomFilterCollapse" aria-expanded="true" aria-controls="bomFilterCollapse"><i class="bi bi-funnel"></i>Bộ lọc</button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-layout-three-columns"></i>Hiển thị cột</button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="bomTable">
                                    <?php foreach (['bom' => 'BOM', 'component' => 'Component', 'status' => 'Trạng thái'] as $key => $label): ?>
                                        <label class="form-check"><input class="form-check-input" type="checkbox" value="<?php echo $key; ?>" checked><span class="form-check-label"><?php echo $label; ?></span></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars(app_url('/bom/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm BOM</a>
                        </div>
                    </div>
                    <div class="collapse show mb-4" id="bomFilterCollapse" data-filter-collapse="bom">
                    <form method="get" action="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-semibold">Bán thành phẩm</label>
                                <select name="component_id" class="form-select erp-select">
                                    <option value="">Tất cả</option>
                                    <?php foreach ($components as $component): ?>
                                        <option value="<?php echo (int) $component['id']; ?>" <?php echo $componentId === (int) $component['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-semibold">Phiên bản</label>
                                <input type="text" name="version" class="form-control erp-field" value="<?php echo htmlspecialchars((string) $version, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập phiên bản">
                            </div>
                            <div class="col-12 col-lg-2">
                                <label class="form-label fw-semibold">Hiển thị</label>
                                <select name="per_page" class="form-select erp-select">
                                    <?php foreach ([10, 25, 50, 100] as $size): ?>
                                        <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="d-flex gap-2 justify-content-lg-end">
                                    <a href="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                    <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-funnel"></i>Lọc</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="bomTable">
                                <thead>
                                    <tr>
                                        <th data-col="bom">BOM</th>
                                        <th data-col="component">Component</th>
                                        <th data-col="status">Trạng thái</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($boms === []): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-secondary">Chưa có BOM nào.</td></tr>
                                <?php else: foreach ($boms as $bom): ?>
                                    <tr class="erp-row-compact">
                                        <td data-col="bom" class="erp-cell-compact">
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string) ($bom['bom_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="erp-cell-secondary">Version: <?php echo htmlspecialchars((string) $bom['version'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td data-col="component" class="erp-cell-compact">
                                            <div><?php echo htmlspecialchars((string) $bom['component_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="erp-cell-secondary"><?php echo htmlspecialchars((string) $bom['component_code'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td data-col="status"><span class="erp-status-badge <?php echo (int) $bom['is_active'] === 1 ? 'is-active' : 'is-inactive'; ?>"><?php echo (int) $bom['is_active'] === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?></span></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Thao tác</button>
                                                <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/bom/show?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/bom/tree?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>">Xem cây BOM</a></li>
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/bom/edit?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                    <li><form method="post" action="<?php echo htmlspecialchars(app_url('/bom/delete?id=' . (int) $bom['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xóa BOM này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
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
