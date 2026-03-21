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
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        return $this->view('app/Modules/Quotation/Views/index.php', [
            'pageTitle' => 'Quotations',
            'pageEyebrow' => 'Quotation management',
            'activeSidebar' => 'quotations',
            'search' => $search,
            'status' => $status,
            'statuses' => $this->service->statuses(),
            'quotations' => $this->service->list($search, $status),
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Create Quotation', app_url('/quotations/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

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
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Quotation/Views/show.php', [
            'pageTitle' => 'Quotation Detail',
            'pageEyebrow' => 'Quotation profile',
            'activeSidebar' => 'quotations',
            'quotation' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $quotation = $this->service->find($id);

        return $this->renderForm('Edit Quotation', app_url('/quotations/update?id=' . $id), $quotation);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

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
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Quotation deleted successfully.');

        return $this->redirect(app_url('/quotations'));
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