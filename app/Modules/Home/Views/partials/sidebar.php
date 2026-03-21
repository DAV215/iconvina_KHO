<?php
$activeSidebar = $activeSidebar ?? 'dashboard';
$sidebarItems = [
    ['key' => 'login', 'label' => 'Tổng quan', 'icon' => 'grid', 'href' => app_url('/')],
    ['key' => 'customers', 'label' => 'Khách hàng', 'icon' => 'people', 'href' => app_url('/customers')],
    ['key' => 'materials', 'label' => 'Nguyên vật liệu', 'icon' => 'box-seam', 'href' => app_url('/materials')],
    ['key' => 'components', 'label' => 'Bán thành phẩm', 'icon' => 'boxes', 'href' => app_url('/components')],
    ['key' => 'bom', 'label' => 'BOM', 'icon' => 'diagram-3', 'href' => app_url('/bom')],
    ['key' => 'quotations', 'label' => 'Báo giá', 'icon' => 'file-earmark-text', 'href' => app_url('/quotations')],
    ['key' => 'orders', 'label' => 'Đơn hàng', 'icon' => 'receipt', 'href' => app_url('/orders')],
    ['key' => 'inventory', 'label' => 'Kho', 'icon' => 'archive', 'href' => app_url('/stocks')],
    ['key' => 'production', 'label' => 'Sản xuất', 'icon' => 'gear-wide-connected', 'href' => app_url('/')],
];
?>
<aside class="erp-sidebar border-end bg-white position-fixed top-0 start-0 h-100">
    <div class="erp-sidebar__brand d-flex align-items-center justify-content-between px-4 py-4 border-bottom">
        <div>
            <div class="erp-eyebrow text-uppercase fw-semibold text-secondary">ICONVINA</div>
            <h1 class="erp-brand-title h5 mb-0">ERP sản xuất</h1>
        </div>
        <button class="btn btn-light d-lg-none rounded-3 shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#erpMobileNav" aria-label="Mở menu">
            <i class="bi bi-list fs-5"></i>
        </button>
    </div>

    <div class="erp-sidebar__section px-3 py-4">
        <div class="small text-uppercase text-secondary fw-semibold px-3 mb-3">Điều hướng</div>
        <nav class="nav flex-column gap-2">
            <?php foreach ($sidebarItems as $item): ?>
                <a class="erp-nav-link nav-link d-flex align-items-center gap-3 px-3 py-3 rounded-4 <?php echo $activeSidebar === $item['key'] ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="erp-nav-link__icon d-inline-flex align-items-center justify-content-center rounded-3">
                        <i class="bi bi-<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    </span>
                    <span class="fw-medium"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="erp-sidebar__footer px-4 pb-4 mt-auto position-absolute bottom-0 start-0 end-0">
        <div class="erp-sidebar-note rounded-4 p-3">
            <div class="small text-secondary mb-1">Sản xuất hôm nay</div>
            <div class="fw-semibold mb-2">18 lệnh đang chạy</div>
            <div class="progress erp-progress" role="progressbar" aria-label="Tiến độ sản xuất" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 72%"></div>
            </div>
        </div>
    </div>
</aside>

<div class="offcanvas offcanvas-start" tabindex="-1" id="erpMobileNav" aria-labelledby="erpMobileNavLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="erpMobileNavLabel">ICONVINA ERP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body p-3">
        <nav class="nav flex-column gap-2">
            <?php foreach ($sidebarItems as $item): ?>
                <a class="erp-nav-link nav-link d-flex align-items-center gap-3 px-3 py-3 rounded-4 <?php echo $activeSidebar === $item['key'] ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="erp-nav-link__icon d-inline-flex align-items-center justify-content-center rounded-3">
                        <i class="bi bi-<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    </span>
                    <span class="fw-medium"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>
