<?php

declare(strict_types=1);

namespace App\Modules\Department\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Department\Repositories\DepartmentRepository;

final class DepartmentApiController extends Controller
{
    public function __construct(private readonly DepartmentRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $branchId = (int) $request->query('branch_id', 0);
        $items = $branchId > 0 ? $this->repository->optionsByBranch($branchId) : [];

        return $this->json([
            'items' => $items,
        ]);
    }
}
