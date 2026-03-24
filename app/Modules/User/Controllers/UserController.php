<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\User\Services\UserService;

final class UserController extends Controller
{
    public function __construct(private readonly UserService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('user.view');
        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'role_id' => (string) $request->query('role_id', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'updated_at'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/users', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'role_id' => $filters['role_id'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/User/Views/index.php', [
            'pageTitle' => 'Người dùng',
            'pageEyebrow' => 'Nhân sự',
            'activeSidebar' => 'users',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'roles' => $this->service->roleOptions(),
            'sortOptions' => $this->service->sortOptions(),
            'users' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('user.create');
        unset($request);

        return $this->renderForm('Thêm người dùng', app_url('/users/store'));
    }

    public function store(Request $request)
    {
        $this->authorize('user.create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules(true));
            $userId = $this->service->create($validated);
            session_flash('success', 'Tạo người dùng thành công.');

            return $this->redirect(app_url('/users/show?id=' . $userId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm người dùng', app_url('/users/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('user.view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/User/Views/show.php', [
            'pageTitle' => 'Chi tiết người dùng',
            'pageEyebrow' => 'Nhân sự',
            'activeSidebar' => 'users',
            'user' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('user.update');
        $id = (int) $request->query('id', 0);
        $user = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa người dùng', app_url('/users/update?id=' . $id), $user);
    }

    public function update(Request $request)
    {
        $this->authorize('user.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules(false));
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật người dùng thành công.');

            return $this->redirect(app_url('/users/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa người dùng', app_url('/users/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function disable(Request $request)
    {
        $this->authorize('user.update');
        $id = (int) $request->query('id', 0);
        $this->service->disable($id);
        session_flash('success', 'Đã khóa tài khoản người dùng.');

        return $this->redirect(app_url('/users'));
    }

    public function delete(Request $request)
    {
        $this->authorize('user.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Đã xóa mềm người dùng.');

        return $this->redirect(app_url('/users'));
    }

    private function renderForm(string $title, string $action, array $user = [], array $errors = [], int $status = 200)
    {
        $userId = isset($user['id']) ? (int) $user['id'] : null;

        return $this->view('app/Modules/User/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Nhân sự',
            'activeSidebar' => 'users',
            'formAction' => $action,
            'user' => $user,
            'errors' => $errors,
            'statuses' => $this->service->statuses(),
            'roles' => $this->service->roleOptions(),
            'managers' => $this->service->managerOptions($userId),
            'companies' => $this->service->companyOptions(),
            'branches' => $this->service->branchOptions(),
            'departments' => $this->service->departmentOptions(),
            'positions' => $this->service->positionOptions(),
        ], $status);
    }

    private function rules(bool $isCreate): array
    {
        return [
            'code' => 'required|string|max:30',
            'username' => 'required|string|max:80',
            'password' => $isCreate ? 'required|string|max:255' : 'nullable|string|max:255',
            'full_name' => 'required|string|max:190',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'avatar_url' => 'nullable|string|max:255',
            'company_id' => 'nullable|numeric',
            'branch_id' => 'nullable|numeric',
            'department_id' => 'nullable|numeric',
            'position_id' => 'nullable|numeric',
            'manager_id' => 'nullable|numeric',
            'role_id' => 'required|numeric',
            'status' => 'required|string|max:30',
            'is_verified' => 'nullable|numeric',
            'joined_at' => 'nullable|string|max:10',
            'terminated_at' => 'nullable|string|max:10',
            'language' => 'nullable|string|max:12',
            'timezone' => 'nullable|string|max:64',
            'theme' => 'nullable|string|max:32',
            'meta_json' => 'nullable|string|max:65535',
            'note' => 'nullable|string|max:65535',
        ];
    }
}
