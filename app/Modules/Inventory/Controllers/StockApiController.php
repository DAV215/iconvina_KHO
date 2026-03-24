<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Inventory\Services\StockService;

final class StockApiController extends Controller
{
    public function __construct(private readonly StockService $service)
    {
    }

    public function index(Request $request): array
    {
        $this->authorize('stock.view');
        $search = (string) $request->query('search', '');
        $txnType = (string) $request->query('txn_type', '');

        return [
            'module' => 'Stock',
            'query' => [
                'search' => $search,
                'txn_type' => $txnType,
            ],
            'data' => $this->service->list($search, $txnType),
        ];
    }
}
