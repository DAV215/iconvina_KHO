<?php

declare(strict_types=1);

namespace App\Modules\PurchaseOrder\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\PurchaseOrder\Services\PurchaseOrderService;

final class PurchaseOrderApiController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $service)
    {
    }

    public function index(Request $request): array
    {
        $this->authorize('purchase_order.view');
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'order_date'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];

        return [
            'module' => 'PurchaseOrder',
            'query' => [
                'filters' => $filters,
                'sort' => $sort,
            ],
            'data' => $this->service->list($filters, $sort),
        ];
    }
}
