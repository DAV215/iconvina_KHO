<?php

declare(strict_types=1);

namespace App\Modules\Payment\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\PurchaseOrder\Repositories\PurchaseOrderRepository;

final class PaymentService
{
    private const STATUSES = ['draft', 'confirmed'];
    private const PAYMENT_METHODS = ['cash', 'bank_transfer', 'card', 'other'];

    public function __construct(
        private readonly PaymentRepository $repository,
        private readonly OrderRepository $orderRepository,
        private readonly PurchaseOrderRepository $purchaseOrderRepository,
    ) {
    }

    public function createReceipt(int $salesOrderId, array $data): int
    {
        $order = $this->orderRepository->findById($salesOrderId);
        if ($order === null) {
            throw new HttpException('Sales order not found.', 404);
        }

        $this->assertSalesOrderPayable($order);
        $payload = $this->normalizePayload($data, 'receipt');
        $remainingAmount = $this->remainingAmount((float) ($order['total_amount'] ?? 0), $this->repository->confirmedTotalBySalesOrderId($salesOrderId));
        if ((float) $payload['amount'] > $remainingAmount) {
            throw new ValidationException([
                'amount' => ['Payment amount cannot exceed remaining amount.'],
            ]);
        }

        return $this->repository->transaction(function () use ($order, $salesOrderId, $payload): int {
            $paymentId = $this->repository->create([
                'payment_type' => 'receipt',
                'status' => 'draft',
                'customer_id' => (int) ($order['customer_id'] ?? 0),
                'supplier_id' => null,
                'sales_order_id' => $salesOrderId,
                'purchase_order_id' => null,
                'amount' => $payload['amount'],
                'payment_date' => $payload['payment_date'],
                'payment_method' => $payload['payment_method'],
                'reference_no' => $payload['reference_no'],
                'note' => $payload['note'],
                'created_by' => $this->actorId(),
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ]);
            $this->orderRepository->createLog([
                'module' => 'sales_order',
                'entity_id' => $salesOrderId,
                'action' => 'payment_created',
                'old_status' => (string) ($order['status'] ?? null),
                'new_status' => (string) ($order['status'] ?? null),
                'changed_fields_json' => json_encode([
                    'payment_id' => $paymentId,
                    'amount' => $payload['amount'],
                    'payment_date' => $payload['payment_date'],
                ], JSON_UNESCAPED_UNICODE),
                'remark' => 'Draft payment receipt created.',
                'acted_by' => $this->actorId(),
                'acted_at' => $this->timestamp(),
                'ip_address' => $this->nullableString($_SERVER['REMOTE_ADDR'] ?? null),
                'user_agent' => $this->nullableString($_SERVER['HTTP_USER_AGENT'] ?? null),
            ]);

            return $paymentId;
        });
    }

    public function createVoucher(int $purchaseOrderId, array $data): int
    {
        $purchaseOrder = $this->purchaseOrderRepository->findById($purchaseOrderId);
        if ($purchaseOrder === null) {
            throw new HttpException('Purchase order not found.', 404);
        }

        $this->assertPurchaseOrderPayable($purchaseOrder);
        $payload = $this->normalizePayload($data, 'voucher');
        $remainingAmount = $this->remainingAmount((float) ($purchaseOrder['total_amount'] ?? 0), $this->repository->confirmedTotalByPurchaseOrderId($purchaseOrderId));
        if ((float) $payload['amount'] > $remainingAmount) {
            throw new ValidationException([
                'amount' => ['Payment amount cannot exceed remaining amount.'],
            ]);
        }

        return $this->repository->transaction(function () use ($purchaseOrder, $purchaseOrderId, $payload): int {
            $paymentId = $this->repository->create([
                'payment_type' => 'voucher',
                'status' => 'draft',
                'customer_id' => null,
                'supplier_id' => isset($purchaseOrder['supplier_id']) && (int) $purchaseOrder['supplier_id'] > 0 ? (int) $purchaseOrder['supplier_id'] : null,
                'sales_order_id' => null,
                'purchase_order_id' => $purchaseOrderId,
                'amount' => $payload['amount'],
                'payment_date' => $payload['payment_date'],
                'payment_method' => $payload['payment_method'],
                'reference_no' => $payload['reference_no'],
                'note' => $payload['note'],
                'created_by' => $this->actorId(),
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ]);
            $this->purchaseOrderRepository->createLog([
                'module' => 'purchase_order',
                'entity_id' => $purchaseOrderId,
                'action' => 'payment_created',
                'old_status' => (string) ($purchaseOrder['status'] ?? null),
                'new_status' => (string) ($purchaseOrder['status'] ?? null),
                'changed_fields_json' => json_encode([
                    'payment_id' => $paymentId,
                    'amount' => $payload['amount'],
                    'payment_date' => $payload['payment_date'],
                ], JSON_UNESCAPED_UNICODE),
                'remark' => 'Draft payment voucher created.',
                'acted_by' => $this->actorId(),
                'acted_at' => $this->timestamp(),
                'ip_address' => $this->nullableString($_SERVER['REMOTE_ADDR'] ?? null),
                'user_agent' => $this->nullableString($_SERVER['HTTP_USER_AGENT'] ?? null),
            ]);

            return $paymentId;
        });
    }

