<?php
$activeSidebar = $activeSidebar ?? 'components';
$pageTitle = $pageTitle ?? 'Chi tiết bán thành phẩm';
$pageEyebrow = $pageEyebrow ?? 'Hồ sơ bán thành phẩm';
$status = $status ?? '';
$component = $component ?? [];
$statusMap = [
    'created' => ['Tạo bán thành phẩm thành công.', 'success'],
    'updated' => ['Cập nhật bán thành phẩm thành công.', 'success'],
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
        <section class="erp-page-section">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <?php if (isset($statusMap[$status])): ?><div class="alert alert-<?php echo $statusMap[$status][1]; ?> shadow-sm mb-4"><?php echo $statusMap[$status][0]; ?></div><?php endif; ?>
                <div class="erp-card p-3 p-lg-4 p-xl-5">
                    <div class="erp-toolbar mb-4">
                        <div class="erp-toolbar__meta">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Bán thành phẩm</div>
                            <h3 class="h4 fw-bold mb-1"><?php echo htmlspecialchars((string) ($component['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="erp-inline-note">Mã: <?php echo htmlspecialchars((string) ($component['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="erp-toolbar__actions">
                            <a href="<?php echo htmlspecialchars(app_url('/components'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Quay lại</a>
                            <a href="<?php echo htmlspecialchars(app_url('/components/edit?id=' . (int) ($component['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-pencil-square"></i>Chỉnh sửa</a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-xl-4">
                            <div class="erp-section-panel p-3 p-lg-4 h-100">
                                <div class="fw-semibold mb-3">Hình ảnh bán thành phẩm</div>
                                <div class="erp-image-preview">
                                    <?php if (!empty($component['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars((string) $component['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($component['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php else: ?>
                                        <div class="text-center text-secondary">
                                            <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                            Chưa có hình ảnh
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-8">
                            <div class="erp-section-panel p-3 p-lg-4 h-100">
                                <div class="fw-semibold mb-3">Thông tin chi tiết</div>
                                <dl class="row mb-0 erp-info-list">
                                    <dt class="col-sm-4">Mã bán thành phẩm</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars((string) ($component['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Tên bán thành phẩm</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars((string) ($component['name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Loại / nhóm</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars((string) ($component['component_type'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Giá chuẩn</dt>
                                    <dd class="col-sm-8 fw-semibold"><?php echo number_format((float) ($component['standard_cost'] ?? 0), 2); ?></dd>
                                    <dt class="col-sm-4">Đường dẫn ảnh</dt>
                                    <dd class="col-sm-8 text-break"><?php echo htmlspecialchars((string) ($component['image_path'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Trạng thái</dt>
                                    <dd class="col-sm-8">
                                        <span class="erp-status-badge <?php echo (int) ($component['is_active'] ?? 1) === 1 ? 'is-active' : 'is-inactive'; ?>">
                                            <?php echo (int) ($component['is_active'] ?? 1) === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?>
                                        </span>
                                    </dd>
                                </dl>
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
