<?php

declare(strict_types=1);

namespace App\Modules\Quotation\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Quotation\Services\QuotationService;

final class QuotationController extends Controller
{
    public function __construct(private readonly QuotationService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('quotation.view');
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');
        $paging = $this->paginationParams($request);
        $list = $this->service->list($search, $status, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/quotations', ['search' => $search, 'status' => $status], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Quotation/Views/index.php', [
            'pageTitle' => 'Quotations',
            'pageEyebrow' => 'Quotation management',
            'activeSidebar' => 'quotations',
            'search' => $search,
            'status' => $status,
            'statuses' => $this->service->statuses(),
            'quotations' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('quotation.create');
        $quoteDate = (string) $request->query('quote_date', date('Y-m-d'));

        return $this->renderForm('Create Quotation', app_url('/quotations/store'), [
            'quote_date' => $quoteDate,
            'code' => $this->service->suggestCode($quoteDate),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('quotation.create');
        $input = $request->all();
        $input['status'] = 'draft';

        try {
            $validated = $this->validate($input, $this->rules());
            $quotationId = $this->service->create($validated);
            session_flash('success', 'Quotation created successfully.');

            return $this->redirect(app_url('/quotations/show?id=' . $quotationId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Create Quotation', app_url('/quotations/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('quotation.view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Quotation/Views/show.php', [
            'pageTitle' => 'Quotation Detail',
            'pageEyebrow' => 'Quotation profile',
            'activeSidebar' => 'quotations',
            'quotation' => $this->service->find($id),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('quotation.update');
        $id = (int) $request->query('id', 0);
        $quotation = $this->service->find($id);
        if (empty($quotation['workflow']['can_edit'])) {
            throw new HttpException('Chỉ được chỉnh sửa báo giá ở trạng thái nháp hoặc bị từ chối.', 409);
        }

        return $this->renderForm('Edit Quotation', app_url('/quotations/update?id=' . $id), $quotation);
    }

    public function update(Request $request)
    {
        $this->authorize('quotation.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();
        $quotation = $this->service->find($id);
        $input['status'] = (string) ($quotation['status'] ?? 'draft');

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Quotation updated successfully.');

            return $this->redirect(app_url('/quotations/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Edit Quotation', app_url('/quotations/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('quotation.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Quotation deleted successfully.');

        return $this->redirect(app_url('/quotations'));
    }

    public function submit(Request $request)
    {
        $this->authorize('quotation.submit');
        $id = (int) $request->query('id', 0);
        $this->service->submit($id);
        session_flash('success', 'Đã trình duyệt báo giá.');

        return $this->redirect(app_url('/quotations/show?id=' . $id));
    }

    public function approve(Request $request)
    {
        $this->authorize('quotation.approve');
        $id = (int) $request->query('id', 0);
        $this->service->approve($id);
        session_flash('success', 'Đã duyệt báo giá.');

        return $this->redirect(app_url('/quotations/show?id=' . $id));
    }

    public function reject(Request $request)
    {
        $this->authorize('quotation.reject');
        $id = (int) $request->query('id', 0);
        $this->service->reject($id);
        session_flash('success', 'Đã từ chối báo giá.');

        return $this->redirect(app_url('/quotations/show?id=' . $id));
    }

    public function cancel(Request $request)
    {
        $this->authorize('quotation.cancel');
        $id = (int) $request->query('id', 0);
        $this->service->cancel($id);
        session_flash('success', 'Đã hủy báo giá.');

        return $this->redirect(app_url('/quotations/show?id=' . $id));
    }

    private function renderForm(string $title, string $action, array $quotation = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Quotation/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Quotation management',
            'activeSidebar' => 'quotations',
            'formAction' => $action,
            'quotation' => $quotation,
            'customers' => $this->service->customerOptions(),
            'statuses' => $this->service->statuses(),
            'statusLabels' => $this->service->statusLabels(),
            'suggestedCode' => $this->service->suggestCode((string) ($quotation['quote_date'] ?? date('Y-m-d')), (string) ($quotation['code'] ?? '')),
            'itemModes' => $this->service->itemModes(),
            'itemPayload' => $this->service->itemPayload(),
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'customer_id' => 'required|numeric',
            'quote_date' => 'required|string|max:10',
            'expired_at' => 'nullable|string|max:10',
            'status' => 'required|string|max:20',
            'tax_amount' => 'nullable|numeric',
            'note' => 'nullable|string|max:65535',
        ];
    }
}
