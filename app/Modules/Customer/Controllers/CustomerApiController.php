<?php

declare(strict_types=1);

namespace App\Modules\Customer\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Customer\Services\CustomerService;

final class CustomerApiController extends Controller
{
    public function __construct(private readonly CustomerService $service)
    {
    }

    public function index(Request $request): array
    {
        $this->authorize('customer.view');
        $search = (string) $request->query('search', '');

        return [
            'module' => 'Customer',
            'query' => ['search' => $search],
            'data' => $this->service->list($search),
        ];
    }
}
