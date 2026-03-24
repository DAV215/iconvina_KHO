<?php
$activeSidebar = $activeSidebar ?? 'companies';
$pageTitle = $pageTitle ?? 'Công ty';
$filters = $filters ?? ['search' => '', 'status' => ''];
$sort = $sort ?? ['by' => 'updated_at', 'dir' => 'DESC'];
$statuses = $statuses ?? [];
$sortOptions = $sortOptions ?? [];
$companies = $companies ?? [];
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
        <section class="erp-page-section"><div class="container-fluid px-3 px-lg-4 px-xl-5"><div class="erp-card p-3 p-lg-4 p-xl-5">
            <div class="erp-toolbar mb-4"><div class="erp-toolbar__meta"><div class="text-uppercase small fw-semibold text-secondary mb-2">Tổ chức</div><h3 class="h4 fw-bold mb-1">Danh sách công ty</h3></div><div class="erp-toolbar__actions"><button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#companyFilterCollapse" aria-expanded="true"><i class="bi bi-funnel"></i>Bộ lọc</button><a href="<?php echo htmlspecialchars(app_url('/companies/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm công ty</a></div></div>
            <div class="collapse show mb-4" id="companyFilterCollapse" data-filter-collapse="companies"><form method="get" action="<?php echo htmlspecialchars(app_url('/companies'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-0"><div class="row g-3 align-items-end"><div class="col-12 col-lg-4"><label class="form-label fw-semibold">Tìm kiếm</label><input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars((string) $filters['search'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Mã, tên công ty"></div><div class="col-12 col-lg-2"><label class="form-label fw-semibold">Trạng thái</label><select name="status" class="form-select erp-select"><option value="">Tất cả</option><?php foreach ($statuses as $value => $label): ?><option value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $filters['status'] === (string) $value ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div><div class="col-12 col-lg-2"><label class="form-label fw-semibold">Hiển thị</label><select name="per_page" class="form-select erp-select"><?php foreach ([10, 25, 50, 100] as $size): ?><option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option><?php endforeach; ?></select></div><div class="col-12 col-lg-2"><label class="form-label fw-semibold">Sắp xếp theo</label><select name="sort_by" class="form-select erp-select"><?php foreach ($sortOptions as $sortKey => $sortLabel): ?><option value="<?php echo htmlspecialchars($sortKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $sort['by'] === $sortKey ? 'selected' : ''; ?>><?php echo htmlspecialchars($sortLabel, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div><div class="col-12 col-lg-2"><label class="form-label fw-semibold">Chiều sắp xếp</label><select name="sort_dir" class="form-select erp-select"><option value="desc" <?php echo strtolower((string) $sort['dir']) === 'desc' ? 'selected' : ''; ?>>Giảm dần</option><option value="asc" <?php echo strtolower((string) $sort['dir']) === 'asc' ? 'selected' : ''; ?>>Tăng dần</option></select></div><div class="col-12"><div class="d-flex gap-2 justify-content-lg-end"><a href="<?php echo htmlspecialchars(app_url('/companies'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a><button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Lọc</button></div></div></div></form></div>
            <div class="erp-table-shell p-2 p-lg-3"><div class="erp-table-wrap"><table class="table erp-table align-middle"><thead><tr><th>Mã</th><th>Tên công ty</th><th>Trạng thái</th><th>Cập nhật</th><th class="text-end">Thao tác</th></tr></thead><tbody><?php if ($companies === []): ?><tr><td colspan="5" class="text-center text-secondary py-5">Không có công ty phù hợp.</td></tr><?php else: foreach ($companies as $company): ?><tr class="erp-row-compact"><td><span class="erp-code-badge"><?php echo htmlspecialchars((string) $company['code'], ENT_QUOTES, 'UTF-8'); ?></span></td><td class="fw-semibold"><?php echo htmlspecialchars((string) $company['name'], ENT_QUOTES, 'UTF-8'); ?></td><td><span class="badge text-bg-<?php echo $company['deleted_at'] !== null ? 'danger' : ((int) ($company['is_active'] ?? 1) === 1 ? 'success' : 'secondary'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars($company['deleted_at'] !== null ? 'Đã xóa' : ((int) ($company['is_active'] ?? 1) === 1 ? 'Đang dùng' : 'Ngưng dùng'), ENT_QUOTES, 'UTF-8'); ?></span></td><td><?php echo htmlspecialchars((string) ($company['updated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td><td class="text-end"><div class="dropdown"><button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button><ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu"><li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/companies/show?id=' . (int) $company['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li><li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/companies/edit?id=' . (int) $company['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li><?php if ($company['deleted_at'] === null): ?><li><form method="post" action="<?php echo htmlspecialchars(app_url('/companies/disable?id=' . (int) $company['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Ngưng sử dụng công ty này?');"><button type="submit" class="dropdown-item">Ngưng dùng</button></form></li><li><form method="post" action="<?php echo htmlspecialchars(app_url('/companies/delete?id=' . (int) $company['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xóa mềm công ty này?');"><button type="submit" class="dropdown-item text-danger">Xóa mềm</button></form></li><?php endif; ?></ul></div></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
            <?php include base_path('app/Modules/Home/Views/partials/list_pagination.php'); ?>
        </div></div></section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars(app_url('/assets/js/erp-list.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
