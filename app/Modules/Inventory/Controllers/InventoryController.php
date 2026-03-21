<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Core\Http\Request;

final class InventoryController
{
    public function index(Request $request): array
    {
        unset($request);

        return [
            'module' => 'Inventory',
            'capabilities' => [
                'materials',
                'semi-finished components',
                'bom',
                'stock in',
                'stock out',
                'stock ledger',
            ],
        ];
    }
}
