<?php

declare(strict_types=1);

namespace App\Modules\Position\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Position\Services\PositionService;

final class PositionController extends Controller
{
    public function __construct(private readonly PositionService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'department_id' => (string) $request->query('department_id', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'updated_at'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/positions', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'department_id' => $filters['department_id'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Position/Views/index.php', [
            'pageTitle' => 'Chức danh',
            'activeSidebar' => 'positions',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'sortOptions' => $this->service->sortOptions(),
            'departments' => $this->service->departmentOptions(),
            'positions' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        unset($request);
        return $this->renderForm('Thêm chức danh', app_url('/positions/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $positionId = $this->service->create($validated);
            session_flash('success', 'Tạo chức danh thành công.');

            return $this->redirect(app_url('/positions/show?id=' . $positionId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm chức danh', app_url('/positions/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Position/Views/show.php', [
            'pageTitle' => 'Chi tiết chức danh',
            'activeSidebar' => 'positions',
            'position' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $position = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa chức danh', app_url('/positions/update?id=' . $id), $position);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật chức danh thành công.');

            return $this->redirect(app_url('/positions/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa chức danh', app_url('/positions/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function disable(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->disable($id);
        session_flash('success', 'Đã ngưng sử dụng chức danh.');

        return $this->redirect(app_url('/positions'));
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Đã xóa mềm chức danh.');

        return $this->redirect(app_url('/positions'));
    }

    private function renderForm(string $title, string $action, array $position = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Position/Views/form.php', [
            'pageTitle' => $title,
            'activeSidebar' => 'positions',
            'formAction' => $action,
            'position' => $position,
            'errors' => $errors,
            'departments' => $this->service->departmentOptions(),
        ], $status);
    }

    private function rules(): array
    {
        return [
            'department_id' => 'nullable|numeric',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:65535',
            'is_active' => 'nullable|numeric',
        ];
    }
}
