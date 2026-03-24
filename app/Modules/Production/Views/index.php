<?php
$activeSidebar = $activeSidebar ?? 'production-orders';
$pageTitle = $pageTitle ?? 'Lệnh sản xuất';
$filters = $filters ?? ['search' => '', 'status' => '', 'mine' => false];
$productionOrders = $productionOrders ?? [];
$statuses = $statuses ?? [];
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
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?> body{font-size:13px}.prod-table th,.prod-table td{font-size:12.75px;vertical-align:middle}</style>
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Sản xuất</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách lệnh sản xuất</h3>
                        </div>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars(app_url('/production-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-section-panel p-3 p-lg-4 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-5">
                                <label class="form-label fw-semibold">Tìm kiếm</label>
                                <input type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Mã LSX, mã đơn hàng, mã thành phẩm">
                            </div>
                            <div class="col-12 col-lg-3">
                                <label class="form-label fw-semibold">Trạng thái</label>
                                <select name="status" class="form-select erp-select">
                                    <option value="">Tất cả</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($filters['status'] ?? '') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $status)), ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-2">
                                <label class="form-label fw-semibold">Hiển thị</label>
                                <select name="per_page" class="form-select erp-select">
                                    <?php foreach ([10, 25, 50, 100] as $size): ?>
                                        <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-check mt-4 pt-2">
                                    <input class="form-check-input" type="checkbox" name="mine" value="1" id="mineOnly" <?php echo !empty($filters['mine']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="mineOnly">Việc của tôi</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="<?php echo htmlspecialchars(app_url('/production-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Đặt lại</a>
                            <button type="submit" class="btn btn-dark erp-btn px-4"><i class="bi bi-search"></i>Lọc</button>
                        </div>
                    </form>

                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table prod-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã LSX</th>
                                        <th>Đơn bán</th>
                                        <th>Thành phẩm</th>
                                        <th class="text-end">SL kế hoạch</th>
                                        <th class="text-end">Thiếu hụt</th>
                                        <th>Trạng thái</th>
                                        <th class="text-end">Tiến độ</th>
                                        <th class="text-end">Task</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($productionOrders === []): ?>
                                    <tr><td colspan="9" class="text-center text-secondary py-5">Chưa có lệnh sản xuất.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($productionOrders as $productionOrder): ?>
                                        <tr>
                                            <td><span class="erp-code-badge"><?php echo htmlspecialchars((string) $productionOrder['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo htmlspecialchars((string) ($productionOrder['sales_order_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) ($productionOrder['component_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="text-secondary small"><?php echo htmlspecialchars((string) ($productionOrder['component_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td class="text-end"><?php echo number_format((float) ($productionOrder['planned_qty'] ?? 0), 2); ?></td>
                                            <td class="text-end"><?php echo number_format((float) ($productionOrder['stock_shortage_qty'] ?? 0), 2); ?></td>
                                            <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($productionOrder['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($productionOrder['status_label'] ?? ($productionOrder['status'] ?? 'draft')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-end"><?php echo number_format((float) ($productionOrder['progress_percent'] ?? 0), 0); ?>%</td>
                                            <td class="text-end"><?php echo (int) ($productionOrder['task_done_count'] ?? 0); ?>/<?php echo (int) ($productionOrder['task_count'] ?? 0); ?></td>
                                            <td class="text-end"><a href="<?php echo htmlspecialchars(app_url('/production-orders/show?id=' . (int) $productionOrder['id']), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn-sm">Chi tiết</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
</body>
</html>
