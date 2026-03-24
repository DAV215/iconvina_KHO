<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $basePath = App\Core\Application::basePath();

        return $path === '' ? $basePath : $basePath . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path($path === '' ? 'config' : 'config' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path($path === '' ? 'storage' : 'storage' . DIRECTORY_SEPARATOR . $path);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = '/'): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $path = '/' . ltrim($path, '/');

        if ($scriptDir === '' || $scriptDir === '.') {
            return $path;
        }

        return $scriptDir . $path;
    }
}

if (!function_exists('session_flash')) {
    function session_flash(string $key, string $message): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['_flash'][$key] = $message;

        return $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $key, ?string $default = null): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        if (isset($_SESSION['_flash']) && $_SESSION['_flash'] === []) {
            unset($_SESSION['_flash']);
        }

        return is_string($value) ? $value : $default;
    }
}

if (!function_exists('app_locale')) {
    function app_locale(): string
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $supported = ['vi', 'en'];
        $requested = strtolower(trim((string) ($_GET['lang'] ?? '')));

        if (in_array($requested, $supported, true)) {
            $_SESSION['_locale'] = $requested;
        }

        $sessionLocale = strtolower(trim((string) ($_SESSION['_locale'] ?? 'vi')));
        $resolved = in_array($sessionLocale, $supported, true) ? $sessionLocale : 'vi';

        return $resolved;
    }
}

if (!function_exists('lang_catalog')) {
    function lang_catalog(?string $locale = null): array
    {
        static $catalogs = [];

        $locale = $locale ?? app_locale();
        if (isset($catalogs[$locale])) {
            return $catalogs[$locale];
        }

        $path = base_path('app/Lang/' . $locale . '.php');
        $catalogs[$locale] = is_file($path) ? (array) require $path : [];

        return $catalogs[$locale];
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        $catalog = lang_catalog($locale);
        $messages = $catalog['messages'] ?? [];
        $text = is_array($messages) && array_key_exists($key, $messages) ? (string) $messages[$key] : $key;

        foreach ($replace as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }

        return $text;
    }
}

if (!function_exists('ui_replacements')) {
    function ui_replacements(?string $locale = null): array
    {
        $catalog = lang_catalog($locale);
        $replace = $catalog['replace'] ?? [];

        return is_array($replace) ? $replace : [];
    }
}

if (!function_exists('translate_html')) {
    function translate_html(string $html, ?string $locale = null): string
    {
        $replace = ui_replacements($locale);

        if ($replace === [] || $html === '') {
            return $html;
        }

        $protectedBlocks = [];
        $translatedHtml = preg_replace_callback(
            '/<(script|style|textarea)\b[^>]*>.*?<\/\1>/is',
            static function (array $matches) use (&$protectedBlocks): string {
                $token = '__HTML_TRANSLATE_BLOCK_' . count($protectedBlocks) . '__';
                $protectedBlocks[$token] = $matches[0];

                return $token;
            },
            $html
        );

        if (!is_string($translatedHtml)) {
            return $html;
        }

        $translatedHtml = strtr($translatedHtml, $replace);

        return $protectedBlocks === [] ? $translatedHtml : strtr($translatedHtml, $protectedBlocks);
    }
}

if (!function_exists('current_url')) {
    function current_url(array $overrides = []): string
    {
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? app_url('/'));
        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        $query = [];
        parse_str((string) parse_url($requestUri, PHP_URL_QUERY), $query);

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
                continue;
            }

            $query[$key] = $value;
        }

        $queryString = http_build_query($query);

        return $queryString === '' ? $path : $path . '?' . $queryString;
    }
}

if (!function_exists('erp_paginate')) {
    function erp_paginate(string $path, array $query, int $page, int $perPage, int $totalItems): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $totalItems = max(0, $totalItems);
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $page = min($page, $totalPages);

        $buildUrl = static function (int $targetPage) use ($path, $query, $perPage): string {
            $params = $query;
            $params['page'] = $targetPage;
            $params['per_page'] = $perPage;
            $params = array_filter(
                $params,
                static fn (mixed $value): bool => $value !== '' && $value !== null
            );

            return app_url($path . ($params === [] ? '' : '?' . http_build_query($params)));
        };

        $windowStart = max(1, $page - 2);
        $windowEnd = min($totalPages, $page + 2);
        if (($windowEnd - $windowStart) < 4) {
            if ($windowStart === 1) {
                $windowEnd = min($totalPages, 5);
            } elseif ($windowEnd === $totalPages) {
                $windowStart = max(1, $totalPages - 4);
            }
        }

        $pages = [];
        for ($index = $windowStart; $index <= $windowEnd; $index++) {
            $pages[] = [
                'number' => $index,
                'url' => $buildUrl($index),
                'is_current' => $index === $page,
            ];
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'from' => $totalItems === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($totalItems, $page * $perPage),
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_url' => $page > 1 ? $buildUrl($page - 1) : null,
            'next_url' => $page < $totalPages ? $buildUrl($page + 1) : null,
            'pages' => $pages,
        ];
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['auth_user'] ?? $_SESSION['_auth_user'] ?? null;
        if (is_array($user)) {
            $resolved = $user;
            return $resolved;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        try {
            $config = require config_path('database.php');
            $connection = $config['connections'][$config['default']] ?? null;
            if (!is_array($connection)) {
                return null;
            }

            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $connection['driver'],
                $connection['host'],
                $connection['port'],
                $connection['database'],
                $connection['charset'],
            );
            $pdo = new PDO($dsn, (string) $connection['username'], (string) $connection['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $stmt = $pdo->prepare(
                'SELECT u.*, r.code AS role_code, r.name AS role_name
                 FROM users u
                 LEFT JOIN roles r ON r.id = u.role_id
                 WHERE u.id = :id
                   AND u.is_active = 1
                   AND u.deleted_at IS NULL
                   AND u.status <> :deleted_status
                 LIMIT 1'
            );
            $stmt->execute([
                'id' => $userId,
                'deleted_status' => 'deleted',
            ]);
            $row = $stmt->fetch();
            if ($row === false) {
                unset($_SESSION['user_id'], $_SESSION['auth_user'], $_SESSION['_auth_user']);
                return null;
            }

            unset($row['password_hash']);
            $_SESSION['auth_user'] = $row;
            $resolved = $row;

            return $resolved;
        } catch (Throwable) {
            return null;
        }
    }
}

