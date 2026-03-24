<?php

declare(strict_types=1);

namespace App\Modules\MaterialCategory\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\MaterialCategory\Services\MaterialCategoryService;

final class MaterialCategoryController extends Controller
{
    public function __construct(private readonly MaterialCategoryService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('material_category.view');
        $search = (string) $request->query('search', '');
        $paging = $this->paginationParams($request);
        $list = $this->service->list($search, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/material-categories', ['search' => $search], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/MaterialCategory/Views/index.php', [
            'pageTitle' => 'Danh mục nguyên vật liệu',
            'pageEyebrow' => 'Quản lý danh mục nguyên vật liệu',
            'activeSidebar' => 'material-categories',
            'search' => $search,
            'categories' => $list['items'],
            'pagination' => $pagination,
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('material_category.create');
        unset($request);

        return $this->renderForm('Thêm danh mục nguyên vật liệu', app_url('/material-categories/store'));
    }

    public function store(Request $request)
    {
        $this->authorize('material_category.create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $id = $this->service->create($validated);
            session_flash('success', 'Tạo danh mục nguyên vật liệu thành công.');

            return $this->redirect(app_url('/material-categories/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm danh mục nguyên vật liệu', app_url('/material-categories/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('material_category.view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/MaterialCategory/Views/show.php', [
            'pageTitle' => 'Chi tiết danh mục nguyên vật liệu',
            'pageEyebrow' => 'Hồ sơ danh mục nguyên vật liệu',
            'activeSidebar' => 'material-categories',
            'category' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('material_category.update');
        $id = (int) $request->query('id', 0);
        $category = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa danh mục nguyên vật liệu', app_url('/material-categories/update?id=' . $id), $category, [], 200, $id);
    }

    public function update(Request $request)
    {
        $this->authorize('material_category.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật danh mục nguyên vật liệu thành công.');

            return $this->redirect(app_url('/material-categories/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa danh mục nguyên vật liệu', app_url('/material-categories/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422, $id);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('material_category.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Xóa danh mục nguyên vật liệu thành công.');

        return $this->redirect(app_url('/material-categories'));
    }

    private function renderForm(string $title, string $action, array $category = [], array $errors = [], int $status = 200, ?int $excludeId = null)
    {
        return $this->view('app/Modules/MaterialCategory/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quản lý danh mục nguyên vật liệu',
            'activeSidebar' => 'material-categories',
            'formAction' => $action,
            'category' => $category,
            'parentOptions' => $this->service->parentOptions($excludeId),
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:120',
            'parent_id' => 'nullable|numeric',
            'is_active' => 'nullable|numeric',
        ];
    }
}
