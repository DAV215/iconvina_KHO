<?php
$activeSidebar = $activeSidebar ?? 'service-orders';
$filters = $filters ?? ['search' => '', 'status' => '', 'mine' => false];
$serviceOrders = $serviceOrders ?? [];
$statuses = $statuses ?? [];
$pagination = $pagination ?? ['per_page' => 25];
$perPage = (int) ($pagination['per_page'] ?? 25);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lệnh dịch vụ - ICONVINA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?> .service-table th,.service-table td{font-size:12.75px;vertical-align:top}</style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-4 px-xl-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <div class="erp-detail-section__eyebrow">Dịch vụ / Thực hiện</div>
                        <h2 class="h4 fw-semibold mb-1">Lệnh dịch vụ</h2>
                        <div class="text-secondary">Theo dõi các công việc dịch vụ phát sinh từ Sales Order.</div>
                    </div>
                </div>

                <div class="erp-card p-3 p-lg-4 mb-4">
                    <form method="get" action="<?php echo htmlspecialchars(app_url('/service-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3">
                        <div class="col-12 col-lg-5">
                            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars((string) ($filters['search'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm mã lệnh, tên dịch vụ, đơn bán, khách hàng">
                        </div>
                        <div class="col-12 col-lg-3">
                            <select name="status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($filters['status'] ?? '') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $status)), ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-2 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="serviceOrderMineCheckbox" name="mine" <?php echo !empty($filters['mine']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="serviceOrderMineCheckbox">Việc của tôi</label>
                            </div>
                        </div>
                        <div class="col-12 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-dark w-100">Lọc</button>
                        </div>
                    </form>
                </div>

                <div class="erp-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table service-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mã lệnh</th>
                                    <th>Dịch vụ</th>
                                    <th>Đơn bán</th>
                                    <th>Khách hàng</th>
                                    <th>Người phụ trách</th>
                                    <th>Deadline</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($serviceOrders === []): ?>
                                <tr><td colspan="8" class="text-center text-secondary py-5">Chưa có lệnh dịch vụ.</td></tr>
                            <?php else: ?>
                                <?php foreach ($serviceOrders as $serviceOrder): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars((string) ($serviceOrder['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string) ($serviceOrder['service_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="small text-secondary"><?php echo htmlspecialchars((string) ($serviceOrder['title'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) ($serviceOrder['sales_order_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($serviceOrder['customer_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($serviceOrder['assigned_name'] ?? 'Chưa phân công'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($serviceOrder['planned_end_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="badge text-bg-<?php echo htmlspecialchars((string) ($serviceOrder['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> rounded-pill"><?php echo htmlspecialchars((string) ($serviceOrder['status_label'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="text-end"><a href="<?php echo htmlspecialchars(app_url('/service-orders/show?id=' . (int) ($serviceOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-light">Xem</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php include base_path('app/Modules/Home/Views/partials/list_pagination.php'); ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
