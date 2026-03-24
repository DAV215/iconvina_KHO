<?php

declare(strict_types=1);

namespace App\Modules\PurchaseOrder\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\PurchaseOrder\Services\PurchaseOrderService;

final class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $service)
    {
    }

    public function index(Request $request)
    {
        require_po_permission('view');

        $filters = [
            'search' => (string) $request->query('search', ''),
            'status' => (string) $request->query('status', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];
        $sort = [
            'by' => (string) $request->query('sort_by', 'order_date'),
            'dir' => (string) $request->query('sort_dir', 'desc'),
        ];
        $paging = $this->paginationParams($request);
        $list = $this->service->list($filters, $sort, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/purchase-orders', [
            'search' => $filters['search'],
            'status' => $filters['status'],
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'sort_by' => $sort['by'],
            'sort_dir' => $sort['dir'],
        ], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/PurchaseOrder/Views/index.php', [
            'pageTitle' => 'Đơn mua hàng',
            'pageEyebrow' => 'Purchase Order Workflow',
            'activeSidebar' => 'purchase-orders',
            'filters' => $filters,
            'sort' => $sort,
            'statuses' => $this->service->statuses(),
            'statusLabels' => $this->service->statusLabels(),
            'purchaseOrders' => $list['items'],
            'pagination' => $pagination,
            'sortOptions' => $this->service->sortOptions(),
        ]);
    }

    public function create(Request $request)
    {
        require_po_permission('create');
        unset($request);

        return $this->renderForm('Tạo đơn mua hàng', app_url('/purchase-orders/store'));
    }

    public function store(Request $request)
    {
        require_po_permission('create');
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $purchaseOrderId = $this->service->create($validated);
            session_flash('success', 'Tạo đơn mua hàng thành công.');

            return $this->redirect(app_url('/purchase-orders/show?id=' . $purchaseOrderId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Tạo đơn mua hàng', app_url('/purchase-orders/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        require_po_permission('view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/PurchaseOrder/Views/show.php', [
            'pageTitle' => 'Chi tiết đơn mua hàng',
            'pageEyebrow' => 'Purchase Order Workflow',
            'activeSidebar' => 'purchase-orders',
            'purchaseOrder' => $this->service->find($id),
            'statusLabels' => $this->service->statusLabels(),
        ]);
    }

    public function edit(Request $request)
    {
        require_po_permission('update');
        $id = (int) $request->query('id', 0);
        $purchaseOrder = $this->service->find($id);

        return $this->renderForm('Chỉnh sửa đơn mua hàng', app_url('/purchase-orders/update?id=' . $id), $purchaseOrder);
    }

    public function update(Request $request)
    {
        require_po_permission('update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Cập nhật đơn mua hàng thành công.');

            return $this->redirect(app_url('/purchase-orders/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Chỉnh sửa đơn mua hàng', app_url('/purchase-orders/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function submit(Request $request)
    {
        require_po_permission('submit');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->submit($id, $this->remark($request)),
            'Đã submit PO chờ duyệt.'
        );
    }

    public function approve(Request $request)
    {
        require_po_permission('approve');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->approve($id, $this->remark($request)),
            'Đã duyệt PO.'
        );
    }

    public function reject(Request $request)
    {
        require_po_permission('reject');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->reject($id, $this->remark($request)),
            'Đã từ chối PO.'
        );
    }

    public function cancel(Request $request)
    {
        require_po_permission('cancel');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->cancel($id, $this->remark($request)),
            'Đã hủy PO.'
        );
    }

    public function receive(Request $request)
    {
        require_po_permission('receive');
        $mode = strtolower(trim((string) $request->input('receive_mode', 'partial')));
        require_po_permission($mode === 'full' ? 'receive_full' : 'receive_partial');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->receive($id, $request->all(), $mode === 'full' ? 'full' : 'partial'),
            'Đã ghi nhận nhận hàng.',
            '#receiving'
        );
    }

    public function addExtraCost(Request $request)
    {
        require_po_permission('add_extra_cost');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->addExtraCost($id, $request->all()),
            'Đã thêm chi phí.',
            '#extra-costs'
        );
    }

    public function submitStockIn(Request $request)
    {
        require_po_permission('submit_stock_in');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->submitStockIn($id, $this->remark($request)),
            'Đã submit stock-in chờ duyệt.',
            '#stock-in'
        );
    }

    public function approveStockIn(Request $request)
    {
        require_po_permission('stock_in_approve');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->approveStockIn($id, $this->remark($request)),
            'Đã duyệt stock-in.',
            '#stock-in'
        );
    }

    public function close(Request $request)
    {
        require_po_permission('close');
        $id = (int) $request->query('id', 0);

        return $this->runAction(
            $id,
            fn () => $this->service->close($id, $this->remark($request)),
            'Đã đóng PO.'
        );
    }

    public function delete(Request $request)
    {
        require_po_permission('delete');
        $id = (int) $request->query('id', 0);

        try {
            $this->service->delete($id);
            session_flash('success', 'Xóa đơn mua hàng thành công.');
        } catch (ValidationException|HttpException $exception) {
            session_flash('error', $this->messageFromException($exception));
        }

        return $this->redirect(app_url('/purchase-orders'));
    }

    private function renderForm(string $title, string $action, array $purchaseOrder = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/PurchaseOrder/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Purchase Order Workflow',
            'activeSidebar' => 'purchase-orders',
            'formAction' => $action,
            'purchaseOrder' => $purchaseOrder,
            'suggestedCode' => $this->service->suggestCode(
                (string) ($purchaseOrder['order_date'] ?? date('Y-m-d')),
                isset($purchaseOrder['id']) ? (int) $purchaseOrder['id'] : null
            ),
            'statuses' => $this->service->statuses(),
            'statusLabels' => $this->service->statusLabels(),
            'materials' => $this->service->materialOptions(),
            'materialPayload' => $this->service->materialPayload(),
            'supplierPayload' => $this->service->supplierPayload(),
            'materialCategoryOptions' => $this->service->materialCategoryOptions(),
            'errors' => $errors,
        ], $status);
    }

    private function rules(): array
    {
        return [
            'code' => 'nullable|string|max:30',
            'supplier_id' => 'nullable|string|max:20',
            'supplier_name' => 'nullable|string|max:150',
            'supplier_contact' => 'nullable|string|max:150',
            'supplier_phone' => 'nullable|string|max:30',
            'supplier_email' => 'nullable|string|max:150',
            'order_date' => 'required|string|max:10',
            'expected_date' => 'nullable|string|max:10',
            'tax_percent' => 'nullable|numeric',
            'note' => 'nullable|string|max:65535',
        ];
    }

    private function remark(Request $request): ?string
    {
        $remark = trim((string) $request->input('remark', ''));

        return $remark === '' ? null : $remark;
    }

    private function runAction(int $id, callable $callback, string $successMessage, string $hash = ''): \App\Core\Http\Response
    {
        try {
            $callback();
            session_flash('success', $successMessage);
        } catch (ValidationException|HttpException $exception) {
            session_flash('error', $this->messageFromException($exception));
        }

        return $this->redirect(app_url('/purchase-orders/show?id=' . $id . $hash));
    }

    private function messageFromException(ValidationException|HttpException $exception): string
    {
        $context = $exception->context()['errors'] ?? [];
        foreach ($context as $messages) {
            if (is_array($messages) && $messages !== []) {
                return (string) $messages[0];
            }
        }

        return $exception->getMessage();
    }
}
