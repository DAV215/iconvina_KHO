<?php

declare(strict_types=1);

namespace App\Modules\Order\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Order\Services\OrderService;

final class OrderController extends Controller
{
    public function __construct(private readonly OrderService $service)
    {
    }

    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        return $this->view('app/Modules/Order/Views/index.php', [
            'pageTitle' => 'Orders',
            'pageEyebrow' => 'Sales order management',
            'activeSidebar' => 'orders',
            'search' => $search,
            'status' => $status,
            'statuses' => $this->service->statuses(),
            'orders' => $this->service->list($search, $status),
        ]);
    }

    public function create(Request $request)
    {
        unset($request);

        return $this->renderForm('Create Order', app_url('/orders/store'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        try {
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
        $id = (int) $request->query('id', 0);
        $order = $this->service->find($id);

        return $this->renderForm('Edit Order', app_url('/orders/update?id=' . $id), $order);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $input = $request->all();

        try {
            $validated = $this->validate($input, $this->rules());
            $this->service->update($id, $validated);
            session_flash('success', 'Order updated successfully.');

            return $this->redirect(app_url('/orders/show?id=' . $id));
        } catch (ValidationException|HttpException $exception) {
            $input['id'] = $id;

            return $this->renderForm('Edit Order', app_url('/orders/update?id=' . $id), $input, $exception->context()['errors'] ?? [], 422);
        }
    }

    public function delete(Request $request)
    {
        $id = (int) $request->query('id', 0);
        $this->service->delete($id);
        session_flash('success', 'Order deleted successfully.');

        return $this->redirect(app_url('/orders'));
    }

    private function renderForm(string $title, string $action, array $order = [], array $errors = [], int $status = 200)
    {
        return $this->view('app/Modules/Order/Views/form.php', [
            'pageTitle' => $title,
            'pageEyebrow' => 'Sales order management',
            'activeSidebar' => 'orders',
            'formAction' => $action,
            'order' => $order,
            'customers' => $this->service->customerOptions(),
            'quotations' => $this->service->quotationOptions(),
            'quotationPayload' => $this->service->quotationPayload(),
            'statuses' => $this->service->statuses(),
            'priorities' => $this->service->priorities(),
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
}