<?php

declare(strict_types=1);

namespace App\Modules\Accounting\Controllers;

use App\Core\Http\Request;

final class AccountingController
{
    public function index(Request $request): array
    {
        unset($request);

        return [
            'module' => 'Accounting',
            'capabilities' => [
                'expense voucher',
                'receipt voucher',
                'payable',
                'receivable',
                'cashflow summary',
            ],
        ];
    }
}
