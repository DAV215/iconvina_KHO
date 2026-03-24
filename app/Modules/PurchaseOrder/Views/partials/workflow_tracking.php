<?php
$processTitle = $processTitle ?? 'Quy trình đơn mua hàng';
$processSubtitle = $processSubtitle ?? 'Theo dõi tiến độ xử lý đơn mua hàng.';
$processSteps = $trackingSteps ?? ($processSteps ?? []);
include base_path('app/Modules/Home/Views/partials/process_timeline.php');
