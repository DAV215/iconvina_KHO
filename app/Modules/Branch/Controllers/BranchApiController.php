<?php

declare(strict_types=1);

namespace App\Modules\Branch\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Branch\Repositories\BranchRepository;

final class BranchApiController extends Controller
{
    public function __construct(private readonly BranchRepository $repository)
    {
    }

    public function index(Request $request)
    {
        $companyId = (int) $request->query('company_id', 0);
        $items = $companyId > 0 ? $this->repository->optionsByCompany($companyId) : [];

        return $this->json([
            'items' => $items,
        ]);
    }
}
