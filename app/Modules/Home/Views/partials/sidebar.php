<?php
$activeSidebar = $activeSidebar ?? 'dashboard';
$sidebarGroups = [
    [
        'key' => 'overview',
        'label' => 'Tổng quan',
        'icon' => 'speedometer2',
        'items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid', 'href' => app_url('/')],
        ],
    ],
    [
        'key' => 'sales',
        'label' => 'Bán hàng',
        'icon' => 'graph-up-arrow',
        'items' => [
            ['key' => 'customers', 'label' => 'Khách hàng', 'icon' => 'people', 'href' => app_url('/customers'), 'permission' => 'customer.view'],
            ['key' => 'quotations', 'label' => 'Báo giá', 'icon' => 'file-earmark-text', 'href' => app_url('/quotations'), 'permission' => 'quotation.view'],
            ['key' => 'orders', 'label' => 'Đơn bán hàng', 'icon' => 'receipt', 'href' => app_url('/orders'), 'permission' => 'sales_order.view'],
        ],
    ],
    [
        'key' => 'purchase',
        'label' => 'Mua hàng',
        'icon' => 'cart-check',
        'items' => [
            ['key' => 'suppliers', 'label' => 'Nhà cung cấp', 'icon' => 'truck', 'href' => app_url('/suppliers'), 'permission' => 'supplier.view'],
            ['key' => 'purchase-orders', 'label' => 'Đơn mua hàng', 'icon' => 'cart3', 'href' => app_url('/purchase-orders'), 'permission' => 'purchase_order.view'],
        ],
    ],
    [
        'key' => 'organization',
        'label' => 'Tổ chức',
        'icon' => 'building',
        'items' => [
            ['key' => 'companies', 'label' => 'Công ty', 'icon' => 'buildings', 'href' => app_url('/companies')],
            ['key' => 'branches', 'label' => 'Chi nhánh', 'icon' => 'signpost-split', 'href' => app_url('/branches')],
            ['key' => 'departments', 'label' => 'Phòng ban', 'icon' => 'diagram-2', 'href' => app_url('/departments')],
            ['key' => 'positions', 'label' => 'Chức danh', 'icon' => 'person-workspace', 'href' => app_url('/positions')],
        ],
    ],
    [
        'key' => 'inventory',
        'label' => 'Kho',
        'icon' => 'boxes',
        'items' => [
            ['key' => 'materials', 'label' => 'Vật tư', 'icon' => 'box-seam', 'href' => app_url('/materials'), 'permission' => 'material.view'],
            ['key' => 'material-categories', 'label' => 'Danh mục vật tư', 'icon' => 'tags', 'href' => app_url('/material-categories'), 'permission' => 'material_category.view'],
            ['key' => 'components', 'label' => 'Bán thành phẩm', 'icon' => 'box2-heart', 'href' => app_url('/components'), 'permission' => 'component.view'],
            ['key' => 'inventory', 'label' => 'Nhập / Xuất kho', 'icon' => 'archive', 'href' => app_url('/stocks'), 'permission' => 'stock.view'],
            ['key' => 'inventory-balance', 'label' => 'Xem tồn kho', 'icon' => 'clipboard2-data', 'href' => app_url('/inventory/balance'), 'permission' => 'stock.view'],
            ['key' => 'bom', 'label' => 'BOM', 'icon' => 'diagram-3', 'href' => app_url('/bom'), 'permission' => 'bom.view'],
        ],
    ],
    [
        'key' => 'production',
        'label' => 'Sản xuất',
        'icon' => 'gear-wide-connected',
        'items' => [
            ['key' => 'production-orders', 'label' => 'Lệnh sản xuất', 'icon' => 'kanban', 'href' => app_url('/production-orders'), 'permission' => 'production.view'],
            ['key' => 'service-orders', 'label' => 'Lệnh dịch vụ', 'icon' => 'clipboard2-check', 'href' => app_url('/service-orders'), 'permission' => 'service_order.view'],
            ['key' => 'production-steps', 'label' => 'Công đoạn sản xuất', 'icon' => 'bezier2', 'href' => null, 'disabled' => true, 'badge' => 'Sớm có'],
        ],
    ],
    [
        'key' => 'finance',
        'label' => 'Tài chính',
        'icon' => 'wallet2',
        'items' => [
            ['key' => 'receivables', 'label' => 'Công nợ phải thu', 'icon' => 'cash-stack', 'href' => null, 'disabled' => true, 'badge' => 'Sớm có'],
            ['key' => 'payables', 'label' => 'Công nợ phải trả', 'icon' => 'credit-card-2-back', 'href' => null, 'disabled' => true, 'badge' => 'Sớm có'],
        ],
    ],
    [
        'key' => 'hr',
        'label' => 'Nhân sự',
        'icon' => 'person-badge',
        'items' => [
            ['key' => 'users', 'label' => 'Người dùng', 'icon' => 'people', 'href' => app_url('/users'), 'permission' => 'user.view'],
            ['key' => 'roles', 'label' => 'Vai trò', 'icon' => 'shield-lock', 'href' => app_url('/roles'), 'permission' => 'role.view'],
            ['key' => 'permissions', 'label' => 'Phân quyền', 'icon' => 'sliders', 'href' => app_url('/roles'), 'permission' => 'role.view'],
        ],
    ],
];

