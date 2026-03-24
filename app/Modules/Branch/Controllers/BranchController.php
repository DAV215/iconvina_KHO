<?php

declare(strict_types=1);

namespace App\Modules\Branch\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Branch\Services\BranchService;

final class BranchController extends Controller
{
    public function __construct(private readonly BranchService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'company_id' => (string) $request->query('company_id', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'updated_at'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/branches', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'company_id' => $filters['company_id'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Branch/Views/index.php', [
            'pageTitle' => 'Chi nhánh',
            'activeSidebar' => 'branches',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'sortOptions' => $this->service->sortOptions(),
            'companies' => $this->service->companyOptions(),
            'branches' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Thêm chi nhánh', app_url('/branches/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $branchId = $this->service->create($validated);
            session_flash('success', 'Tạo chi nhánh thành công.');

            return $this->redirect(app_url('/branches/show?id=' . $branchId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm chi nhánh', app_url('/branches/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Branch/Views/show.php', [
            'pageTitle' => 'Chi tiết chi nhánh',
            'activeSidebar' => 'branches',
            'branch' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $branch = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa chi nhánh', app_url('/branches/update?id=' . $id), $branch);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật chi nhánh thành công.');

            return $this->redirect(app_url('/branches/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa chi nhánh', app_url('/branches/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function disable(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->disable($id);
        session_flash('success', 'Đã ngưng sử dụng chi nhánh.');

        return $this->redirect(app_url('/branches'));
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Đã xóa mềm chi nhánh.');

        return $this->redirect(app_url('/branches'));
    }

    private function renderForm(string $title, string $action, array $branch = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Branch/Views/form.php', [
            'pageTitle' => $title,
            'activeSidebar' => 'branches',
            'formAction' => $action,
            'branch' => $branch,
            'errors' => $errors,
            'companies' => $this->service->companyOptions(),
        ], $status);
    }

    private function rules(): array
    {
        return [
            'company_id' => 'required|numeric',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'is_active' => 'nullable|numeric',
        ];
    }
}
