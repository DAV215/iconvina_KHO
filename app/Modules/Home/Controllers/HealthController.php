<?php

declare(strict_types=1);

namespace App\Modules\Home\Controllers;

use App\Core\Http\Request;

final class HealthController
{
    public function show(Request $request): array
    {
        unset($request);

        return [
            'status' => 'ok',
            'app' => 'ICONVINA Mini ERP',
            'timestamp' => date(DATE_ATOM),
        ];
    }
}
