<?php
$activeSidebar = $activeSidebar ?? 'permissions';
$pageTitle = $pageTitle ?? 'Phân quyền';
$role = $role ?? [];
$modules = $modules ?? [];
$actions = $actions ?? [];
$matrix = $matrix ?? [];
$labels = ['customer' => 'Khách hàng', 'supplier' => 'Nhà cung cấp', 'quotation' => 'Báo giá', 'material' => 'Vật tư', 'component' => 'Bán thành phẩm', 'bom' => 'BOM', 'purchase_order' => 'Đơn mua hàng cũ', 'po' => 'PO workflow', 'sales_order' => 'Đơn bán hàng', 'stock' => 'Kho', 'production' => 'Sản xuất', 'user' => 'Người dùng'];
$actionLabels = ['view' => 'Xem', 'create' => 'Tạo', 'update' => 'Sửa', 'delete' => 'Xóa', 'approve' => 'Duyệt', 'confirm' => 'Xác nhận', 'assign' => 'Phân công', 'complete' => 'Hoàn tất', 'submit' => 'Trình duyệt', 'reject' => 'Từ chối', 'cancel' => 'Hủy', 'receive' => 'Nhận hàng', 'receive_partial' => 'Nhận một phần', 'receive_full' => 'Nhận đủ', 'add_extra_cost' => 'Chi phí khác', 'submit_stock_in' => 'Trình nhập kho', 'stock_in_approve' => 'Duyệt nhập kho', 'close' => 'Đóng', 'view_log' => 'Xem log'];
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
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4"><div><div class="text-uppercase small fw-semibold text-secondary mb-2">Nhân sự</div><h3 class="h4 mb-1 fw-semibold">Phân quyền vai trò</h3><div class="text-secondary"><?php echo htmlspecialchars((string) ($role['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div></div><div class="d-flex gap-2"><a href="<?php echo htmlspecialchars(app_url('/roles'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a><a href="<?php echo htmlspecialchars(app_url('/roles/edit?id=' . (int) ($role['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark rounded-4 px-4">Sửa vai trò</a></div></div>
                    <form method="post" action="<?php echo htmlspecialchars(app_url('/roles/permissions?id=' . (int) ($role['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="erp-table-shell p-2 p-lg-3"><div class="erp-table-wrap"><table class="table erp-table align-middle"><thead><tr><th>Module</th><th class="text-center" style="width: 150px;">Thao tác</th><?php foreach ($actions as $action): ?><th class="text-center"><?php echo htmlspecialchars((string) ($actionLabels[$action] ?? $action), ENT_QUOTES, 'UTF-8'); ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($modules as $module): ?><tr data-permission-row><td class="fw-semibold"><?php echo htmlspecialchars((string) ($labels[$module] ?? $module), ENT_QUOTES, 'UTF-8'); ?></td><td class="text-center"><div class="d-inline-flex gap-2"><button type="button" class="btn btn-light erp-btn-sm" data-row-check-all>Chọn hết</button><button type="button" class="btn btn-light erp-btn-sm" data-row-uncheck-all>Bỏ hết</button></div></td><?php foreach ($actions as $action): $cell = $matrix[$module][$action] ?? null; ?><td class="text-center"><?php if ($cell !== null): ?><div class="form-check d-inline-flex justify-content-center m-0"><input class="form-check-input" type="checkbox" name="permission_ids[]" value="<?php echo (int) $cell['id']; ?>" <?php echo !empty($cell['checked']) ? 'checked' : ''; ?>></div><?php else: ?><span class="text-secondary">-</span><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div></div>
                        <div class="d-flex justify-content-end gap-2 mt-4"><a href="<?php echo htmlspecialchars(app_url('/roles'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a><button type="submit" class="btn btn-dark rounded-4 px-4">Lưu phân quyền</button></div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const initPermissionMatrixActions = () => {
        document.querySelectorAll('[data-permission-row]').forEach((row) => {
            const checkboxes = Array.from(row.querySelectorAll('input[type="checkbox"][name="permission_ids[]"]'));
            const checkAllButton = row.querySelector('[data-row-check-all]');
            const uncheckAllButton = row.querySelector('[data-row-uncheck-all]');

            if (checkAllButton) {
                checkAllButton.addEventListener('click', () => {
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = true;
                    });
                });
            }

            if (uncheckAllButton) {
                uncheckAllButton.addEventListener('click', () => {
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                });
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPermissionMatrixActions, { once: true });
    } else {
        initPermissionMatrixActions();
    }
})();
</script>
</body>
</html>
