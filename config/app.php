<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'ICONVINA Mini ERP'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://localhost:8000'),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'default_admin' => [
        'username' => env('APP_ADMIN_USERNAME', 'admin'),
        'password' => env('APP_ADMIN_PASSWORD', 'ChangeMe123!'),
        'full_name' => env('APP_ADMIN_FULL_NAME', 'System Administrator'),
        'email' => env('APP_ADMIN_EMAIL', 'admin@iconvina.local'),
        'phone' => env('APP_ADMIN_PHONE', ''),
    ],
];
