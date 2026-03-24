<?php

declare(strict_types=1);

namespace App\Modules\Quotation\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Quotation\Services\QuotationService;

final class QuotationApiController extends Controller
{
    public function __construct(private readonly QuotationService $service)
    {
    }

    public function index(Request $request): array
    {
        $this->authorize('quotation.view');
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        return [
            'module' => 'Quotation',
            'query' => [
                'search' => $search,
                'status' => $status,
            ],
            'data' => $this->service->list($search, $status),
        ];
    }
}
