<?php

declare(strict_types=1);

namespace App\Modules\Order\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Order\Services\OrderService;
use App\Modules\ServiceOrder\Services\ServiceOrderService;

final class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $service,
        private readonly ServiceOrderService $serviceOrderService,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('sales_order.view');
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');
        $paging = $this->paginationParams($request);
        $list = $this->service->list($search, $status, $paging['page'], $paging['per_page']);
        $pagination = erp_paginate('/orders', ['search' => $search, 'status' => $status], $paging['page'], $paging['per_page'], (int) $list['total']);

        return $this->view('app/Modules/Order/Views/index.php', [
            'pageTitle' => 'Orders',
            'pageEyebrow' => 'Sales order management',
            'activeSidebar' => 'orders',
            'search' => $search,
            'status' => $status,
            'statuses' => $this->service->statuses(),
            'orders' => $list['items'],
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('sales_order.create');
        $quotationId = (int) $request->query('quotation_id', 0);

        return $this->renderForm('Create Order', app_url('/orders/store'), [
            'quotation_id' => $quotationId > 0 ? $quotationId : null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('sales_order.create');
        $input = $request->all();

        try {
            $this->assertStatusChangeAllowed((string) ($input['status'] ?? 'draft'));
            $validated = $this->validate($input, $this->rules());
            $orderId = $this->service->create($validated);
            session_flash('success', 'Order created successfully.');

            return $this->redirect(app_url('/orders/show?id=' . $orderId));
        } catch (ValidationException|HttpException $exception) {
            return $this->renderForm('Create Order', app_url('/orders/store'), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function show(Request $request)
    {
        $this->authorize('sales_order.view');
        $id = (int) $request->query('id', 0);

        return $this->view('app/Modules/Order/Views/show.php', [
            'pageTitle' => 'Order Detail',
            'pageEyebrow' => 'Sales order profile',
            'activeSidebar' => 'orders',
            'order' => $this->service->find($id),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function edit(Request $request)
    {
        $this->authorize('sales_order.update');
        $id = (int) $request->query('id', 0);
        $order = $this->service->find($id);

        return $this->renderForm('Edit Order', app_url('/orders/update?id=' . $id), $order);
    }

    public function update(Request $request)
    {
        $this->authorize('sales_order.update');
        $id = (int) $request->query('id', 0);
        $input = $request->all();
        $order = $this->service->find($id);

        try {
            $this->assertStatusChangeAllowed((string) ($input['status'] ?? ($order['status'] ?? 'draft')), (string) ($order['status'] ?? 'draft'));
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Order updated successfully.');

            return $this->redirect(app_url('/orders/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Edit Order', app_url('/orders/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function approve(Request $request)
    {
        if (!has_permission('sales_order.confirm') && !has_permission('sales_order.approve')) {
            $this->authorize('sales_order.confirm');
        }
        $id = (int) $request->query('id', 0);
        $this->service->approve($id);
        $this->serviceOrderService->ensureForSalesOrder($id);
        session_flash('success', 'Đã xác nhận đơn bán hàng.');

        return $this->redirect(app_url('/orders/show?id=' . $id));
    }

    public function confirm(Request $request)
    {
        return $this->approve($request);
    }

    public function markReadyToDeliver(Request $request)
    {
        $this->authorizeDeliveryAction();
        $id = (int) $request->query('id', 0);
        $this->service->markReadyToDeliver($id);
        session_flash('success', 'Đơn bán đã chuyển sang sẵn sàng giao.');

        return $this->redirect(app_url('/orders/show?id=' . $id));
    }

    public function createDelivery(Request $request)
    {
        $this->authorizeDeliveryAction();
        $orderId = (int) $request->query('id', 0);

        try {
            $deliveryId = $this->service->createDelivery($orderId, $request->all());
            session_flash('success', 'Đã tạo phiếu giao hàng nháp.');

            return $this->redirect(app_url('/orders/show?id=' . $orderId . '&delivery_id=' . $deliveryId));
        } catch (ValidationException|HttpException $exception) {
            session_flash('error', $this->extractFirstError($exception->context()['errors'] ?? []) ?? $exception->getMessage());

            return $this->redirect(app_url('/orders/show?id=' . $orderId));
        }
    }

    public function confirmDelivery(Request $request)
    {
        $this->authorizeDeliveryAction();
        $orderId = (int) $request->query('id', 0);
        $deliveryId = (int) $request->query('delivery_id', 0);
        $this->service->confirmDelivery($orderId, $deliveryId);
        session_flash('success', 'Đã xác nhận giao hàng và xuất kho.');

        return $this->redirect(app_url('/orders/show?id=' . $orderId));
    }

    public function cancelDelivery(Request $request)
    {
        $this->authorizeDeliveryAction();
        $orderId = (int) $request->query('id', 0);
        $deliveryId = (int) $request->query('delivery_id', 0);
        $this->service->cancelDelivery($orderId, $deliveryId);
        session_flash('success', 'Đã hủy phiếu giao hàng nháp.');

        return $this->redirect(app_url('/orders/show?id=' . $orderId));
    }

    public function createComponent(Request $request)
    {
        $this->authorize('component.create');
        $orderId = (int) $request->query('id', 0);
        $itemId = (int) $request->query('item_id', 0);
        $componentId = $this->service->createComponentFromEstimateItem($orderId, $itemId);
        session_flash('success', 'Đã tạo mã bán thành phẩm từ dòng estimate.');

        return $this->redirect(app_url('/orders/show?id=' . $orderId . '&component_id=' . $componentId));
    }

    public function delete(Request $request)
    {
        $this->authorize('sales_order.delete');
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Order deleted successfully.');

        return $this->redirect(app_url('/orders'));
    }

    private function renderForm(string $title, string $action, array $order = [], array $errors = [], int $status = 200)
    {
        $orderDate = (string) ($order['order_date'] ?? date('Y-m-d'));

        return $this->view('app/Modules/Order/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Sales order management',
            'activeSidebar' => 'orders',
            'formAction' => $action,
            'order' => $order,
            'suggestedCode' => $this->service->suggestCode($orderDate, (string) ($order['code'] ?? '')),
            'customers' => $this->service->customerOptions(),
            'quotations' => $this->service->quotationOptions(),
            'quotationPayload' => $this->service->quotationPayload(),
            'statuses' => $this->service->statuses(),
            'priorities' => $this->service->priorities(),
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
            'quotation_id' => 'nullable|numeric',
            'order_date' => 'required|string|max:10',
            'due_date' => 'nullable|string|max:10',
            'status' => 'required|string|max:30',
            'priority' => 'required|string|max:20',
            'discount_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'note' => 'nullable|string|max:65535',
        ];
    }

    private function assertStatusChangeAllowed(string $requestedStatus, string $currentStatus = 'draft'): void
    {
        $requestedStatus = strtolower(trim($requestedStatus));
        $currentStatus = strtolower(trim($currentStatus));

        if ($requestedStatus === '') {
            $requestedStatus = $currentStatus;
        }

        if ($requestedStatus === $currentStatus || has_permission('sales_order.approve') || has_permission('sales_order.confirm')) {
            return;
        }

        throw new HttpException('Bạn không có quyền thay đổi trạng thái đơn bán hàng.', 403, [
            'errors' => [
                'status' => ['Bạn không có quyền thay đổi trạng thái đơn bán hàng.'],
            ],
        ]);
    }

    private function authorizeDeliveryAction(): void
    {
        if (!has_permission('sales_order.deliver') && !has_permission('stock.create') && !has_permission('stock.approve')) {
            $this->authorize('sales_order.deliver');
        }
    }

    private function extractFirstError(array $errors): ?string
    {
        foreach ($errors as $messages) {
            if (is_array($messages) && isset($messages[0])) {
                return (string) $messages[0];
            }
        }

        return null;
    }
}
