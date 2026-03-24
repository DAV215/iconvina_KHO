<?php
$activeSidebar = $activeSidebar ?? 'components';
$pageTitle = $pageTitle ?? 'Bán thành phẩm';
$pageEyebrow = $pageEyebrow ?? 'Quản lý bán thành phẩm';
$search = $search ?? '';
$components = $components ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Bán thành phẩm</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách bán thành phẩm</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#componentFilterCollapse" aria-expanded="true" aria-controls="componentFilterCollapse"><i class="bi bi-funnel"></i>Bộ lọc</button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-layout-three-columns"></i>Hiển thị cột</button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="componentsTable">
                                    <?php foreach (['image' => 'Ảnh', 'code' => 'Mã', 'name' => 'Tên', 'type' => 'Đơn vị', 'cost' => 'Giá chuẩn', 'status' => 'Kích hoạt'] as $key => $label): ?>
                                        <label class="form-check"><input class="form-check-input" type="checkbox" value="<?php echo $key; ?>" checked><span class="form-check-label"><?php echo $label; ?></span></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars(app_url('/components/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm mới</a>
                        </div>
                    </div>
                    <div class="collapse show mb-4" id="componentFilterCollapse" data-filter-collapse="components">
                    <form method="get" action="<?php echo htmlspecialchars(app_url('/components'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-7">
                                <label class="form-label fw-semibold">Tìm kiếm</label>
                                <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm theo mã, tên, đơn vị">
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
                                    <a href="<?php echo htmlspecialchars(app_url('/components'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                                    <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Tìm kiếm</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="componentsTable">
                                <thead>
                                    <tr>
                                        <th data-col="image">Ảnh</th>
                                        <th data-col="code">Mã</th>
                                        <th data-col="name">Tên</th>
                                        <th data-col="type">Đơn vị</th>
                                        <th data-col="cost" class="text-end">Giá chuẩn</th>
                                        <th data-col="status" class="text-center">Kích hoạt</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($components === []): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-secondary">Chưa có bán thành phẩm nào.</td></tr>
                                <?php else: foreach ($components as $component): ?>
                                    <tr class="erp-row-compact">
                                        <td data-col="image">
                                            <?php if (!empty($component['image_path'])): ?>
                                                <img src="<?php echo htmlspecialchars((string) $component['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="rounded-3 border" style="width:40px;height:40px;object-fit:cover;">
                                            <?php else: ?>
                                                <div class="rounded-3 border bg-light d-inline-flex align-items-center justify-content-center text-secondary" style="width:40px;height:40px;"><i class="bi bi-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $component['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td data-col="name" class="fw-semibold"><?php echo htmlspecialchars((string) $component['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td data-col="type"><?php echo htmlspecialchars((string) $component['component_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td data-col="cost" class="text-end fw-semibold"><?php echo number_format((float) $component['standard_cost'], 2); ?></td>
                                        <td data-col="status" class="text-center"><span class="erp-status-badge <?php echo (int) $component['is_active'] === 1 ? 'is-active' : 'is-inactive'; ?>"><?php echo (int) $component['is_active'] === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?></span></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Thao tác</button>
                                                <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/components/show?id=' . (int) $component['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                    <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/components/edit?id=' . (int) $component['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                    <li><form method="post" action="<?php echo htmlspecialchars(app_url('/components/delete?id=' . (int) $component['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa bán thành phẩm này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
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