$renderSidebarGroups = static function (array $groups, string $activeKey): void {
    foreach ($groups as $group) {
        $items = array_values(array_filter(
            $group['items'] ?? [],
            static function (array $item): bool {
                $permission = (string) ($item['permission'] ?? '');

                return $permission === '' || has_permission($permission);
            }
        ));
        if ($items === []) {
            continue;
        }
        $isOpen = false;
        foreach ($items as $item) {
            if (($item['key'] ?? '') === $activeKey) {
                $isOpen = true;
                break;
            }
        }
        ?>
        <details class="erp-sidebar-group" <?php echo $isOpen ? 'open' : ''; ?>>
            <summary class="erp-sidebar-group__summary list-unstyled">
                <span class="erp-sidebar-group__title-wrap">
                    <span class="erp-sidebar-group__icon"><i class="bi bi-<?php echo htmlspecialchars((string) ($group['icon'] ?? 'grid'), ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                    <span>
                        <span class="erp-sidebar-group__eyebrow">Nghiệp vụ</span>
                        <span class="erp-sidebar-group__title"><?php echo htmlspecialchars((string) ($group['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </span>
                <span class="erp-sidebar-group__chevron"><i class="bi bi-chevron-down"></i></span>
            </summary>
            <div class="erp-sidebar-group__body">
                <nav class="nav flex-column gap-2">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $isActive = $activeKey === ($item['key'] ?? '');
                        $isDisabled = (bool) ($item['disabled'] ?? false);
                        $classes = 'erp-nav-link nav-link d-flex align-items-center gap-3 px-3 py-3 rounded-4' . ($isActive ? ' is-active' : '') . ($isDisabled ? ' is-disabled' : '');
                        ?>
                        <?php if ($isDisabled): ?>
                            <span class="<?php echo $classes; ?>">
                                <span class="erp-nav-link__icon d-inline-flex align-items-center justify-content-center rounded-3">
                                    <i class="bi bi-<?php echo htmlspecialchars((string) ($item['icon'] ?? 'dot'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                                </span>
                                <span class="d-flex align-items-center justify-content-between gap-2 flex-grow-1">
                                    <span class="fw-semibold"><?php echo htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (($item['badge'] ?? '') !== ''): ?><span class="erp-nav-badge"><?php echo htmlspecialchars((string) $item['badge'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                                </span>
                            </span>
                        <?php else: ?>
                            <a class="<?php echo $classes; ?>" href="<?php echo htmlspecialchars((string) ($item['href'] ?? '#'), ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="erp-nav-link__icon d-inline-flex align-items-center justify-content-center rounded-3">
                                    <i class="bi bi-<?php echo htmlspecialchars((string) ($item['icon'] ?? 'dot'), ENT_QUOTES, 'UTF-8'); ?>"></i>
                                </span>
                                <span class="d-flex align-items-center justify-content-between gap-2 flex-grow-1">
                                    <span class="fw-semibold"><?php echo htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (($item['badge'] ?? '') !== ''): ?><span class="erp-nav-badge"><?php echo htmlspecialchars((string) $item['badge'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                                </span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </div>
        </details>
        <?php
    }
};
?>
<aside class="erp-sidebar position-fixed top-0 start-0 h-100">
    <div class="erp-sidebar__brand d-flex align-items-start justify-content-between px-4 py-4">
        <div>
            <div class="erp-eyebrow text-uppercase fw-semibold">ICONVINA</div>
            <h1 class="erp-brand-title h4 mb-2 text-white">ERP Sản Xuất</h1>
            <div class="small text-secondary">Điều phối bán hàng, mua hàng, kho và sản xuất trên một luồng vận hành thống nhất.</div>
        </div>
    </div>

    <div class="px-3 pb-4 erp-sidebar__scroll">
        <div class="small text-uppercase fw-semibold px-3 mb-3 text-secondary">Điều hướng nghiệp vụ</div>
        <div class="erp-sidebar-groups">
            <?php $renderSidebarGroups($sidebarGroups, $activeSidebar); ?>
        </div>
    </div>

    <div class="erp-sidebar__footer px-4 pb-4 mt-auto">
        <div class="erp-sidebar-note rounded-4 p-3">
            <div class="small mb-1">Tình hình hôm nay</div>
            <div class="fw-semibold mb-2">18 lệnh sản xuất đang chạy</div>
            <div class="progress erp-progress" role="progressbar" aria-label="Tiến độ sản xuất" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 72%"></div>
            </div>
        </div>
    </div>
</aside>

<div class="offcanvas offcanvas-start" tabindex="-1" id="erpMobileNav" aria-labelledby="erpMobileNavLabel">
    <div class="offcanvas-header border-bottom">
        <div>
            <div class="erp-eyebrow text-uppercase fw-semibold text-secondary">ICONVINA</div>
            <h5 class="offcanvas-title mb-0" id="erpMobileNavLabel">ERP Sản Xuất</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body p-3">
        <div class="erp-sidebar-groups">
            <?php $renderSidebarGroups($sidebarGroups, $activeSidebar); ?>
        </div>
    </div>
</div>
