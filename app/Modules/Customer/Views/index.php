<?php
$activeSidebar = $activeSidebar ?? 'customers';
$pageTitle = $pageTitle ?? 'Khách hàng';
$pageEyebrow = $pageEyebrow ?? 'Quản lý khách hàng';
$search = $search ?? '';
$customers = $customers ?? [];
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
                            <div class="text-uppercase small fw-semibold text-secondary mb-2">Khách hàng</div>
                            <h3 class="h4 fw-bold mb-1">Danh sách khách hàng</h3>
                        </div>
                        <div class="erp-toolbar__actions">
                            <button class="btn btn-light erp-btn erp-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#customerFilterCollapse" aria-expanded="true" aria-controls="customerFilterCollapse">
                                <i class="bi bi-funnel"></i>Bộ lọc
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-light erp-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-layout-three-columns"></i>Hiển thị cột
                                </button>
                                <div class="dropdown-menu dropdown-menu-end erp-dropdown-menu erp-column-menu p-2" data-column-menu="customersTable">
                                    <?php foreach ([
                                        'code' => 'Mã',
                                        'name' => 'Tên khách hàng',
                                        'contact' => 'Người liên hệ',
                                        'phone' => 'Điện thoại',
                                        'email' => 'Email',
                                        'tax_code' => 'Mã số thuế',
                                    ] as $key => $label): ?>
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" checked>
                                            <span class="form-check-label"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars(app_url('/customers/create'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-dark erp-btn px-4"><i class="bi bi-plus-lg"></i>Thêm khách hàng</a>
                        </div>
                    </div>

                    <div class="collapse show mb-4" id="customerFilterCollapse" data-filter-collapse="customers">
                        <form method="get" action="<?php echo htmlspecialchars(app_url('/customers'), ENT_QUOTES, 'UTF-8'); ?>" class="erp-filter-bar">
                            <div class="erp-filter-bar__group erp-filter-bar__group--search">
                                <label class="erp-filter-bar__label" for="customerSearchInput">Tìm kiếm khách hàng</label>
                                <input id="customerSearchInput" type="text" class="form-control erp-field" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm theo mã, tên, liên hệ, số điện thoại hoặc email">
                            </div>
                            <div class="erp-filter-bar__group erp-filter-bar__group--compact">
                                <label class="erp-filter-bar__label" for="customerPerPageSelect">Hiển thị</label>
                                <select id="customerPerPageSelect" name="per_page" class="form-select erp-select">
                                    <?php foreach ([10, 25, 50, 100] as $size): ?>
                                        <option value="<?php echo $size; ?>" <?php echo $perPage === $size ? 'selected' : ''; ?>><?php echo $size; ?> dòng</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="erp-filter-bar__actions">
                                <a href="<?php echo htmlspecialchars(app_url('/customers'), ENT_QUOTES, 'UTF-8'); ?>" class="btn erp-btn erp-btn-ghost">Đặt lại</a>
                                <button type="submit" class="btn btn-dark erp-btn"><i class="bi bi-search"></i>Tìm kiếm</button>
                            </div>
                        </form>
                    </div>

                    <div class="erp-table-shell p-2 p-lg-3">
                        <div class="erp-table-wrap">
                            <table class="table erp-table align-middle" id="customersTable">
                                <thead>
                                    <tr>
                                        <th data-col="code">Mã</th>
                                        <th data-col="name">Tên khách hàng</th>
                                        <th data-col="contact">Người liên hệ</th>
                                        <th data-col="phone">Điện thoại</th>
                                        <th data-col="email">Email</th>
                                        <th data-col="tax_code">Mã số thuế</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($customers === []): ?>
                                    <tr><td colspan="7" class="text-center text-secondary py-5">Không có khách hàng phù hợp.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr class="erp-row-compact">
                                            <td data-col="code"><span class="erp-code-badge"><?php echo htmlspecialchars((string) $customer['code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td data-col="name" class="erp-cell-compact">
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string) $customer['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <?php if (!empty($customer['address'])): ?><div class="erp-cell-secondary text-truncate" style="max-width: 240px;"><?php echo htmlspecialchars((string) $customer['address'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
                                            </td>
                                            <td data-col="contact"><?php echo htmlspecialchars((string) ($customer['contact_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="phone"><?php echo htmlspecialchars((string) ($customer['phone'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="email"><?php echo htmlspecialchars((string) ($customer['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-col="tax_code"><?php echo htmlspecialchars((string) ($customer['tax_code'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-light erp-btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Mở</button>
                                                    <ul class="dropdown-menu dropdown-menu-end erp-dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/customers/show?id=' . (int) $customer['id']), ENT_QUOTES, 'UTF-8'); ?>">Chi tiết</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars(app_url('/customers/edit?id=' . (int) $customer['id']), ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
                                                        <li><form method="post" action="<?php echo htmlspecialchars(app_url('/customers/delete?id=' . (int) $customer['id']), ENT_QUOTES, 'UTF-8'); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?');"><button type="submit" class="dropdown-item text-danger">Xóa</button></form></li>
                                                    </ul>
                                                </div>
                                            </td>
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
<script src="<?php echo htmlspecialchars(app_url('/assets/js/erp-list.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
