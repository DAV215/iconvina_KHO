<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Inventory\Services\StockService;
use App\Modules\PurchaseOrder\Services\PurchaseOrderService;

final class StockController extends Controller
{
    public function __construct(
        private readonly StockService $service,
        private readonly PurchaseOrderService $purchaseOrderService,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('stock.view');
        $search = (string) $request->query('search', '');
        $txnType = (string) $request->query('txn_type', '');
        $paging = $this->paginationParams($request);
        $list = $this->service->list($search, $txnType, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/stocks', ['search' => $search, 'txn_type' => $txnType], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Inventory/Views/index.php', [
            'pageTitle' => 'Stock Transactions',
            'pageEyebrow' => 'Inventory management',
            'activeSidebar' => 'inventory',
            'search' => $search,
            'txnType' => $txnType,
            'txnTypes' => $this->service->txnTypes(),
            'transactions' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        $refType = (string) $request->query('ref_type', '');
        $refId = (int) $request->query('ref_id', 0);
        $this->authorize($refType === 'purchase_order' ? 'stock.approve' : 'stock.create');
        $transaction = [];
        $title = 'Tạo phiếu nhập kho';

        if ($refType === 'purchase_order' && $refId > 0) {
            $transaction = $this->purchaseOrderService->buildStockImportPrefill($refId);
            $title = 'Tạo phiếu nhập kho từ đơn mua hàng';
        }

        return $this->renderForm($title, app_url('/stocks/store'), $transaction);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $refType = strtolower(trim((string) ($input['ref_type'] ?? '')));
        $this->authorize($refType === 'purchase_order' ? 'stock.approve' : 'stock.create');

        try {
            $validated = $this->validate($input, $this->rules());
            $transactionId = $this->service->create($validated);
            session_flash('success', 'Tạo phiếu nhập kho thành công.');

            return $this->redirect(app_url('/stocks/show?id=' . $transactionId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Tạo phiếu nhập kho', app_url('/stocks/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('stock.view');
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
        $this->authorize('stock.update');
        $id = (int) $request->query('id', 0);
        $transaction = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa phiếu kho', app_url('/stocks/update?id=' . $id), $transaction);
    }

    public function update(Request $request)
    {
        $this->authorize('stock.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật phiếu kho thành công.');

            return $this->redirect(app_url('/stocks/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa phiếu kho', app_url('/stocks/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $this->authorize('stock.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Stock transaction deleted successfully.');

        return $this->redirect(app_url('/stocks'));
    }

    private function renderForm(string $title, string $action, array $transaction = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Inventory/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Kho / Nhập kho',
            'activeSidebar' => 'inventory',
            'formAction' => $action,
            'transaction' => $transaction,
            'txnTypes' => $this->service->txnTypes(),
            'materials' => $this->service->materialOptions(),
            'components' => $this->service->componentOptions(),
            'materialCategoryOptions' => $this->service->materialCategoryOptions(),
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