if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (auth_user() !== null) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        header('Location: ' . app_url('/login'));
        exit;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        static $cache = [];

        $user = auth_user();
        if (!is_array($user)) {
            return false;
        }

        $permissions = $user['permissions'] ?? [];
        if (is_array($permissions) && (in_array('*', $permissions, true) || in_array($permission, $permissions, true))) {
            return true;
        }

        $roleCode = strtoupper(trim((string) ($user['role_code'] ?? '')));
        if (in_array($roleCode, ['SUPER_ADMIN', 'ADMIN'], true)) {
            return true;
        }

        $roleId = (int) ($user['role_id'] ?? 0);
        if ($roleId <= 0) {
            return false;
        }

        if (!array_key_exists($roleId, $cache)) {
            try {
                $config = require config_path('database.php');
                $connection = $config['connections'][$config['default']] ?? null;
                if (!is_array($connection)) {
                    $cache[$roleId] = [];
                } else {
                    $dsn = sprintf(
                        '%s:host=%s;port=%d;dbname=%s;charset=%s',
                        $connection['driver'],
                        $connection['host'],
                        $connection['port'],
                        $connection['database'],
                        $connection['charset'],
                    );
                    $pdo = new PDO($dsn, (string) $connection['username'], (string) $connection['password'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    $stmt = $pdo->prepare(
                        'SELECT CONCAT(p.module, ".", p.action) AS permission_key
                         FROM role_permissions rp
                         INNER JOIN permissions p ON p.id = rp.permission_id
                         INNER JOIN roles r ON r.id = rp.role_id
                         WHERE rp.role_id = :role_id
                           AND r.is_active = 1'
                    );
                    $stmt->execute(['role_id' => $roleId]);
                    $cache[$roleId] = array_map(static fn (array $row): string => (string) $row['permission_key'], $stmt->fetchAll() ?: []);
                }
            } catch (Throwable) {
                $cache[$roleId] = [];
            }
        }

        return in_array($permission, $cache[$roleId], true);
    }
}

if (!function_exists('require_permission')) {
    function require_permission(string $permission): void
    {
        if (has_permission($permission)) {
            return;
        }

        throw new App\Core\Exceptions\HttpException('Bạn không có quyền thực hiện thao tác này.', 403, [
            'errors' => [
                'permission' => [$permission],
            ],
        ]);
    }
}

if (!function_exists('po_permission')) {
    function po_permission(string $action): bool
    {
        $action = strtolower(trim($action));
        if ($action === '') {
            return false;
        }

        if (has_permission('po.' . $action)) {
            return true;
        }

        $legacyMap = [
            'create' => ['purchase_order.create'],
            'view' => ['purchase_order.view'],
            'update' => ['purchase_order.update'],
            'approve' => ['purchase_order.approve'],
            'delete' => ['purchase_order.delete'],
            'view_log' => ['purchase_order.view'],
        ];

        foreach ($legacyMap[$action] ?? [] as $permission) {
            if (has_permission($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('require_po_permission')) {
    function require_po_permission(string $action): void
    {
        if (po_permission($action)) {
            return;
        }

        throw new App\Core\Exceptions\HttpException('Bạn không có quyền thực hiện thao tác này.', 403, [
            'errors' => [
                'permission' => ['po.' . strtolower(trim($action))],
            ],
        ]);
    }
}

if (!function_exists('production_permission')) {
    function production_permission(string $action): bool
    {
        $action = strtolower(trim($action));
        if ($action === '') {
            return false;
        }

        if (has_permission('production.' . $action)) {
            return true;
        }

        $legacyMap = [
            'start' => ['production.update'],
            'issue' => ['production.update', 'stock.create'],
            'view_log' => ['production.view', 'production.update'],
        ];

        foreach ($legacyMap[$action] ?? [] as $permission) {
            if (has_permission($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('require_production_permission')) {
    function require_production_permission(string $action): void
    {
        if (production_permission($action)) {
            return;
        }

        throw new App\Core\Exceptions\HttpException('Bạn không có quyền thực hiện thao tác này.', 403, [
            'errors' => [
                'permission' => ['production.' . strtolower(trim($action))],
            ],
        ]);
    }
}

if (!function_exists('service_order_permission')) {
    function service_order_permission(string $action): bool
    {
        $action = strtolower(trim($action));
        if ($action === '') {
            return false;
        }

        if (has_permission('service_order.' . $action)) {
            return true;
        }

        $legacyMap = [
            'assign' => ['service_order.update'],
            'start' => ['service_order.update'],
            'complete' => ['service_order.update'],
            'view_log' => ['service_order.view', 'service_order.update'],
        ];

        foreach ($legacyMap[$action] ?? [] as $permission) {
            if (has_permission($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('require_service_order_permission')) {
    function require_service_order_permission(string $action): void
    {
        if (service_order_permission($action)) {
            return;
        }

        throw new App\Core\Exceptions\HttpException('Bạn không có quyền thực hiện thao tác này.', 403, [
            'errors' => [
                'permission' => ['service_order.' . strtolower(trim($action))],
            ],
        ]);
    }
}
