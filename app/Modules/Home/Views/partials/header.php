<?php
$pageEyebrow = $pageEyebrow ?? 'ICONVINA ERP';
$pageTitle = $pageTitle ?? 'Trung tâm điều hành';
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$currentLocale = app_locale();
$currentUser = auth_user();
$currentUserName = is_array($currentUser) ? (string) ($currentUser['full_name'] ?? $currentUser['username'] ?? 'Người dùng') : 'Khách';
$currentUserRole = is_array($currentUser) ? (string) ($currentUser['role_name'] ?? 'Người dùng hệ thống') : 'Chưa đăng nhập';
$initials = 'IV';
if (is_array($currentUser)) {
    $source = trim((string) ($currentUser['full_name'] ?? $currentUser['username'] ?? ''));
    if ($source !== '') {
        $parts = preg_split('/\s+/', $source) ?: [];
        $first = mb_substr((string) ($parts[0] ?? ''), 0, 1);
        $last = mb_substr((string) ($parts[count($parts) - 1] ?? ''), 0, 1);
        $initials = strtoupper(trim($first . $last));
        if ($initials === '') {
            $initials = 'US';
        }
    }
}
?>
<header class="erp-header sticky-top border-bottom">
    <div class="container-fluid px-3 px-lg-4 px-xl-5 py-3 py-lg-4">
        <div class="d-flex flex-column gap-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <button class="btn btn-light erp-btn erp-mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#erpMobileNav" aria-label="Mở menu">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <div class="text-uppercase fw-semibold erp-header-eyebrow text-secondary"><?php echo htmlspecialchars($pageEyebrow, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <h2 class="h3 mb-1 fw-bold erp-page-title"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <div class="erp-header-subtitle">Giao diện quản trị sản xuất, vật tư và BOM đồng bộ cho ICONVINA.</div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
                    <div class="btn-group" role="group" aria-label="<?php echo htmlspecialchars(__('common.language'), ENT_QUOTES, 'UTF-8'); ?>">
                        <a href="<?php echo htmlspecialchars(current_url(['lang' => 'vi']), ENT_QUOTES, 'UTF-8'); ?>" class="btn <?php echo $currentLocale === 'vi' ? 'btn-dark' : 'btn-light'; ?> erp-btn erp-btn-sm">VI</a>
                        <a href="<?php echo htmlspecialchars(current_url(['lang' => 'en']), ENT_QUOTES, 'UTF-8'); ?>" class="btn <?php echo $currentLocale === 'en' ? 'btn-dark' : 'btn-light'; ?> erp-btn erp-btn-sm">EN</a>
                    </div>
                    <button class="btn btn-light erp-btn erp-btn-sm position-relative" type="button" aria-label="Thông báo">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </button>
                    <div class="d-flex align-items-center gap-3 rounded-4 px-3 py-2 bg-white border shadow-sm">
                        <div class="rounded-3 d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width:42px;height:42px;background:#0f3d57;"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="small text-secondary"><?php echo htmlspecialchars($currentUserRole, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <form method="post" action="<?php echo htmlspecialchars(app_url('/logout'), ENT_QUOTES, 'UTF-8'); ?>" class="ms-2">
                            <button type="submit" class="btn btn-light erp-btn erp-btn-sm">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success shadow-sm mb-0"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-danger shadow-sm mb-0"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</header>
