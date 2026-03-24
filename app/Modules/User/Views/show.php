<?php
$activeSidebar = $activeSidebar ?? 'users';
$pageTitle = $pageTitle ?? 'Chi tiết người dùng';
$user = $user ?? [];
$statusMap = ['draft' => ['secondary', 'Nháp'], 'active' => ['success', 'Đang hoạt động'], 'suspended' => ['warning', 'Tạm khóa'], 'resigned' => ['dark', 'Đã nghỉ việc'], 'deleted' => ['danger', 'Đã xóa']];
[$statusBadge, $statusLabel] = $statusMap[(string) ($user['status'] ?? 'draft')] ?? ['secondary', (string) ($user['status'] ?? 'draft')];
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
                        <div><div class="text-uppercase small fw-semibold text-secondary mb-2">Người dùng</div><div class="d-flex align-items-center gap-2 flex-wrap mb-2"><h3 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) ($user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3><span class="badge text-bg-<?php echo htmlspecialchars($statusBadge, ENT_QUOTES, 'UTF-8'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></div><div class="text-secondary">Mã: <?php echo htmlspecialchars((string) ($user['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> | Username: <?php echo htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div></div>
                        <div class="d-flex gap-2"><a href="<?php echo htmlspecialchars(app_url('/users'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a><a href="<?php echo htmlspecialchars(app_url('/users/edit?id=' . (int) ($user['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Chỉnh sửa</a></div>
                    </div>
                    <div class="row g-4">
                        <div class="col-12 col-lg-6"><div class="erp-card p-4 h-100"><div class="small text-uppercase text-secondary fw-semibold mb-3">Thông tin chính</div><dl class="row mb-0"><dt class="col-sm-4">Vai trò</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['role_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Chức danh</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['position_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Điện thoại</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Quản lý trực tiếp</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['manager_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Ngày vào làm</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['joined_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Ngày nghỉ việc</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['terminated_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd></dl></div></div>
                        <div class="col-12 col-lg-6"><div class="erp-card p-4 h-100"><div class="small text-uppercase text-secondary fw-semibold mb-3">Thiết lập hệ thống</div><dl class="row mb-0"><dt class="col-sm-4">Ngôn ngữ</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['language'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Múi giờ</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['timezone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Theme</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['theme'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Đã xác minh</dt><dd class="col-sm-8"><?php echo (int) ($user['is_verified'] ?? 0) === 1 ? 'Có' : 'Chưa'; ?></dd><dt class="col-sm-4">Đăng nhập gần nhất</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['last_login_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd><dt class="col-sm-4">Khóa đến</dt><dd class="col-sm-8"><?php echo htmlspecialchars((string) ($user['locked_until'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></dd></dl></div></div>
                        <div class="col-12"><div class="erp-card p-4"><div class="small text-uppercase text-secondary fw-semibold mb-3">Ghi chú</div><div class="text-secondary"><?php echo nl2br(htmlspecialchars((string) ($user['note'] ?? '-'), ENT_QUOTES, 'UTF-8')); ?></div></div></div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
