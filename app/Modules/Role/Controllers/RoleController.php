<?php

declare(strict_types=1);

namespace App\Modules\Role\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Role\Services\RoleService;

final class RoleController extends Controller
{
    public function __construct(private readonly RoleService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('role.view');
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'updated_at'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/roles', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Role/Views/index.php', [
            'pageTitle' => 'Vai trò',
            'activeSidebar' => 'roles',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'sortOptions' => $this->service->sortOptions(),
            'roles' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('role.create');
        unset($request);
        return $this->renderForm('Thêm vai trò', app_url('/roles/store'));
    }

    public function store(Request $request)
    {
        $this->authorize('role.create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $roleId = $this->service->create($validated);
            session_flash('success', 'Tạo vai trò thành công.');

            return $this->redirect(app_url('/roles/permissions?id=' . $roleId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm vai trò', app_url('/roles/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function edit(Request $request)
    {
        $this->authorize('role.update');
        $id = (int) $request->query('id', 0);
        return $this->renderForm('Chỉnh sửa vai trò', app_url('/roles/update?id=' . $id), $this->service->find($id));
    }

    public function update(Request $request)
    {
        $this->authorize('role.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật vai trò thành công.');

            return $this->redirect(app_url('/roles'));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;
            return $this->renderForm('Chỉnh sửa vai trò', app_url('/roles/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('role.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Đã xóa vai trò.');

        return $this->redirect(app_url('/roles'));
    }

    public function disable(Request $request)
    {
        $this->authorize('role.update');
        $id = (int) $request->query('id', 0);
        $this->service->disable($id);
        session_flash('success', 'Đã ngưng sử dụng vai trò.');

        return $this->redirect(app_url('/roles'));
    }

    private function renderForm(string $title, string $action, array $role = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Role/Views/form.php', [
            'pageTitle' => $title,
            'activeSidebar' => 'roles',
            'formAction' => $action,
            'role' => $role,
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:65535',
            'is_active' => 'nullable|numeric',
        ];
    }
}
