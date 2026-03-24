<?php

declare(strict_types=1);

namespace App\Modules\Customer\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Customer\Services\CustomerService;

final class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('customer.view');
        $search = (string) $request->query('search', '');
        $paging = $this->paginationParams($request);
        $list = $this->service->list($search, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/customers', ['search' => $search], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Customer/Views/index.php', [
            'pageTitle' => 'Khách hàng',
            'pageEyebrow' => 'Quản lý khách hàng',
            'activeSidebar' => 'customers',
            'search' => $search,
            'customers' => $list['items'],
            'pagination' => $pagination,
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('customer.create');
        unset($request);

        return $this->renderForm('Thêm khách hàng', app_url('/customers/store'));
    }

    public function store(Request $request)
    {
        $this->authorize('customer.create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $customerId = $this->service->create($validated);
            session_flash('success', 'Tạo khách hàng thành công.');

            return $this->redirect(app_url('/customers/show?id=' . $customerId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm khách hàng', app_url('/customers/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('customer.view');
        $id = (int) $request->query('id', 0);
        $customer = $this->service->find($id);

        return $this->view('app/Modules/Customer/Views/show.php', [
            'pageTitle' => 'Chi tiết khách hàng',
            'pageEyebrow' => 'Hồ sơ khách hàng',
            'activeSidebar' => 'customers',
            'customer' => $customer,
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('customer.update');
        $id = (int) $request->query('id', 0);
        $customer = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa khách hàng', app_url('/customers/update?id=' . $id), $customer);
    }

    public function update(Request $request)
    {
        $this->authorize('customer.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật khách hàng thành công.');

            return $this->redirect(app_url('/customers/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa khách hàng', app_url('/customers/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('customer.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Xóa khách hàng thành công.');

        return $this->redirect(app_url('/customers'));
    }

    private function renderForm(string $title, string $action, array $customer = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Customer/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quản lý khách hàng',
            'activeSidebar' => 'customers',
            'formAction' => $action,
            'customer' => $customer,
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
        ];
    }
}
