<?php

declare(strict_types=1);

namespace App\Modules\Position\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Position\Repositories\PositionRepository;

final class PositionApiController extends Controller
{
    public function __construct(private readonly PositionRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $departmentId = (int) $request->query('department_id', 0);
        $items = $departmentId > 0 ? $this->repository->optionsByDepartment($departmentId) : [];

        return $this->json([
            'items' => $items,
        ]);
    }
}
