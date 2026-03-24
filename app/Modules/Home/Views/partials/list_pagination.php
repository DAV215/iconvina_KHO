<?php
$pagination = $pagination ?? null;
if (!is_array($pagination) || (int) ($pagination['total_pages'] ?? 1) <= 1) {
    return;
}
?>
<div class="erp-pagination mt-4">
    <div class="erp-pagination__summary">
        Hiển thị <?php echo (int) ($pagination['from'] ?? 0); ?>-<?php echo (int) ($pagination['to'] ?? 0); ?>
        / <?php echo (int) ($pagination['total_items'] ?? 0); ?> bản ghi
    </div>
    <nav aria-label="Điều hướng phân trang">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?php echo !empty($pagination['has_prev']) ? '' : 'disabled'; ?>">
                <a class="page-link" href="<?php echo htmlspecialchars((string) ($pagination['prev_url'] ?? '#'), ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($pagination['has_prev']) ? '' : 'tabindex="-1" aria-disabled="true"'; ?>>Trước</a>
            </li>
            <?php foreach (($pagination['pages'] ?? []) as $pageItem): ?>
                <li class="page-item <?php echo !empty($pageItem['is_current']) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo htmlspecialchars((string) $pageItem['url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo (int) $pageItem['number']; ?></a>
                </li>
            <?php endforeach; ?>
            <li class="page-item <?php echo !empty($pagination['has_next']) ? '' : 'disabled'; ?>">
                <a class="page-link" href="<?php echo htmlspecialchars((string) ($pagination['next_url'] ?? '#'), ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($pagination['has_next']) ? '' : 'tabindex="-1" aria-disabled="true"'; ?>>Sau</a>
            </li>
        </ul>
    </nav>
</div>
