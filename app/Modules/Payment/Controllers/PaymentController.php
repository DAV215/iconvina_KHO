<?php

declare(strict_types=1);

namespace App\Modules\Payment\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Payment\Services\PaymentService;

final class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $service)
    {
    }

    public function storeReceipt(Request $request)
    {
        $this->authorize('payment.create');
        $salesOrderId = (int) $request->query('id', 0);

        try {
            $this->service->createReceipt($salesOrderId, $request->all());
            session_flash('success', 'Payment receipt created.');
        } catch (ValidationException|HttpException $exception) {
            session_flash('error', $this->messageFromException($exception));
        }

        return $this->redirect(app_url('/orders/show?id=' . $salesOrderId . '#payments'));
    }

    public function storeVoucher(Request $request)
    {
        $this->authorize('payment.create');
        $purchaseOrderId = (int) $request->query('id', 0);

        try {
            $this->service->createVoucher($purchaseOrderId, $request->all());
            session_flash('success', 'Payment voucher created.');
        } catch (ValidationException|HttpException $exception) {
            session_flash('error', $this->messageFromException($exception));
        }

        return $this->redirect(app_url('/purchase-orders/show?id=' . $purchaseOrderId . '#payments'));
    }

    public function confirm(Request $request)
    {
        $this->authorize('payment.confirm');
        $paymentId = (int) $request->query('id', 0);
        $source = (string) $request->query('source', '');
        $sourceId = (int) $request->query('source_id', 0);

        try {
            $result = $this->service->confirm($paymentId);
            session_flash('success', 'Payment confirmed.');

            if (($result['type'] ?? '') === 'purchase_order') {
                return $this->redirect(app_url('/purchase-orders/show?id=' . (int) ($result['entity_id'] ?? 0) . '#payments'));
            }

            return $this->redirect(app_url('/orders/show?id=' . (int) ($result['entity_id'] ?? 0) . '#payments'));
        } catch (ValidationException|HttpException $exception) {
            session_flash('error', $this->messageFromException($exception));
        }

        if ($source === 'purchase_order' && $sourceId > 0) {
            return $this->redirect(app_url('/purchase-orders/show?id=' . $sourceId . '#payments'));
        }
        if ($source === 'sales_order' && $sourceId > 0) {
            return $this->redirect(app_url('/orders/show?id=' . $sourceId . '#payments'));
        }

        return $this->redirect(app_url('/'));
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
