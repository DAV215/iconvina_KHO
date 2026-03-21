<?php

declare(strict_types=1);

namespace App\Modules\Production\Controllers;

use App\Core\Http\Request;

final class ProductionController
{
    public function index(Request $request): array
    {
        unset($request);

        return [
            'module' => 'Production',
            'capabilities' => [
                'production order',
                'task breakdown',
                'timeline',
                'progress tracking',
                'material consumption linkage',
            ],
        ];
    }
}
