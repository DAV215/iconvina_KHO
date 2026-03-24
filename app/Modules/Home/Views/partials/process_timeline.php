<?php
$processTitle = $processTitle ?? 'Theo dõi quy trình';
$processSubtitle = $processSubtitle ?? '';
$processSteps = $processSteps ?? [];
$stateLabels = [
    'completed' => 'Hoàn tất',
    'current' => 'Đang xử lý',
    'pending' => 'Chưa tới',
    'cancelled' => 'Đã hủy',
];
$stateIcons = [
    'completed' => 'check-lg',
    'current' => 'hourglass-split',
    'pending' => 'circle',
    'cancelled' => 'x-lg',
];
?>
<?php if ($processSteps !== []): ?>
    <div class="erp-card erp-process-card p-3 p-xl-4 mb-4" data-process-tracking>
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
            <div>
                <div class="erp-detail-section__eyebrow">Tracking quy trình</div>
                <h3 class="erp-detail-section__title"><?php echo htmlspecialchars((string) $processTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
            </div>
            <?php if ($processSubtitle !== ''): ?>
                <div class="text-secondary small"><?php echo htmlspecialchars((string) $processSubtitle, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </div>
        <div class="erp-process-track">
            <?php foreach ($processSteps as $step): ?>
                <?php
                $state = (string) ($step['state'] ?? 'pending');
                $anchor = (string) ($step['anchor'] ?? '');
                $icon = (string) ($step['icon'] ?? ($stateIcons[$state] ?? 'circle'));
                $badgeLabel = (string) ($step['badge'] ?? ($stateLabels[$state] ?? 'Chưa cập nhật'));
                $tagName = $anchor !== '' ? 'a' : 'div';
                ?>
                <<?php echo $tagName; ?>
                    class="erp-process-step is-<?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php if ($anchor !== ''): ?>href="#<?php echo htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8'); ?>" data-process-anchor="<?php echo htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>
                >
                    <span class="erp-process-step__head">
                        <span class="erp-process-step__dot"><i class="bi bi-<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                        <span class="erp-process-step__badge"><?php echo htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                    <span class="erp-process-step__body">
                        <span class="erp-process-step__title"><?php echo htmlspecialchars((string) ($step['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (($step['time'] ?? '') !== ''): ?><span class="erp-process-step__time"><?php echo htmlspecialchars((string) $step['time'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        <?php if (($step['note'] ?? '') !== ''): ?><span class="erp-process-step__note"><?php echo htmlspecialchars((string) $step['note'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                    </span>
                </<?php echo $tagName; ?>>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
