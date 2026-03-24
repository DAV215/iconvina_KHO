<?php
$activeSidebar = $activeSidebar ?? 'material-categories';
$pageTitle = $pageTitle ?? 'Danh mục nguyên vật liệu';
$pageEyebrow = $pageEyebrow ?? 'Quản lý danh mục nguyên vật liệu';
$search = $search ?? '';
$status = $status ?? '';
$categories = $categories ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
$statusMap = ['created' => ['Tạo danh mục nguyên vật liệu thành công.', 'success'], 'updated' => ['Cập nhật danh mục nguyên vật liệu thành công.', 'success'], 'deleted' => ['Xóa danh mục nguyên vật liệu thành công.', 'success']];
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Danh mục vật tư</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách danh mục</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#materialCategoryFilterCollapse" aria-expanded="true" aria-controls="materialCategoryFilterCollapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-layout-three-columns"></i>Hiển thị cột</button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="materialCategoriesTable">
                                    <?php foreach (['code' => 'Mã', 'tree' => 'Cây danh mục', 'parent' => 'Danh mục cha', 'children' => 'Con', 'materials' => 'Số vật tư', 'status' => 'Trạng thái'] as $key => $label): ?>
                                        <label class="form-check"><input class="form-check-input" type="checkbox" value="<?php echo $key; ?>" checked><span class="form-check-label"><?php echo $label; ?></span></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars(app_url('/material-categories/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm danh mục</a>
                        </div>
                    </div>
                    <div class="collapse show mb-4" id="materialCategoryFilterCollapse" data-filter-collapse="material-categories">
                    <form method="get" action="<?php echo htmlspecialchars(app_url('/material-categories'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-semibold">Tìm kiếm danh mục</label>
                                <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm theo mã, tên hoặc danh mục cha">
                            </div>
                            <div class="col-12 col-lg-3">
                                <label class="form-label fw-semibold">Hiển thị</label>
                                <select name="per_page" class="form-select erp-select">
                                    <?php foreach ([10, 25, 50, 100] as $size): ?>
                                        <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="d-flex gap-2 justify-content-lg-end">
                                    <a href="<?php echo htmlspecialchars(app_url('/material-categories'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                    <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="materialCategoriesTable">
                                <thead>
                                    <tr>
                                        <th data-col="code">Mã</th>
                                        <th data-col="tree">Cây danh mục</th>
                                        <th data-col="parent">Danh mục cha</th>
                                        <th data-col="children" class="text-center">Con</th>
                                        <th data-col="materials" class="text-center">Số vật tư</th>
                                        <th data-col="status" class="text-center">Trạng thái</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($categories === []): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-secondary">Chưa có danh mục nguyên vật liệu nào.</td></tr>
                                <?php else: foreach ($categories as $category): ?>
                                    <tr class="erp-row-compact">
                                        <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $category['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td data-col="tree">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="erp-tree-indent" style="--depth: <?php echo (int) ($category['depth'] ?? 0); ?>"></span>
                                                <span class="fw-semibold"><?php echo htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </td>
                                        <td data-col="parent"><?php echo htmlspecialchars((string) ($category['parent_name'] ?? 'Không có'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td data-col="children" class="text-center"><?php echo (int) ($category['child_count'] ?? 0); ?></td>
                                        <td data-col="materials" class="text-center"><?php echo (int) ($category['material_count'] ?? 0); ?></td>
                                        <td data-col="status" class="text-center"><span class="erp-status-badge <?php echo (int) ($category['is_active'] ?? 1) === 1 ? 'is-active' : 'is-inactive'; ?>"><?php echo (int) ($category['is_active'] ?? 1) === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?></span></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Thao tác</button>
                                                <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/material-categories/show?id=' . (int) $category['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/material-categories/edit?id=' . (int) $category['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                    <li><form method="post" action="<?php echo htmlspecialchars(app_url('/material-categories/delete?id=' . (int) $category['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
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
