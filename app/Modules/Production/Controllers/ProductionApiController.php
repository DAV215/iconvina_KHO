<?php

declare(strict_types=1);

namespace App\Modules\Production\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Production\Services\ProductionService;

final class ProductionApiController extends Controller
{
    public function __construct(private readonly ProductionService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'mine' => (string) $request->query('mine', '') === '1',
        ];
        $paging = $this->paginationParams($request, 25);
        $list = $this->service->list($filters, $paging['page'], $paging['per_page']);

        return $this->json($list);
    }
}
