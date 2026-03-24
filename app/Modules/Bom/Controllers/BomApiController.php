<?php

declare(strict_types=1);

namespace App\Modules\Bom\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Bom\Services\BomService;

final class BomApiController extends Controller
{
    public function __construct(private readonly BomService $service)
    {
    }

    public function index(Request $request): array
    {
        $this->authorize('bom.view');
        $componentId = (int) $request->query('component_id', 0);
        $version = (string) $request->query('version', '');

        return [
            'module' => 'BOM',
            'query' => [
                'component_id' => $componentId,
                'version' => $version,
            ],
            'data' => $this->service->list($componentId > 0 ? $componentId : null, $version),
        ];
    }
}
