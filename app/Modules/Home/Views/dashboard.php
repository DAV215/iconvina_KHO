<?php
$kpis = [
    ['label' => 'Khách hàng đang hoạt động', 'value' => '248', 'delta' => '+12% trong tháng', 'icon' => 'people'],
    ['label' => 'Báo giá đang mở', 'value' => '64', 'delta' => '18 báo giá chờ duyệt', 'icon' => 'file-earmark-text'],
    ['label' => 'Đơn hàng đang xử lý', 'value' => '31', 'delta' => '7 đơn cần giao gấp', 'icon' => 'receipt'],
    ['label' => 'Mức bao phủ vật tư', 'value' => '92%', 'delta' => 'Tồn kho đang ổn định', 'icon' => 'boxes'],
];

$activities = [
    ['title' => 'Đơn hàng bảng hiệu neon đã được duyệt', 'meta' => 'Đơn SO-2026-0412 đã chuyển sang sản xuất', 'time' => '5 phút trước'],
    ['title' => 'Bản cập nhật báo giá đã được gửi', 'meta' => 'QTN-2026-0198 đã cập nhật cho Minh Long Pharma', 'time' => '22 phút trước'],
    ['title' => 'Cảnh báo tồn kho thấp', 'meta' => 'LED module 3 bóng đã xuống dưới mức đặt hàng', 'time' => '48 phút trước'],
    ['title' => 'Hoàn thành mốc xưởng', 'meta' => 'Khung hộp đèn mica đã hoàn tất 100% lắp ráp', 'time' => '1 giờ trước'],
];

$quickActions = [
    ['label' => 'Thêm khách hàng', 'icon' => 'person-plus'],
    ['label' => 'Tạo báo giá', 'icon' => 'file-plus'],
    ['label' => 'Tạo đơn hàng', 'icon' => 'bag-plus'],
    ['label' => 'Xuất vật tư', 'icon' => 'box-arrow-right'],
];

$workflow = [
    ['label' => 'Khách hàng tiềm năng sang báo giá', 'value' => '14'],
    ['label' => 'Báo giá sang đơn hàng', 'value' => '09'],
    ['label' => 'Đơn hàng sang sản xuất', 'value' => '27'],
    ['label' => 'Sản xuất sang giao hàng', 'value' => '11'],
];

