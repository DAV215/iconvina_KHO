<?php
$pageEyebrow = $pageEyebrow ?? __('Dashboard overview');
$pageTitle = $pageTitle ?? __('Premium ERP Control Center');
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
$currentLocale = app_locale();
?>
<header class="erp-header sticky-top bg-body-tertiary border-bottom">
    <div class="container-fluid px-4 px-xl-5 py-3">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small fw-semibold text-secondary mb-1"><?php echo htmlspecialchars($pageEyebrow, ENT_QUOTES, 'UTF-8'); ?></div>
                <h2 class="h3 mb-0 fw-semibold text-dark"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
                <div class="erp-search input-group rounded-4 overflow-hidden d-none d-md-flex">
                    <span class="input-group-text bg-white border-0 text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 shadow-none" placeholder="Search customer, quotation, order...">
                </div>

                <div class="btn-group shadow-sm" role="group" aria-label="<?php echo htmlspecialchars(__('common.language'), ENT_QUOTES, 'UTF-8'); ?>">
                    <a href="<?php echo htmlspecialchars(current_url(['lang' => 'vi']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm <?php echo $currentLocale === 'vi' ? 'btn-dark' : 'btn-outline-secondary bg-white'; ?>">VI</a>
                    <a href="<?php echo htmlspecialchars(current_url(['lang' => 'en']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm <?php echo $currentLocale === 'en' ? 'btn-dark' : 'btn-outline-secondary bg-white'; ?>">EN</a>
                </div>

                <button class="btn btn-light rounded-4 shadow-sm position-relative" type="button" aria-label="Thông báo">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </button>

                <div class="erp-user-chip d-flex align-items-center gap-3 rounded-4 px-3 py-2 bg-white shadow-sm">
                    <div class="erp-user-chip__avatar rounded-3 d-inline-flex align-items-center justify-content-center fw-semibold">IV</div>
                    <div class="d-none d-sm-block">
                        <div class="fw-semibold text-dark">ICONVINA Admin</div>
                        <div class="small text-secondary">Operations Director</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success rounded-4 border-0 shadow-sm mt-3 mb-0"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger rounded-4 border-0 shadow-sm mt-3 mb-0"><?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
    </div>
</header>