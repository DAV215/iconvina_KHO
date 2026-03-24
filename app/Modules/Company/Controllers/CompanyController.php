<?php

declare(strict_types=1);

namespace App\Modules\Company\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Company\Services\CompanyService;

final class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $service)
    {
    }

    public function index(Request $request)
    {
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
        $pagination = erp_paginate('/companies', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Company/Views/index.php', [
            'pageTitle' => 'Công ty',
            'activeSidebar' => 'companies',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'sortOptions' => $this->service->sortOptions(),
            'companies' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Thêm công ty', app_url('/companies/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $companyId = $this->service->create($validated);
            session_flash('success', 'Tạo công ty thành công.');

            return $this->redirect(app_url('/companies/show?id=' . $companyId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Thêm công ty', app_url('/companies/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Company/Views/show.php', [
            'pageTitle' => 'Chi tiết công ty',
            'activeSidebar' => 'companies',
            'company' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $company = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa công ty', app_url('/companies/update?id=' . $id), $company);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật công ty thành công.');

            return $this->redirect(app_url('/companies/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa công ty', app_url('/companies/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function disable(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->disable($id);
        session_flash('success', 'Đã ngưng sử dụng công ty.');

        return $this->redirect(app_url('/companies'));
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Đã xóa mềm công ty.');

        return $this->redirect(app_url('/companies'));
    }

    private function renderForm(string $title, string $action, array $company = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Company/Views/form.php', [
            'pageTitle' => $title,
            'activeSidebar' => 'companies',
            'formAction' => $action,
            'company' => $company,
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:190',
            'is_active' => 'nullable|numeric',
        ];
    }
}
