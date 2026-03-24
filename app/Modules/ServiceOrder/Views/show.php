<?php
$activeSidebar = $activeSidebar ?? 'service-orders';
$serviceOrder = $serviceOrder ?? [];
$status = (string) ($status ?? '');
$users = $users ?? [];
$statusMap = [
    'created' => ['Tạo lệnh dịch vụ thành công.', 'success'],
    'updated' => ['Cập nhật lệnh dịch vụ thành công.', 'success'],
];
$logs = $serviceOrder['logs'] ?? [];
$actions = $serviceOrder['available_actions'] ?? [];
$canAssign = !empty($actions['can_assign']);
$canStart = !empty($actions['can_start']);
$canComplete = !empty($actions['can_complete']);
$canCancel = !empty($actions['can_cancel']);
$canViewLog = !empty($actions['can_view_log']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết lệnh dịch vụ - ICONVINA ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style><?php require base_path('app/Modules/Home/Views/partials/theme.css'); ?> .service-log-table th,.service-log-table td{font-size:12.75px;vertical-align:top}</style>
</head>
<body>
<div class="erp-shell d-flex">
    <?php include base_path('app/Modules/Home/Views/partials/sidebar.php'); ?>
    <main class="erp-main flex-grow-1">
        <?php include base_path('app/Modules/Home/Views/partials/header.php'); ?>
        <section class="erp-page-section">
            <div class="container-fluid px-4 px-xl-5">
                <?php if ($success = get_flash('success')): ?><div class="alert alert-success rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if ($error = get_flash('error')): ?><div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                <?php if (isset($statusMap[$status])): ?><div class="alert alert-<?php echo htmlspecialchars((string) ($statusMap[$status][1] ?? 'success'), ENT_QUOTES, 'UTF-8'); ?> rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) ($statusMap[$status][0] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <div class="erp-detail-section__eyebrow">Dịch vụ / Work Order</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                            <h2 class="h4 fw-semibold mb-0"><?php echo htmlspecialchars((string) ($serviceOrder['code'] ?? 'SVO'), ENT_QUOTES, 'UTF-8'); ?></h2>
                            <span class="badge text-bg-<?php echo htmlspecialchars((string) ($serviceOrder['status_badge'] ?? 'secondary'), ENT_QUOTES, 'UTF-8'); ?> px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($serviceOrder['status_label'] ?? 'Nháp'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge text-bg-secondary px-3 py-2 rounded-pill"><?php echo htmlspecialchars((string) ($serviceOrder['priority_label'] ?? 'Bình thường'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="text-secondary"><?php echo htmlspecialchars((string) ($serviceOrder['service_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo htmlspecialchars(app_url('/service-orders'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                        <?php if (!empty($serviceOrder['sales_order_url'])): ?><a href="<?php echo htmlspecialchars((string) $serviceOrder['sales_order_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary rounded-4 px-4">Xem Sales Order</a><?php endif; ?>
                        <?php if ($canStart): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/service-orders/start?id=' . (int) ($serviceOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bắt đầu thực hiện lệnh dịch vụ này?');">
                                <button type="submit" class="btn btn-outline-dark rounded-4 px-4">Bắt đầu</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($canComplete): ?>
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/service-orders/complete?id=' . (int) ($serviceOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Xác nhận hoàn thành lệnh dịch vụ này?');">
                                <button type="submit" class="btn btn-dark rounded-4 px-4">Hoàn thành</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($serviceOrder['status_warning'])): ?>
                    <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars((string) $serviceOrder['status_warning'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <div class="accordion d-grid gap-4" id="serviceOrderAccordion">
                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#serviceOrderInfoCollapse" aria-expanded="true">Thông tin lệnh</button></h2>
                        <div id="serviceOrderInfoCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4"><strong>Sales Order:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['sales_order_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Dòng đơn:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['sales_order_line_no'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Khách hàng:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['customer_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-8"><strong>Tiêu đề:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['title'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Số lượng:</strong> <?php echo number_format((float) ($serviceOrder['quantity'] ?? 0), 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#serviceOrderWorkCollapse" aria-expanded="true">Công việc cần làm</button></h2>
                        <div id="serviceOrderWorkCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="mb-3"><strong>Tên dịch vụ:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['service_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="mb-3"><strong>Mô tả công việc:</strong> <span class="text-secondary"><?php echo nl2br(htmlspecialchars((string) ($serviceOrder['work_description'] ?? 'Chưa có mô tả.'), ENT_QUOTES, 'UTF-8')); ?></span></div>
                                <div><strong>Ghi chú nội bộ:</strong> <span class="text-secondary"><?php echo nl2br(htmlspecialchars((string) ($serviceOrder['internal_note'] ?? 'Chưa có ghi chú nội bộ.'), ENT_QUOTES, 'UTF-8')); ?></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item erp-card border-0">
                        <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#serviceOrderAssignmentCollapse" aria-expanded="true">Người phụ trách / Deadline</button></h2>
                        <div id="serviceOrderAssignmentCollapse" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-4"><strong>Người phụ trách:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['assigned_name'] ?? 'Chưa phân công'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Bắt đầu kế hoạch:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['planned_start_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Hạn hoàn thành:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['planned_end_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Bắt đầu thực tế:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['actual_start_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Hoàn thành thực tế:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['actual_end_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="col-12 col-md-4"><strong>Tạo bởi:</strong> <?php echo htmlspecialchars((string) ($serviceOrder['created_by_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <?php if ($canAssign): ?>
                                    <div class="border rounded-4 p-3">
                                        <div class="small text-uppercase text-secondary fw-semibold mb-3">Giao việc</div>
                                        <form method="post" action="<?php echo htmlspecialchars(app_url('/service-orders/assign?id=' . (int) ($serviceOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="row g-3">
                                            <div class="col-12 col-lg-4">
                                                <label class="form-label fw-semibold">Người phụ trách</label>
                                                <select name="assigned_to" class="form-select">
                                                    <option value="">Chọn nhân sự</option>
                                                    <?php foreach ($users as $user): ?>
                                                        <option value="<?php echo (int) ($user['id'] ?? 0); ?>" <?php echo (int) ($serviceOrder['assigned_to'] ?? 0) === (int) ($user['id'] ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) ((($user['full_name'] ?? '') !== '' ? $user['full_name'] : ($user['username'] ?? '')) . ' (' . ($user['username'] ?? '-') . ')'), ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12 col-lg-4">
                                                <label class="form-label fw-semibold">Bắt đầu kế hoạch</label>
                                                <input type="datetime-local" name="planned_start_at" class="form-control" value="<?php echo htmlspecialchars(!empty($serviceOrder['planned_start_at']) ? date('Y-m-d\TH:i', strtotime((string) $serviceOrder['planned_start_at'])) : '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="col-12 col-lg-4">
                                                <label class="form-label fw-semibold">Hạn hoàn thành</label>
                                                <input type="datetime-local" name="planned_end_at" class="form-control" value="<?php echo htmlspecialchars(!empty($serviceOrder['planned_end_at']) ? date('Y-m-d\TH:i', strtotime((string) $serviceOrder['planned_end_at'])) : '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Ghi chú nội bộ</label>
                                                <textarea name="internal_note" rows="3" class="form-control"><?php echo htmlspecialchars((string) ($serviceOrder['internal_note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            </div>
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-dark btn-sm px-4">Giao việc</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($canCancel): ?>
                        <div class="d-flex justify-content-end">
                            <form method="post" action="<?php echo htmlspecialchars(app_url('/service-orders/cancel?id=' . (int) ($serviceOrder['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Hủy lệnh dịch vụ này?');">
                                <button type="submit" class="btn btn-outline-danger rounded-4 px-4">Hủy lệnh</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($canViewLog): ?>
                        <div class="accordion-item erp-card border-0">
                            <h2 class="accordion-header"><button class="accordion-button rounded-top-4" type="button" data-bs-toggle="collapse" data-bs-target="#serviceOrderLogCollapse" aria-expanded="false">Nhật ký hoạt động</button></h2>
                            <div id="serviceOrderLogCollapse" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table service-log-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Thời gian</th>
                                                    <th>Hành động</th>
                                                    <th>Từ</th>
                                                    <th>Sang</th>
                                                    <th>Ghi chú</th>
                                                    <th>Người thực hiện</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if ($logs === []): ?>
                                                <tr><td colspan="6" class="text-center text-secondary py-4">Chưa có nhật ký.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($logs as $log): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars((string) ($log['acted_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['action'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['old_status'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['new_status'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) ($log['remark'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars((string) (($log['acted_by_name'] ?? '') !== '' ? $log['acted_by_name'] : ($log['acted_by_username'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
