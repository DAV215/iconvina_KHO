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

        return $replace === [] ? $html : strtr($html, $replace);
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