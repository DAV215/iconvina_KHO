<?php

declare(strict_types=1);

namespace App\Modules\Department\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Department\Services\DepartmentService;

final class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'branch_id' => (string) $request->query('branch_id', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'updated_at'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/departments', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'branch_id' => $filters['branch_id'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Department/Views/index.php', [
            'pageTitle' => 'Phòng ban',
            'activeSidebar' => 'departments',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'sortOptions' => $this->service->sortOptions(),
            'branches' => $this->service->branchOptions(),
            'departments' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Thêm phòng ban', app_url('/departments/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $departmentId = $this->service->create($validated);
            session_flash('success', 'Tạo phòng ban thành công.');

            return $this->redirect(app_url('/departments/show?id=' . $departmentId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm phòng ban', app_url('/departments/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Department/Views/show.php', [
            'pageTitle' => 'Chi tiết phòng ban',
            'activeSidebar' => 'departments',
            'department' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $department = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa phòng ban', app_url('/departments/update?id=' . $id), $department);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật phòng ban thành công.');

            return $this->redirect(app_url('/departments/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa phòng ban', app_url('/departments/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function disable(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->disable($id);
        session_flash('success', 'Đã ngưng sử dụng phòng ban.');

        return $this->redirect(app_url('/departments'));
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Đã xóa mềm phòng ban.');

        return $this->redirect(app_url('/departments'));
    }

    private function renderForm(string $title, string $action, array $department = [], array $errors = [], int $status = 200)
    {
        $departmentId = isset($department['id']) ? (int) $department['id'] : null;

        return $this->view('app/Modules/Department/Views/form.php', [
            'pageTitle' => $title,
            'activeSidebar' => 'departments',
            'formAction' => $action,
            'department' => $department,
            'errors' => $errors,
            'branches' => $this->service->branchOptions(),
            'parents' => $this->service->parentOptions($departmentId),
        ], $status);
    }

    private function rules(): array
    {
        return [
            'branch_id' => 'required|numeric',
            'parent_id' => 'nullable|numeric',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'is_active' => 'nullable|numeric',
        ];
    }
}
