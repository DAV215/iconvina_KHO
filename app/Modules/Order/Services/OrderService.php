<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Customer\Repositories\CustomerRepository;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Quotation\Repositories\QuotationRepository;
use DateTimeImmutable;
use PDOException;

final class OrderService
{
    private const STATUSES = ['draft', 'confirmed', 'in_progress', 'done'];
    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function __construct(
        private readonly OrderRepository $repository,
        private readonly CustomerRepository $customerRepository,
        private readonly QuotationRepository $quotationRepository,
    ) {
    }

    public function list(?string $search = null, ?string $status = null): array
    {
        return $this->repository->search($search, $this->normalizeStatusFilter($status));
    }

    public function find(int $id): array
    {
        $order = $this->repository->findById($id);
        if ($order === null) {
            throw new HttpException('Order not found.', 404);
        }

        $order['items'] = $this->repository->findItemsByOrderId($id);

        return $order;
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data);
        $this->assertUniqueCode($payload['header']['code']);

        return $this->repository->create($payload['header'], $payload['items']);
    }

    public function update(int $id, array $data): void
    {
        $order = $this->find($id);
        $payload = $this->normalizePayload($data);

        if ($order['code'] !== $payload['header']['code']) {
            $this->assertUniqueCode($payload['header']['code']);
        }

        $this->repository->update($id, $payload['header'], $payload['items']);
    }

    public function delete(int $id): void
    {
        $this->find($id);

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Order cannot be deleted because related records already exist.', 409, [
                    'errors' => [
                        'order' => ['Order has related ERP records. Delete or unlink them first.'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    public function customerOptions(): array
    {
        return $this->customerRepository->options();
    }

    public function quotationOptions(): array
    {
        return $this->quotationRepository->options();
    }

    public function quotationPayload(): array
    {
        $quotations = $this->quotationRepository->options();
        $quotationIds = array_map(static fn (array $quotation): int => (int) $quotation['id'], $quotations);
        $items = $this->quotationRepository->findItemsByQuotationIds($quotationIds);
        $groupedItems = [];

        foreach ($items as $item) {
            $quotationId = (int) $item['quotation_id'];
            $groupedItems[$quotationId][] = [
                'description' => (string) $item['description'],
                'quantity' => number_format((float) $item['quantity'], 2, '.', ''),
                'unit_price' => number_format((float) $item['unit_price'], 2, '.', ''),
            ];
        }

        $payload = [];
        foreach ($quotations as $quotation) {
            $quotationId = (int) $quotation['id'];
            $payload[$quotationId] = [
                'id' => $quotationId,
                'code' => (string) $quotation['code'],
                'customer_id' => (int) $quotation['customer_id'],
                'discount_amount' => number_format((float) $quotation['discount_amount'], 2, '.', ''),
                'tax_amount' => number_format((float) $quotation['tax_amount'], 2, '.', ''),
                'items' => $groupedItems[$quotationId] ?? [],
            ];
        }

        return $payload;
    }

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function priorities(): array
    {
        return self::PRIORITIES;
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        if ($code === '') {
            $errors['code'][] = 'This field is required.';
        }

        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId <= 0 || $this->customerRepository->findById($customerId) === null) {
            $errors['customer_id'][] = 'Selected customer does not exist.';
        }

        $quotationId = $this->normalizeOptionalInt($data['quotation_id'] ?? null);
        if ($quotationId !== null) {
            $quotation = $this->quotationRepository->findById($quotationId);
            if ($quotation === null) {
                $errors['quotation_id'][] = 'Selected quotation does not exist.';
            } elseif ((int) $quotation['customer_id'] !== $customerId) {
                $errors['quotation_id'][] = 'Selected quotation does not belong to the chosen customer.';
            }
        }

        $status = strtolower(trim((string) ($data['status'] ?? '')));
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'][] = 'Selected status is invalid.';
        }

        $priority = strtolower(trim((string) ($data['priority'] ?? '')));
        if (!in_array($priority, self::PRIORITIES, true)) {
            $errors['priority'][] = 'Selected priority is invalid.';
        }

        $orderDate = $this->normalizeDate($data['order_date'] ?? null, 'order_date', true, $errors);
        $dueDate = $this->normalizeDate($data['due_date'] ?? null, 'due_date', false, $errors);
        $discountAmount = $this->normalizeDecimal($data['discount_amount'] ?? 0, 'discount_amount', true, $errors);
        $taxAmount = $this->normalizeDecimal($data['tax_amount'] ?? 0, 'tax_amount', true, $errors);
        $items = $this->normalizeItems($data['items'] ?? [], $errors);

        if ($discountAmount < 0) {
            $errors['discount_amount'][] = 'Discount amount must be zero or greater.';
        }

        if ($taxAmount < 0) {
            $errors['tax_amount'][] = 'Tax amount must be zero or greater.';
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) $item['total_amount'];
        }

        if ($discountAmount > $subtotal) {
            $errors['discount_amount'][] = 'Discount amount cannot exceed subtotal.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        return [
            'header' => [
                'code' => $code,
                'customer_id' => $customerId,
                'quotation_id' => $quotationId,
                'order_date' => $orderDate,
                'due_date' => $dueDate,
                'status' => $status,
                'priority' => $priority,
                'subtotal' => $this->formatDecimal($subtotal),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'tax_amount' => $this->formatDecimal($taxAmount),
                'total_amount' => $this->formatDecimal($totalAmount),
                'note' => $this->nullableString($data['note'] ?? null),
            ],
            'items' => $items,
        ];
    }

    private function normalizeItems(mixed $rawItems, array &$errors): array
    {
        if (!is_array($rawItems)) {
            $errors['items'][] = 'Order items are invalid.';

            return [];
        }

        $items = [];

        foreach ($rawItems as $index => $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $description = trim((string) ($rawItem['description'] ?? ''));
            $quantityRaw = $rawItem['quantity'] ?? '';
            $unitPriceRaw = $rawItem['unit_price'] ?? '';

            $isEmptyRow = $description === ''
                && trim((string) $quantityRaw) === ''
                && trim((string) $unitPriceRaw) === '';

            if ($isEmptyRow) {
                continue;
            }

            if ($description === '') {
                $errors["items.{$index}.description"][] = 'Description is required.';
            }

            $quantity = $this->normalizeDecimal($quantityRaw, "items.{$index}.quantity", false, $errors);
            $unitPrice = $this->normalizeDecimal($unitPriceRaw, "items.{$index}.unit_price", false, $errors);

            if ($quantity <= 0) {
                $errors["items.{$index}.quantity"][] = 'Quantity must be greater than zero.';
            }

            if ($unitPrice < 0) {
                $errors["items.{$index}.unit_price"][] = 'Unit price must be zero or greater.';
            }

            $items[] = [
                'description' => $description,
                'quantity' => $this->formatDecimal($quantity),
                'unit_price' => $this->formatDecimal($unitPrice),
                'total_amount' => $this->formatDecimal(round($quantity * $unitPrice, 2)),
            ];
        }

        if ($items === []) {
            $errors['items'][] = 'At least one order item is required.';
        }

        return $items;
    }

    private function normalizeDate(mixed $value, string $field, bool $required, array &$errors): ?string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            if ($required) {
                $errors[$field][] = 'This field is required.';
            }

            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            $errors[$field][] = 'Invalid date format.';

            return null;
        }

        return $date->format('Y-m-d');
    }

    private function normalizeDecimal(mixed $value, string $field, bool $nullable, array &$errors): float
    {
        $stringValue = trim((string) ($value ?? ''));

        if ($stringValue === '') {
            if ($nullable) {
                return 0.0;
            }

            $errors[$field][] = 'This field is required.';

            return 0.0;
        }

        if (!is_numeric($stringValue)) {
            $errors[$field][] = 'This field must be numeric.';

            return 0.0;
        }

        return round((float) $stringValue, 2);
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Order code already exists.', 422, [
                'errors' => [
                    'code' => ['Order code already exists.'],
                ],
            ]);
        }
    }

    private function normalizeStatusFilter(?string $status): ?string
    {
        if ($status === null || trim($status) === '') {
            return null;
        }

        $status = strtolower(trim($status));

        return in_array($status, self::STATUSES, true) ? $status : null;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }
}