<?php

declare(strict_types=1);

namespace App\Modules\MaterialCategory\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\MaterialCategory\Services\MaterialCategoryService;

final class MaterialCategoryApiController extends Controller
{
    public function __construct(private readonly MaterialCategoryService $service)
    {
    }

    public function index(Request $request): array
    {
        $this->authorize('material_category.view');
        $search = (string) $request->query('search', '');

        return [
            'module' => 'MaterialCategory',
            'query' => ['search' => $search],
            'data' => $this->service->list($search),
        ];
    }
}
