<?php
$activeSidebar = $activeSidebar ?? 'bom';
$pageTitle = $pageTitle ?? 'Cây BOM';
$pageEyebrow = $pageEyebrow ?? 'Cấu trúc BOM';
$bom = $bom ?? [];
$tree = $tree ?? [];

$renderNode = static function (array $node, int $depth = 0) use (&$renderNode): void {
    $children = $node['children'] ?? [];
    $hasChildren = $children !== [];
    $collapseId = 'bom-node-' . $depth . '-' . md5((string) ($node['node_type'] ?? '') . '-' . (string) ($node['id'] ?? '') . '-' . (string) ($node['bom_id'] ?? ''));
    ?>
    <div class="erp-bom-node">
        <div class="erp-tree-row">
            <?php if ($hasChildren): ?>
                <button type="button" class="erp-tree-toggle" data-tree-toggle="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="true">
                    <i class="bi bi-dash-lg"></i>
                </button>
            <?php else: ?>
                <span style="width:34px;min-width:34px;"></span>
            <?php endif; ?>
            <div class="erp-bom-card flex-grow-1">
                <div class="d-flex flex-column flex-xl-row gap-3">
                    <div class="d-flex gap-3 flex-grow-1 min-w-0">
                        <div class="erp-bom-thumb">
                            <?php if (!empty($node['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars((string) $node['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) ($node['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php else: ?>
                                <div class="erp-bom-thumb erp-bom-thumb--placeholder border-0 w-100 h-100">
                                    <i class="bi bi-<?php echo ($node['node_type'] ?? '') === 'component' ? 'boxes' : 'box-seam'; ?> fs-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="erp-code-badge"><?php echo htmlspecialchars((string) ($node['code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="erp-tree-chip"><?php echo htmlspecialchars((string) ($node['node_type'] ?? 'node'), ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if (!empty($node['version'])): ?><span class="erp-tree-chip">Phiên bản: <?php echo htmlspecialchars((string) $node['version'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                            </div>
                            <div class="fw-bold mb-1"><?php echo htmlspecialchars((string) ($node['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php if (!empty($node['note'])): ?>
                                <div class="erp-inline-note"><?php echo nl2br(htmlspecialchars((string) $node['note'], ENT_QUOTES, 'UTF-8')); ?></div>
                            <?php else: ?>
                                <div class="erp-inline-note">Không có ghi chú bổ sung.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-start justify-content-xl-end">
                        <span class="erp-tree-chip">Số lượng: <?php echo htmlspecialchars((string) ($node['quantity'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if ($hasChildren): ?>
                            <span class="erp-tree-chip">Nhánh con: <?php echo count($children); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($hasChildren): ?>
            <div class="erp-bom-branch mt-3" data-tree-children="<?php echo htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach ($children as $child): ?>
                    <?php $renderNode($child, $depth + 1); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
};
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
        <section class="erp-page-section">
            <div class="container-fluid px-3 px-lg-4 px-xl-5">
                <div class="erp-card p-3 p-lg-4 p-xl-5">
                    <div class="erp-toolbar mb-4">
                        <div class="erp-toolbar__meta">
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Cấu trúc BOM</div>
                            <h3 class="h4 fw-bold mb-1"><?php echo htmlspecialchars((string) ($bom['bom_name'] ?? 'BOM'), ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="erp-inline-note">Mã bán thành phẩm: <?php echo htmlspecialchars((string) ($bom['component_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="erp-toolbar__actions">
                            <a href="<?php echo htmlspecialchars(app_url('/bom/show?id=' . (int) ($bom['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light erp-btn px-4">Quay lại</a>
                            <a href="<?php echo htmlspecialchars(app_url('/bom/edit?id=' . (int) ($bom['id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-pencil-square"></i>Chỉnh sửa BOM</a>
                        </div>
                    </div>

                    <div class="erp-tree-panel">
                        <?php if ($tree === []): ?>
                            <div class="text-center text-secondary py-5">Chưa có dữ liệu BOM.</div>
                        <?php else: ?>
                            <?php $renderNode($tree, 0); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    document.querySelectorAll('[data-tree-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.treeToggle;
            const branch = document.querySelector('[data-tree-children="' + id + '"]');
            if (!branch) {
                return;
            }
            const collapsed = branch.classList.toggle('is-collapsed');
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            button.innerHTML = collapsed ? '<i class="bi bi-plus-lg"></i>' : '<i class="bi bi-dash-lg"></i>';
        });
    });
})();
</script>
</body>
</html>
