<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Request;

final class AuthController
{
    public function index(Request $request): array
    {
        unset($request);

        return [
            'module' => 'Auth',
            'planned_features' => [
                'login',
                'logout',
                'session-based authentication',
                'role and permission guard',
            ],
        ];
    }
}