    public function confirm(int $paymentId): array
    {
        $payment = $this->repository->findById($paymentId);
        if ($payment === null) {
            throw new HttpException('Payment not found.', 404);
        }
        if ((string) ($payment['status'] ?? 'draft') !== 'draft') {
            throw new HttpException('Only draft payments can be confirmed.', 409);
        }

        return $this->repository->transaction(function () use ($payment): array {
            $paymentId = (int) ($payment['id'] ?? 0);
            $salesOrderId = (int) ($payment['sales_order_id'] ?? 0);
            $purchaseOrderId = (int) ($payment['purchase_order_id'] ?? 0);

            if ($salesOrderId > 0) {
                $order = $this->orderRepository->findById($salesOrderId);
                if ($order === null) {
                    throw new HttpException('Sales order not found.', 404);
                }

                $remainingAmount = $this->remainingAmount(
                    (float) ($order['total_amount'] ?? 0),
                    $this->repository->confirmedTotalBySalesOrderId($salesOrderId)
                );
                if ((float) ($payment['amount'] ?? 0) > $remainingAmount) {
                    throw new HttpException('Payment amount exceeds remaining amount.', 409);
                }

                $this->repository->update($paymentId, [
                    'status' => 'confirmed',
                    'confirmed_by' => $this->actorId(),
                    'confirmed_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ]);
                $this->refreshSalesOrderPaymentSummary($salesOrderId);

                return [
                    'type' => 'sales_order',
                    'entity_id' => $salesOrderId,
                    'payment' => $this->repository->findById($paymentId),
                ];
            }

            if ($purchaseOrderId > 0) {
                $purchaseOrder = $this->purchaseOrderRepository->findById($purchaseOrderId);
                if ($purchaseOrder === null) {
                    throw new HttpException('Purchase order not found.', 404);
                }

                $remainingAmount = $this->remainingAmount(
                    (float) ($purchaseOrder['total_amount'] ?? 0),
                    $this->repository->confirmedTotalByPurchaseOrderId($purchaseOrderId)
                );
                if ((float) ($payment['amount'] ?? 0) > $remainingAmount) {
                    throw new HttpException('Payment amount exceeds remaining amount.', 409);
                }

                $this->repository->update($paymentId, [
                    'status' => 'confirmed',
                    'confirmed_by' => $this->actorId(),
                    'confirmed_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ]);
                $this->refreshPurchaseOrderPaymentSummary($purchaseOrderId);

                return [
                    'type' => 'purchase_order',
                    'entity_id' => $purchaseOrderId,
                    'payment' => $this->repository->findById($paymentId),
                ];
            }

            throw new HttpException('Payment does not link to any ERP document.', 409);
        });
    }

    public function paymentsBySalesOrder(int $salesOrderId): array
    {
        return $this->decoratePayments($this->repository->listBySalesOrderId($salesOrderId));
    }

    public function paymentsByPurchaseOrder(int $purchaseOrderId): array
    {
        return $this->decoratePayments($this->repository->listByPurchaseOrderId($purchaseOrderId));
    }

    public function paymentMethods(): array
    {
        return self::PAYMENT_METHODS;
    }

    public function refreshSalesOrderPaymentSummary(int $salesOrderId): array
    {
        $order = $this->orderRepository->findById($salesOrderId);
        if ($order === null) {
            throw new HttpException('Sales order not found.', 404);
        }

        $paidAmount = round($this->repository->confirmedTotalBySalesOrderId($salesOrderId), 2);
        $totalAmount = round((float) ($order['total_amount'] ?? 0), 2);
        $remainingAmount = $this->remainingAmount($totalAmount, $paidAmount);
        $paymentStatus = $this->paymentStatus($totalAmount, $paidAmount);

        $this->orderRepository->updatePaymentSummary($salesOrderId, [
            'paid_amount' => $this->formatDecimal($paidAmount),
            'payment_status' => $paymentStatus,
        ]);

        $this->orderRepository->createLog([
            'module' => 'sales_order',
            'entity_id' => $salesOrderId,
            'action' => 'payment_confirmed',
            'old_status' => (string) ($order['status'] ?? null),
            'new_status' => (string) ($order['status'] ?? null),
            'changed_fields_json' => json_encode([
                'paid_amount' => $this->formatDecimal($paidAmount),
                'remaining_amount' => $this->formatDecimal($remainingAmount),
                'payment_status' => $paymentStatus,
            ], JSON_UNESCAPED_UNICODE),
            'remark' => 'Payment confirmed for sales order.',
            'acted_by' => $this->actorId(),
            'acted_at' => $this->timestamp(),
            'ip_address' => $this->nullableString($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $this->nullableString($_SERVER['HTTP_USER_AGENT'] ?? null),
        ]);

        return [
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => $paymentStatus,
        ];
    }

    public function refreshPurchaseOrderPaymentSummary(int $purchaseOrderId): array
    {
        $purchaseOrder = $this->purchaseOrderRepository->findById($purchaseOrderId);
        if ($purchaseOrder === null) {
            throw new HttpException('Purchase order not found.', 404);
        }

        $paidAmount = round($this->repository->confirmedTotalByPurchaseOrderId($purchaseOrderId), 2);
        $totalAmount = round((float) ($purchaseOrder['total_amount'] ?? 0), 2);
        $remainingAmount = $this->remainingAmount($totalAmount, $paidAmount);
        $paymentStatus = $this->paymentStatus($totalAmount, $paidAmount);

        $this->purchaseOrderRepository->updatePaymentSummary($purchaseOrderId, [
            'paid_amount' => $this->formatDecimal($paidAmount),
            'payment_status' => $paymentStatus,
            'updated_at' => $this->timestamp(),
        ]);

        $this->purchaseOrderRepository->createLog([
            'module' => 'purchase_order',
            'entity_id' => $purchaseOrderId,
            'action' => 'payment_confirmed',
            'old_status' => (string) ($purchaseOrder['status'] ?? null),
            'new_status' => (string) ($purchaseOrder['status'] ?? null),
            'changed_fields_json' => json_encode([
                'paid_amount' => $this->formatDecimal($paidAmount),
                'remaining_amount' => $this->formatDecimal($remainingAmount),
                'payment_status' => $paymentStatus,
            ], JSON_UNESCAPED_UNICODE),
            'remark' => 'Payment confirmed for purchase order.',
            'acted_by' => $this->actorId(),
            'acted_at' => $this->timestamp(),
            'ip_address' => $this->nullableString($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $this->nullableString($_SERVER['HTTP_USER_AGENT'] ?? null),
        ]);

        return [
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => $paymentStatus,
        ];
    }

    private function decoratePayments(array $payments): array
    {
        foreach ($payments as &$payment) {
            $status = (string) ($payment['status'] ?? 'draft');
            $method = (string) ($payment['payment_method'] ?? 'other');
            $payment['status_label'] = $status === 'confirmed' ? 'Confirmed' : 'Draft';
            $payment['status_badge'] = $status === 'confirmed' ? 'success' : 'secondary';
            $payment['payment_method_label'] = match ($method) {
                'cash' => 'Cash',
                'bank_transfer' => 'Bank transfer',
                'card' => 'Card',
                default => 'Other',
            };
            $payment['confirmed_by_display'] = trim((string) ($payment['confirmed_by_name'] ?? '')) ?: ((string) ($payment['confirmed_by_username'] ?? '-'));
            $payment['can_confirm'] = $status === 'draft' && has_permission('payment.confirm');
        }
        unset($payment);

        return $payments;
    }

    private function normalizePayload(array $data, string $paymentType): array
    {
        $errors = [];
        $amount = trim((string) ($data['amount'] ?? ''));
        $paymentDate = trim((string) ($data['payment_date'] ?? date('Y-m-d')));
        $paymentMethod = strtolower(trim((string) ($data['payment_method'] ?? 'cash')));

        if (!is_numeric($amount) || (float) $amount <= 0) {
            $errors['amount'][] = 'Amount must be greater than 0.';
        }
        if ($paymentDate === '') {
            $errors['payment_date'][] = 'Payment date is required.';
        }
        if (!in_array($paymentMethod, self::PAYMENT_METHODS, true)) {
            $errors['payment_method'][] = 'Payment method is invalid.';
        }
        if (!in_array($paymentType, ['receipt', 'voucher'], true)) {
            $errors['payment_type'][] = 'Payment type is invalid.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'amount' => $this->formatDecimal((float) $amount),
            'payment_date' => $paymentDate,
            'payment_method' => $paymentMethod,
            'reference_no' => $this->nullableString($data['reference_no'] ?? null),
            'note' => $this->nullableString($data['note'] ?? null),
        ];
    }

    private function paymentStatus(float $totalAmount, float $paidAmount): string
    {
        if ($paidAmount <= 0.0) {
            return 'unpaid';
        }

        return $paidAmount >= $totalAmount ? 'paid' : 'partially_paid';
    }

    private function remainingAmount(float $totalAmount, float $paidAmount): float
    {
        return round(max($totalAmount - $paidAmount, 0), 2);
    }

    private function assertSalesOrderPayable(array $order): void
    {
        if (in_array((string) ($order['status'] ?? ''), ['draft', 'cancelled'], true)) {
            throw new HttpException('This sales order cannot receive payments in its current status.', 409);
        }
    }

    private function assertPurchaseOrderPayable(array $purchaseOrder): void
    {
        if (in_array((string) ($purchaseOrder['status'] ?? ''), ['draft', 'rejected', 'cancelled'], true)) {
            throw new HttpException('This purchase order cannot record payments in its current status.', 409);
        }
    }

    private function actorId(): ?int
    {
        $user = auth_user();
        if (!is_array($user)) {
            return null;
        }

        $id = (int) ($user['id'] ?? $user['user_id'] ?? 0);

        return $id > 0 ? $id : null;
    }

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
