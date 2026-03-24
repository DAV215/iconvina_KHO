<?php
$activeSidebar = $activeSidebar ?? 'bom';
$pageTitle = $pageTitle ?? 'BOM Form';
$formAction = $formAction ?? '/bom/store';
$bom = $bom ?? [];
$components = $components ?? [];
$materials = $materials ?? [];
$childComponents = $childComponents ?? [];
$errors = $errors ?? [];
$items = $bom['items'] ?? [];

if ($items === []) {
    $items = [[
        'item_kind' => 'material',
        'material_id' => '',
        'component_id' => '',
        'quantity' => '',
        'note' => '',
    ]];
}

$field = static function (string $key, string $default = '') use ($bom): string {
    return htmlspecialchars((string) ($bom[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};

$itemErrorFor = static function (int|string $index, string $key) use ($errors): ?string {
    return $errors["items.{$index}.{$key}"][0] ?? null;
};

$selectedComponentId = (string) ($bom['component_id'] ?? '');
$formHeading = trim((string) ($bom['bom_name'] ?? ''));
if ($formHeading === '') {
    $formHeading = (string) $pageTitle;
}
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
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                        <div>
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">BOM module</div>
                            <h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($formHeading, ENT_QUOTES, 'UTF-8'); ?></h3>
                        </div>
                        <a href="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Back</a>
                    </div>

                    <?php if ($errorFor('items')): ?>
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4"><?php echo htmlspecialchars($errorFor('items'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" id="bomForm" class="row g-4" onsubmit="return beforeSubmitBomForm();">
                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="row g-4">
                                    <div class="col-12 col-lg-5">
                                        <label class="form-label fw-semibold">Component</label>
                                        <select name="component_id" id="bomParentRef" class="form-select form-select-lg rounded-4 <?php echo $errorFor('component_id') ? 'is-invalid' : ''; ?>" onchange="syncAllBomRows();">
                                            <option value="">Chọn bán thành phẩm</option>
                                            <?php foreach ($components as $component): ?>
                                                <option value="<?php echo (int) $component['id']; ?>" <?php echo $selectedComponentId === (string) $component['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($errorFor('component_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('component_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Version</label>
                                        <input type="text" name="version" class="form-control form-control-lg rounded-4 <?php echo $errorFor('version') ? 'is-invalid' : ''; ?>" value="<?php echo $field('version'); ?>" maxlength="50">
                                        <?php if ($errorFor('version')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($errorFor('version'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label fw-semibold d-block">Trạng thái</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="bomIsActive" name="is_active" value="1" <?php echo (string) ($bom['is_active'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="bomIsActive">Active BOM</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="erp-card p-4">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                                    <div>
                                        <div class="small text-uppercase text-secondary fw-semibold mb-2">BOM items</div>
                                        <h4 class="h5 mb-0 fw-semibold">Vật tư / bán thành phẩm</h4>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary rounded-4 px-4" onclick="addBomRow();"><i class="bi bi-plus-lg me-2"></i>Thêm dòng</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:72px;">#</th>
                                                <th style="min-width:180px;">Loại</th>
                                                <th style="min-width:280px;">Material</th>
                                                <th style="min-width:280px;">Component</th>
                                                <th style="min-width:140px;">Qty</th>
                                                <th style="min-width:260px;">Note</th>
                                                <th style="width:90px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="bomItemsBody">
                                        <?php foreach ($items as $index => $item): ?>
                                            <?php $itemKind = (string) ($item['item_kind'] ?? 'material'); $materialId = (string) ($item['material_id'] ?? ''); $componentId = (string) ($item['component_id'] ?? ''); ?>
                                            <tr data-item-row>
                                                <td class="text-secondary fw-semibold item-row-number"><?php echo (int) $index + 1; ?></td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][item_kind]" class="form-select rounded-4 item-kind <?php echo $itemErrorFor($index, 'item_kind') ? 'is-invalid' : ''; ?>" onchange="onBomKindChange(this);">
                                                        <option value="material" <?php echo $itemKind === 'material' ? 'selected' : ''; ?>>Material</option>
                                                        <option value="component" <?php echo $itemKind === 'component' ? 'selected' : ''; ?>>Component</option>
                                                    </select>
                                                    <?php if ($itemErrorFor($index, 'item_kind')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'item_kind'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][material_id]" class="form-select rounded-4 item-material <?php echo $itemErrorFor($index, 'material_id') ? 'is-invalid' : ''; ?>">
                                                        <option value="">Chọn material</option>
                                                        <?php foreach ($materials as $material): ?>
                                                            <option value="<?php echo (int) $material['id']; ?>" <?php echo $materialId === (string) $material['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars((string) $material['code'] . ' - ' . (string) $material['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?php if ($itemErrorFor($index, 'material_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'material_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <select name="items[<?php echo (int) $index; ?>][component_id]" class="form-select rounded-4 item-component <?php echo $itemErrorFor($index, 'component_id') ? 'is-invalid' : ''; ?>">
                                                        <option value="">Chọn component</option>
                                                        <?php foreach ($childComponents as $component): ?>
                                                            <option value="<?php echo (int) $component['id']; ?>" <?php echo $componentId === (string) $component['id'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?php if ($itemErrorFor($index, 'component_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'component_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0.01" name="items[<?php echo (int) $index; ?>][quantity]" class="form-control rounded-4 item-quantity <?php echo $itemErrorFor($index, 'quantity') ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php if ($itemErrorFor($index, 'quantity')): ?><div class="invalid-feedback"><?php echo htmlspecialchars($itemErrorFor($index, 'quantity'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[<?php echo (int) $index; ?>][note]" class="form-control rounded-4 item-note" value="<?php echo htmlspecialchars((string) ($item['note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" maxlength="255">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-light rounded-3" onclick="removeBomRow(this);"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?php echo htmlspecialchars(app_url('/bom'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Cancel</a>
                            <button type="submit" class="btn btn-dark rounded-4 px-4">Save BOM</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>

<table class="d-none">
    <tbody>
        <tr id="bomRowTemplate" data-item-row>
            <td class="text-secondary fw-semibold item-row-number"></td>
            <td>
                <select class="form-select rounded-4 item-kind" onchange="onBomKindChange(this);">
                    <option value="material" selected>Material</option>
                    <option value="component">Component</option>
                </select>
            </td>
            <td>
                <select class="form-select rounded-4 item-material">
                    <option value="">Chọn material</option>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo (int) $material['id']; ?>"><?php echo htmlspecialchars((string) $material['code'] . ' - ' . (string) $material['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select class="form-select rounded-4 item-component">
                    <option value="">Chọn component</option>
                    <?php foreach ($childComponents as $component): ?>
                        <option value="<?php echo (int) $component['id']; ?>"><?php echo htmlspecialchars((string) $component['code'] . ' - ' . (string) $component['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" step="0.01" min="0.01" class="form-control rounded-4 item-quantity" value=""></td>
            <td><input type="text" class="form-control rounded-4 item-note" maxlength="255"></td>
            <td class="text-end"><button type="button" class="btn btn-light rounded-3" onclick="removeBomRow(this);"><i class="bi bi-trash"></i></button></td>
        </tr>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function getBomItemsBody() {
    return document.getElementById('bomItemsBody');
}

function reindexBomRows() {
    var body = getBomItemsBody();
    if (!body) {
        return;
    }

    var rows = body.querySelectorAll('tr[data-item-row]');
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var rowNumber = row.querySelector('.item-row-number');
        var itemKind = row.querySelector('.item-kind');
        var material = row.querySelector('.item-material');
        var component = row.querySelector('.item-component');
        var quantity = row.querySelector('.item-quantity');
        var note = row.querySelector('.item-note');

        if (rowNumber) { rowNumber.textContent = String(i + 1); }
        if (itemKind) { itemKind.name = 'items[' + i + '][item_kind]'; }
        if (material) { material.name = 'items[' + i + '][material_id]'; }
        if (component) { component.name = 'items[' + i + '][component_id]'; }
        if (quantity) { quantity.name = 'items[' + i + '][quantity]'; }
        if (note) { note.name = 'items[' + i + '][note]'; }
    }
}

function syncBomRowState(row) {
    if (!row) {
        return;
    }

    var parentRef = document.getElementById('bomParentRef');
    var parentId = parentRef ? parentRef.value : '';
    var itemKind = row.querySelector('.item-kind');
    var material = row.querySelector('.item-material');
    var component = row.querySelector('.item-component');
    var kind = itemKind ? itemKind.value : 'material';

    if (material) {
        material.disabled = kind !== 'material';
        if (kind !== 'material') {
            material.value = '';
        }
    }

    if (component) {
        component.disabled = kind !== 'component';
        if (kind !== 'component') {
            component.value = '';
        }

        for (var i = 0; i < component.options.length; i++) {
            var option = component.options[i];
            if (option.value === '') {
                option.hidden = false;
                continue;
            }

            option.hidden = parentId !== '' && option.value === parentId;
            if (option.hidden && option.selected) {
                component.value = '';
            }
        }
    }
}

function syncAllBomRows() {
    var body = getBomItemsBody();
    if (!body) {
        return;
    }

    var rows = body.querySelectorAll('tr[data-item-row]');
    for (var i = 0; i < rows.length; i++) {
        syncBomRowState(rows[i]);
    }

    reindexBomRows();
}

function addBomRow() {
    var body = getBomItemsBody();
    var template = document.getElementById('bomRowTemplate');
    if (!body || !template) {
        return false;
    }

    var clone = template.cloneNode(true);
    clone.removeAttribute('id');
    body.appendChild(clone);
    syncAllBomRows();
    return false;
}

function removeBomRow(button) {
    var body = getBomItemsBody();
    if (!body) {
        return false;
    }

    var row = button.closest('tr[data-item-row]');
    if (row) {
        row.remove();
    }

    if (body.querySelectorAll('tr[data-item-row]').length === 0) {
        addBomRow();
        return false;
    }

    syncAllBomRows();
    return false;
}

function onBomKindChange(select) {
    var row = select.closest('tr[data-item-row]');
    syncBomRowState(row);
}

function beforeSubmitBomForm() {
    syncAllBomRows();
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    syncAllBomRows();
});

window.addBomRow = addBomRow;
window.removeBomRow = removeBomRow;
window.onBomKindChange = onBomKindChange;
window.syncAllBomRows = syncAllBomRows;
window.beforeSubmitBomForm = beforeSubmitBomForm;
</script>
</body>
</html>
