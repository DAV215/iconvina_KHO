<?php

declare(strict_types=1);

namespace App\Modules\Component\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Component\Services\ComponentService;

final class ComponentController extends Controller
{
    public function __construct(private readonly ComponentService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('component.view');
        $search = (string) $request->query('search', '');
        $paging = $this->paginationParams($request);
        $list = $this->service->list($search, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/components', ['search' => $search], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Component/Views/index.php', [
            'pageTitle' => 'Bán thành phẩm',
            'pageEyebrow' => 'Quản lý bán thành phẩm',
            'activeSidebar' => 'components',
            'search' => $search,
            'components' => $list['items'],
            'pagination' => $pagination,
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('component.create');
        unset($request);

        return $this->renderForm('Thêm bán thành phẩm', app_url('/components/store'));
    }

    public function store(Request $request)
    {
        $this->authorize('component.create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $id = $this->service->create($validated);
            session_flash('success', 'Tạo bán thành phẩm thành công.');

            return $this->redirect(app_url('/components/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm bán thành phẩm', app_url('/components/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('component.view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Component/Views/show.php', [
            'pageTitle' => 'Chi tiết bán thành phẩm',
            'pageEyebrow' => 'Hồ sơ bán thành phẩm',
            'activeSidebar' => 'components',
            'component' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('component.update');
        $id = (int) $request->query('id', 0);
        $component = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa bán thành phẩm', app_url('/components/update?id=' . $id), $component);
    }

    public function update(Request $request)
    {
        $this->authorize('component.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật bán thành phẩm thành công.');

            return $this->redirect(app_url('/components/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa bán thành phẩm', app_url('/components/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('component.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Xóa bán thành phẩm thành công.');

        return $this->redirect(app_url('/components'));
    }

    private function renderForm(string $title, string $action, array $component = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Component/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quản lý bán thành phẩm',
            'activeSidebar' => 'components',
            'formAction' => $action,
            'component' => $component,
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'unit' => 'required|string|max:50',
            'standard_cost' => 'required|numeric',
            'image_path' => 'nullable|string|max:255',
        ];
    }
}
