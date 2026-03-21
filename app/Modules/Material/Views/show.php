<?php
$activeSidebar = $activeSidebar ?? 'materials';
$pageTitle = $pageTitle ?? 'Chi tiết nguyên vật liệu';
$pageEyebrow = $pageEyebrow ?? 'Hồ sơ nguyên vật liệu';
$status = $status ?? '';
$material = $material ?? [];
$statusMap = [
    'created' => ['Tạo nguyên vật liệu thành công.', 'success'],
    'updated' => ['Cập nhật nguyên vật liệu thành công.', 'success'],
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
                <?php if (isset($statusMap[$status])): ?><div class="alert alert-<?php echo $statusMap[$status][1]; ?> rounded-4 border-0 shadow-sm mb-4"><?php echo $statusMap[$status][0]; ?></div><?php endif; ?>
                <div class="erp-card p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Hồ sơ nguyên vật liệu</div>
                            <h3 class="h4 fw-semibold mb-1"><?php echo htmlspecialchars((string) $material['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="text-secondary">Mã: <?php echo htmlspecialchars((string) $material['code'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/materials'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                            <a href="<?php echo htmlspecialchars(app_url('/materials/edit?id=' . (int) $material['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Chỉnh sửa</a>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Thông tin vật tư</div>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Danh mục</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($material['category_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Đơn vị</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) $material['unit'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Quy cách</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($material['specification'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Màu sắc</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($material['color'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                    <dt class="col-sm-4">Giá chuẩn</dt><dd class="col-sm-8"><?php echo number_format((float) $material['standard_cost'], 2); ?></dd>
                                    <dt class="col-sm-4">Tồn tối thiểu</dt><dd class="col-sm-8"><?php echo number_format((float) $material['min_stock'], 2); ?></dd>
                                    <dt class="col-sm-4">Trạng thái</dt><dd class="col-sm-8"><?php echo (int) $material['is_active'] === 1 ? 'Đang dùng' : 'Ngừng dùng'; ?></dd>
                                </dl>
                            </div>
                        </div>
                        <div class="col-12 col-xl-5">
                            <div class="erp-card p-4 h-100">
                                <div class="small text-uppercase text-secondary fw-semibold mb-3">Hình ảnh và mô tả</div>
                                <div class="mb-3"><strong>Ảnh vật tư</strong><div class="text-secondary mt-2"><?php echo htmlspecialchars((string) ($material['image_path'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div></div>
                                <div><strong>Mô tả</strong><div class="text-secondary mt-2"><?php echo nl2br(htmlspecialchars((string) ($material['description'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div></div>
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