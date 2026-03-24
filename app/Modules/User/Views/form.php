<?php
$activeSidebar = $activeSidebar ?? 'users';
$pageTitle = $pageTitle ?? 'Người dùng';
$formAction = $formAction ?? app_url('/users/store');
$user = $user ?? [];
$errors = $errors ?? [];
$statuses = $statuses ?? [];
$roles = $roles ?? [];
$managers = $managers ?? [];
$companies = $companies ?? [];
$branches = $branches ?? [];
$departments = $departments ?? [];
$positions = $positions ?? [];

$field = static function (string $key, string $default = '') use ($user): string {
    return htmlspecialchars((string) ($user[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};
$errorFor = static function (string $key) use ($errors): ?string {
    return $errors[$key][0] ?? null;
};
$selectOptions = static function (array $options, string $selectedValue): string {
    ob_start();
    foreach ($options as $option) {
        $value = (string) ($option['id'] ?? '');
        $label = trim((string) (($option['code'] ?? '') !== '' ? ($option['code'] . ' - ' . ($option['name'] ?? '')) : ($option['name'] ?? '')));
        ?>
        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedValue === $value ? 'selected' : ''; ?>><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
        <?php
    }

    return (string) ob_get_clean();
};
$selectedCompanyId = (string) ($user['company_id'] ?? '');
$selectedBranchId = (string) ($user['branch_id'] ?? '');
$selectedDepartmentId = (string) ($user['department_id'] ?? '');
$selectedPositionId = (string) ($user['position_id'] ?? '');
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
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div><div class="text-uppercase small fw-semibold text-secondary mb-2">Người dùng</div><h3 class="h4 mb-0 fw-semibold"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3></div>
                        <a href="<?php echo htmlspecialchars(app_url('/users'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Quay lại</a>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" class="row g-4">
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Mã người dùng</label><input type="text" name="code" class="form-control rounded-4 <?php echo $errorFor('code') ? 'is-invalid' : ''; ?>" value="<?php echo $field('code'); ?>" maxlength="30"><?php if ($errorFor('code')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('code'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Tài khoản</label><input type="text" name="username" class="form-control rounded-4 <?php echo $errorFor('username') ? 'is-invalid' : ''; ?>" value="<?php echo $field('username'); ?>" maxlength="80"><?php if ($errorFor('username')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('username'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Vai trò</label><select name="role_id" class="form-select rounded-4 <?php echo $errorFor('role_id') ? 'is-invalid' : ''; ?>"><option value="">Chọn vai trò</option><?php echo $selectOptions($roles, (string) ($user['role_id'] ?? '')); ?></select><?php if ($errorFor('role_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('role_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Trạng thái</label><select name="status" class="form-select rounded-4 <?php echo $errorFor('status') ? 'is-invalid' : ''; ?>"><?php foreach ($statuses as $value => $label): ?><option value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) ($user['status'] ?? 'draft') === (string) $value ? 'selected' : ''; ?>><?php echo htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select><?php if ($errorFor('status')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('status'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-6"><label class="form-label fw-semibold">Họ tên</label><input type="text" name="full_name" class="form-control rounded-4 <?php echo $errorFor('full_name') ? 'is-invalid' : ''; ?>" value="<?php echo $field('full_name'); ?>" maxlength="190"><?php if ($errorFor('full_name')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('full_name'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-6"><label class="form-label fw-semibold"><?php echo isset($user['id']) ? 'Đổi mật khẩu' : 'Mật khẩu'; ?></label><input type="password" name="password" class="form-control rounded-4 <?php echo $errorFor('password') ? 'is-invalid' : ''; ?>" maxlength="255"><?php if ($errorFor('password')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('password'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control rounded-4 <?php echo $errorFor('email') ? 'is-invalid' : ''; ?>" value="<?php echo $field('email'); ?>" maxlength="150"><?php if ($errorFor('email')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('email'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Điện thoại</label><input type="text" name="phone" class="form-control rounded-4 <?php echo $errorFor('phone') ? 'is-invalid' : ''; ?>" value="<?php echo $field('phone'); ?>" maxlength="30"><?php if ($errorFor('phone')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('phone'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Avatar URL</label><input type="text" name="avatar_url" class="form-control rounded-4 <?php echo $errorFor('avatar_url') ? 'is-invalid' : ''; ?>" value="<?php echo $field('avatar_url'); ?>" maxlength="255"><?php if ($errorFor('avatar_url')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('avatar_url'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Công ty</label><select id="userCompanyId" name="company_id" class="form-select rounded-4"><option value="">Chọn công ty</option><?php echo $selectOptions($companies, $selectedCompanyId); ?></select></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Chi nhánh</label><select id="userBranchId" name="branch_id" class="form-select rounded-4" data-selected="<?php echo htmlspecialchars($selectedBranchId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedCompanyId === '' ? 'disabled' : ''; ?>><option value=""><?php echo $selectedCompanyId === '' ? 'Chọn công ty trước' : 'Chọn chi nhánh'; ?></option><?php echo $selectOptions($branches, $selectedBranchId); ?></select></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Phòng ban</label><select id="userDepartmentId" name="department_id" class="form-select rounded-4" data-selected="<?php echo htmlspecialchars($selectedDepartmentId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedBranchId === '' ? 'disabled' : ''; ?>><option value=""><?php echo $selectedBranchId === '' ? 'Chọn chi nhánh trước' : 'Chọn phòng ban'; ?></option><?php echo $selectOptions($departments, $selectedDepartmentId); ?></select></div>
                        <div class="col-12 col-lg-3"><label class="form-label fw-semibold">Chức danh</label><select id="userPositionId" name="position_id" class="form-select rounded-4" data-selected="<?php echo htmlspecialchars($selectedPositionId, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedDepartmentId === '' ? 'disabled' : ''; ?>><option value=""><?php echo $selectedDepartmentId === '' ? 'Chọn phòng ban trước' : 'Chọn chức danh'; ?></option><?php echo $selectOptions($positions, $selectedPositionId); ?></select></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Quản lý trực tiếp</label><select name="manager_id" class="form-select rounded-4 <?php echo $errorFor('manager_id') ? 'is-invalid' : ''; ?>"><option value="">Chọn quản lý</option><?php echo $selectOptions($managers, (string) ($user['manager_id'] ?? '')); ?></select><?php if ($errorFor('manager_id')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('manager_id'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Ngày vào làm</label><input type="date" name="joined_at" class="form-control rounded-4 <?php echo $errorFor('joined_at') ? 'is-invalid' : ''; ?>" value="<?php echo $field('joined_at'); ?>"><?php if ($errorFor('joined_at')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('joined_at'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Ngày nghỉ việc</label><input type="date" name="terminated_at" class="form-control rounded-4 <?php echo $errorFor('terminated_at') ? 'is-invalid' : ''; ?>" value="<?php echo $field('terminated_at'); ?>"><?php if ($errorFor('terminated_at')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('terminated_at'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Ngôn ngữ</label><input type="text" name="language" class="form-control rounded-4" value="<?php echo $field('language', 'vi'); ?>" maxlength="12"></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Múi giờ</label><input type="text" name="timezone" class="form-control rounded-4" value="<?php echo $field('timezone', 'Asia/Saigon'); ?>" maxlength="64"></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Theme</label><select name="theme" class="form-select rounded-4"><option value="light" <?php echo $field('theme', 'light') === 'light' ? 'selected' : ''; ?>>Light</option><option value="dark" <?php echo $field('theme') === 'dark' ? 'selected' : ''; ?>>Dark</option></select></div>
                        <div class="col-12 col-lg-4"><label class="form-label fw-semibold">Đã xác minh</label><select name="is_verified" class="form-select rounded-4"><option value="0" <?php echo (string) ($user['is_verified'] ?? '0') === '0' ? 'selected' : ''; ?>>Chưa xác minh</option><option value="1" <?php echo (string) ($user['is_verified'] ?? '0') === '1' ? 'selected' : ''; ?>>Đã xác minh</option></select></div>
                        <div class="col-12 col-lg-8"><label class="form-label fw-semibold">meta_json</label><textarea name="meta_json" rows="3" class="form-control rounded-4 <?php echo $errorFor('meta_json') ? 'is-invalid' : ''; ?>"><?php echo $field('meta_json'); ?></textarea><?php if ($errorFor('meta_json')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('meta_json'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12"><label class="form-label fw-semibold">Ghi chú</label><textarea name="note" rows="4" class="form-control rounded-4 <?php echo $errorFor('note') ? 'is-invalid' : ''; ?>"><?php echo $field('note'); ?></textarea><?php if ($errorFor('note')): ?><div class="invalid-feedback"><?php echo htmlspecialchars((string) $errorFor('note'), ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?></div>
                        <div class="col-12 d-flex justify-content-end gap-2"><a href="<?php echo htmlspecialchars(app_url('/users'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light rounded-4 px-4">Hủy</a><button type="submit" class="btn btn-dark rounded-4 px-4">Lưu người dùng</button></div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const initUserCascading = () => {
        const companySelect = document.getElementById('userCompanyId');
        const branchSelect = document.getElementById('userBranchId');
        const departmentSelect = document.getElementById('userDepartmentId');
        const positionSelect = document.getElementById('userPositionId');

        if (!(companySelect instanceof HTMLSelectElement)
            || !(branchSelect instanceof HTMLSelectElement)
            || !(departmentSelect instanceof HTMLSelectElement)
            || !(positionSelect instanceof HTMLSelectElement)) {
            return;
        }

        const createOption = (value, label, selected = false) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            option.selected = selected;
            return option;
        };

        const formatLabel = (item) => {
            const code = String(item.code || '').trim();
            const name = String(item.name || '').trim();
            return code !== '' ? `${code} - ${name}` : name;
        };

        const resetSelect = (select, placeholder, disabled = true) => {
            select.innerHTML = '';
            select.appendChild(createOption('', placeholder, true));
            select.value = '';
            select.disabled = disabled;
        };

        const fillSelect = (select, items, placeholder, selectedValue = '') => {
            select.innerHTML = '';
            select.appendChild(createOption('', placeholder, selectedValue === ''));
            items.forEach((item) => {
                select.appendChild(createOption(String(item.id ?? ''), formatLabel(item), String(item.id ?? '') === selectedValue));
            });
            select.disabled = false;
            if (selectedValue !== '' && !items.some((item) => String(item.id ?? '') === selectedValue)) {
                select.value = '';
            }
        };

        const fetchOptions = async (url) => {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            });
            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const payload = await response.json();
            return Array.isArray(payload.items) ? payload.items : [];
        };

        const loadBranches = async (companyId, selectedValue = '') => {
            resetSelect(branchSelect, companyId ? 'Đang tải chi nhánh...' : 'Chọn công ty trước', !companyId);
            resetSelect(departmentSelect, 'Chọn chi nhánh trước', true);
            resetSelect(positionSelect, 'Chọn phòng ban trước', true);

            if (!companyId) {
                return;
            }

            const items = await fetchOptions(`<?php echo htmlspecialchars(app_url('/api/branches'), ENT_QUOTES, 'UTF-8'); ?>?company_id=${encodeURIComponent(companyId)}`);
            fillSelect(branchSelect, items, 'Chọn chi nhánh', selectedValue);
        };

        const loadDepartments = async (branchId, selectedValue = '') => {
            resetSelect(departmentSelect, branchId ? 'Đang tải phòng ban...' : 'Chọn chi nhánh trước', !branchId);
            resetSelect(positionSelect, 'Chọn phòng ban trước', true);

            if (!branchId) {
                return;
            }

            const items = await fetchOptions(`<?php echo htmlspecialchars(app_url('/api/departments'), ENT_QUOTES, 'UTF-8'); ?>?branch_id=${encodeURIComponent(branchId)}`);
            fillSelect(departmentSelect, items, 'Chọn phòng ban', selectedValue);
        };

        const loadPositions = async (departmentId, selectedValue = '') => {
            resetSelect(positionSelect, departmentId ? 'Đang tải chức danh...' : 'Chọn phòng ban trước', !departmentId);

            if (!departmentId) {
                return;
            }

            const items = await fetchOptions(`<?php echo htmlspecialchars(app_url('/api/positions'), ENT_QUOTES, 'UTF-8'); ?>?department_id=${encodeURIComponent(departmentId)}`);
            fillSelect(positionSelect, items, 'Chọn chức danh', selectedValue);
        };

        companySelect.addEventListener('change', async () => {
            await loadBranches(companySelect.value);
        });

        branchSelect.addEventListener('change', async () => {
            await loadDepartments(branchSelect.value);
        });

        departmentSelect.addEventListener('change', async () => {
            await loadPositions(departmentSelect.value);
        });

        const bootstrap = async () => {
            try {
                if (companySelect.value !== '') {
                    await loadBranches(companySelect.value, branchSelect.dataset.selected || '');
                } else {
                    resetSelect(branchSelect, 'Chọn công ty trước', true);
                    resetSelect(departmentSelect, 'Chọn chi nhánh trước', true);
                    resetSelect(positionSelect, 'Chọn phòng ban trước', true);
                    return;
                }

                if (branchSelect.value !== '') {
                    await loadDepartments(branchSelect.value, departmentSelect.dataset.selected || '');
                }

                if (departmentSelect.value !== '') {
                    await loadPositions(departmentSelect.value, positionSelect.dataset.selected || '');
                }
            } catch (error) {
                resetSelect(branchSelect, 'Không tải được chi nhánh', true);
                resetSelect(departmentSelect, 'Không tải được phòng ban', true);
                resetSelect(positionSelect, 'Không tải được chức danh', true);
                console.error(error);
            }
        };

        bootstrap();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserCascading, { once: true });
    } else {
        initUserCascading();
    }
})();
</script>
</body>
</html>