$chartBars = [52, 68, 74, 63, 81, 77, 89, 71, 66, 84, 79, 92];
?>
<section class="erp-dashboard py-4 py-xl-5">
    <div class="container-fluid px-4 px-xl-5">
        <div class="row g-4 mb-4">
            <?php foreach ($kpis as $kpi): ?>
                <div class="col-12 col-md-6 col-xxl-3">
                    <section class="erp-card erp-kpi-card h-100 p-4">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div>
                                <div class="text-secondary small fw-semibold text-uppercase mb-2"><?php echo htmlspecialchars($kpi['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="display-6 fw-semibold text-dark mb-1"><?php echo htmlspecialchars($kpi['value'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="small text-success-emphasis"><?php echo htmlspecialchars($kpi['delta'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="erp-stat-icon rounded-4 d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-<?php echo htmlspecialchars($kpi['icon'], ENT_QUOTES, 'UTF-8'); ?> fs-4"></i>
                            </div>
                        </div>
                        <div class="erp-kpi-line"></div>
                    </section>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-8">
                <section class="erp-card p-4 p-xl-5 h-100">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Biểu đồ / báo cáo</div>
                            <h3 class="h4 mb-0 fw-semibold">Doanh thu và lưu lượng công việc theo tháng</h3>
                        </div>
                        <div class="btn-group shadow-sm" role="group" aria-label="Bộ lọc biểu đồ">
                            <button type="button" class="btn btn-dark">12 tháng</button>
                            <button type="button" class="btn btn-outline-secondary bg-white">Quý</button>
                            <button type="button" class="btn btn-outline-secondary bg-white">Tháng</button>
                        </div>
                    </div>

                    <div class="erp-chart-shell rounded-4 p-4">
                        <div class="row align-items-end g-3 erp-chart-bars">
                            <?php foreach ($chartBars as $index => $height): ?>
                                <div class="col">
                                    <div class="erp-chart-bar-wrap text-center">
                                        <div class="erp-chart-bar mx-auto rounded-top-4" style="height: <?php echo (int) $height; ?>%;"></div>
                                        <div class="small text-secondary mt-2">T<?php echo $index + 1; ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-4">
                <div class="row g-4 h-100">
                    <div class="col-12">
                        <section class="erp-card p-4 h-100">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Thao tác nhanh</div>
                            <h3 class="h5 fw-semibold mb-4">Bắt đầu công việc thường dùng</h3>
                            <div class="row g-3">
                                <?php foreach ($quickActions as $action): ?>
                                    <div class="col-6">
                                        <button class="erp-action-tile btn btn-light w-100 rounded-4 p-3 text-start shadow-sm border-0">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 erp-action-tile__icon mb-3">
                                                <i class="bi bi-<?php echo htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                                            </span>
                                            <span class="d-block fw-semibold text-dark"><?php echo htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    <div class="col-12">
                        <section class="erp-card p-4 h-100">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Tóm tắt luồng công việc</div>
                            <h3 class="h5 fw-semibold mb-4">Tình trạng pipeline</h3>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($workflow as $step): ?>
                                    <div class="d-flex align-items-center justify-content-between rounded-4 px-3 py-3 bg-light-subtle">
                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="badge rounded-pill text-bg-dark px-3 py-2"><?php echo htmlspecialchars($step['value'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <section class="erp-card p-4 p-xl-5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Hoạt động gần đây</div>
                            <h3 class="h4 mb-0 fw-semibold">Dòng thời gian vận hành</h3>
                        </div>
                        <a href="#" class="btn btn-outline-secondary rounded-4 px-3">Xem tất cả</a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($activities as $activity): ?>
                            <article class="erp-activity-item d-flex align-items-start gap-3 rounded-4 p-3 p-xl-4">
                                <div class="erp-activity-dot rounded-circle mt-1"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark mb-1"><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-secondary mb-1"><?php echo htmlspecialchars($activity['meta'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="small text-secondary-emphasis"><?php echo htmlspecialchars($activity['time'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="erp-card p-4 p-xl-5 h-100">
                    <div class="text-uppercase small fw-semibold text-secondary mb-2">Sức khỏe vận hành</div>
                    <h3 class="h4 fw-semibold mb-4">Tổng quan hôm nay</h3>

                    <div class="erp-overview-panel rounded-4 p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-semibold text-dark">Tỷ lệ giao hàng đúng hẹn</span>
                            <span class="fw-semibold text-dark">94%</span>
                        </div>
                        <div class="progress erp-progress mb-3" role="progressbar" aria-label="Tỷ lệ giao hàng đúng hẹn" aria-valuenow="94" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 94%"></div>
                        </div>
                        <div class="small text-secondary">Đội xưởng và lắp đặt đang bám sát tiến độ đã cam kết.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="erp-metric-box rounded-4 p-4 h-100">
                                <div class="small text-secondary text-uppercase fw-semibold mb-2">Chờ phê duyệt</div>
                                <div class="h3 mb-1 fw-semibold">12</div>
                                <div class="small text-secondary">Cần rà soát thương mại</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="erp-metric-box rounded-4 p-4 h-100">
                                <div class="small text-secondary text-uppercase fw-semibold mb-2">Mã tồn thấp</div>
                                <div class="h3 mb-1 fw-semibold">07</div>
                                <div class="small text-secondary">Cần lên kế hoạch mua</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="erp-metric-box rounded-4 p-4 h-100">
                                <div class="small text-secondary text-uppercase fw-semibold mb-2">Công nợ mở</div>
                                <div class="h3 mb-1 fw-semibold">1.28B</div>
                                <div class="small text-secondary">VNĐ chưa thu</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="erp-metric-box rounded-4 p-4 h-100">
                                <div class="small text-secondary text-uppercase fw-semibold mb-2">Tải xưởng</div>
                                <div class="h3 mb-1 fw-semibold">81%</div>
                                <div class="small text-secondary">Mức sử dụng đang tốt</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>