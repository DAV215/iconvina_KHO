<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Inventory\Services\StockService;

final class StockController extends Controller
{
    public function __construct(private readonly StockService $service)
    {
    }

    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $txnType = (string) $request->query('txn_type', '');

        return $this->view('app/Modules/Inventory/Views/index.php', [
            'pageTitle' => 'Stock Transactions',
            'pageEyebrow' => 'Inventory management',
            'activeSidebar' => 'inventory',
            'search' => $search,
            'txnType' => $txnType,
            'txnTypes' => $this->service->txnTypes(),
            'transactions' => $this->service->list($search, $txnType),
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Create Stock Transaction', app_url('/stocks/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $transactionId = $this->service->create($validated);
            session_flash('success', 'Stock transaction created successfully.');

            return $this->redirect(app_url('/stocks/show?id=' . $transactionId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Create Stock Transaction', app_url('/stocks/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Inventory/Views/show.php', [
            'pageTitle' => 'Stock Transaction Detail',
            'pageEyebrow' => 'Inventory transaction profile',
            'activeSidebar' => 'inventory',
            'transaction' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $transaction = $this->service->find($id);

        return $this->renderForm('Edit Stock Transaction', app_url('/stocks/update?id=' . $id), $transaction);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Stock transaction updated successfully.');

            return $this->redirect(app_url('/stocks/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Edit Stock Transaction', app_url('/stocks/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Stock transaction deleted successfully.');

        return $this->redirect(app_url('/stocks'));
    }

    private function renderForm(string $title, string $action, array $transaction = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Inventory/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Inventory management',
            'activeSidebar' => 'inventory',
            'formAction' => $action,
            'transaction' => $transaction,
            'txnTypes' => $this->service->txnTypes(),
            'materials' => $this->service->materialOptions(),
            'components' => $this->service->componentOptions(),
            'itemPayload' => $this->service->itemPayload(),
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'txn_no' => 'required|string|max:30',
            'txn_type' => 'required|string|max:30',
            'ref_type' => 'nullable|string|max:30',
            'ref_id' => 'nullable|numeric',
            'txn_date' => 'required|string|max:10',
            'note' => 'nullable|string|max:65535',
        ];
    }
}