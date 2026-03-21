<?php

declare(strict_types=1);

namespace App\Modules\Order\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Order\Services\OrderService;

final class OrderApiController extends Controller
{
    public function __construct(private readonly OrderService $service)
    {
    }

    public function index(Request $request): array
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        return [
            'module' => 'Order',
            'query' => [
                'search' => $search,
                'status' => $status,
            ],
            'data' => $this->service->list($search, $status),
        ];
    }
}