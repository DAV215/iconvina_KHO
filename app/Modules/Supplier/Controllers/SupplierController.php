<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Supplier\Services\SupplierService;

final class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('supplier.view');
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
        $pagination = erp_paginate('/suppliers', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Supplier/Views/index.php', [
            'pageTitle' => 'Nhà cung cấp',
            'pageEyebrow' => 'Quản lý nhà cung cấp',
            'activeSidebar' => 'suppliers',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'sortOptions' => $this->service->sortOptions(),
            'suppliers' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('supplier.create');
        unset($request);

        return $this->renderForm('Thêm nhà cung cấp', app_url('/suppliers/store'));
    }

    public function store(Request $request)
    {
        $this->authorize('supplier.create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $supplierId = $this->service->create($validated);
            session_flash('success', 'Tạo nhà cung cấp thành công.');

            return $this->redirect(app_url('/suppliers/show?id=' . $supplierId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm nhà cung cấp', app_url('/suppliers/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('supplier.view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Supplier/Views/show.php', [
            'pageTitle' => 'Chi tiết nhà cung cấp',
            'pageEyebrow' => 'Hồ sơ nhà cung cấp',
            'activeSidebar' => 'suppliers',
            'supplier' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('supplier.update');
        $id = (int) $request->query('id', 0);
        $supplier = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa nhà cung cấp', app_url('/suppliers/update?id=' . $id), $supplier);
    }

    public function update(Request $request)
    {
        $this->authorize('supplier.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật nhà cung cấp thành công.');

            return $this->redirect(app_url('/suppliers/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa nhà cung cấp', app_url('/suppliers/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('supplier.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Xóa nhà cung cấp thành công.');

        return $this->redirect(app_url('/suppliers'));
    }

    private function renderForm(string $title, string $action, array $supplier = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Supplier/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quản lý nhà cung cấp',
            'activeSidebar' => 'suppliers',
            'formAction' => $action,
            'supplier' => $supplier,
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'contact_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'tax_code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:65535',
            'note' => 'nullable|string|max:65535',
            'is_active' => 'nullable|numeric',
        ];
    }
}
