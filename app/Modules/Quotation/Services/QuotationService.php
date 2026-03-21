<?php

declare(strict_types=1);

namespace App\Modules\Quotation\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Customer\Repositories\CustomerRepository;
use App\Modules\Quotation\Repositories\QuotationRepository;
use DateTimeImmutable;
use PDOException;

final class QuotationService
{
    private const STATUSES = ['draft', 'sent', 'approved', 'rejected'];

    public function __construct(
        private readonly QuotationRepository $repository,
        private readonly CustomerRepository $customerRepository,
    ) {
    }

    public function list(?string $search = null, ?string $status = null): array
    {
        return $this->repository->search($search, $this->normalizeStatusFilter($status));
    }

    public function find(int $id): array
    {
        $quotation = $this->repository->findById($id);
        if ($quotation === null) {
            throw new HttpException('Quotation not found.', 404);
        }

        $quotation['items'] = $this->repository->findItemsByQuotationId($id);

        return $quotation;
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data);
        $this->assertUniqueCode($payload['header']['code']);

        $payload['header']['created_at'] = $this->timestamp();
        $payload['header']['updated_at'] = $payload['header']['created_at'];

        return $this->repository->create($payload['header'], $payload['items']);
    }

    public function update(int $id, array $data): void
    {
        $quotation = $this->find($id);
        $payload = $this->normalizePayload($data);

        if ($quotation['code'] !== $payload['header']['code']) {
            $this->assertUniqueCode($payload['header']['code']);
        }

        $payload['header']['updated_at'] = $this->timestamp();

        $this->repository->update($id, $payload['header'], $payload['items']);
    }

    public function delete(int $id): void
    {
        $this->find($id);

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Quotation cannot be deleted because related records already exist.', 409, [
                    'errors' => [
                        'quotation' => ['Quotation has related ERP records. Delete or unlink them first.'],
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

    public function statuses(): array
    {
        return self::STATUSES;
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId <= 0 || $this->customerRepository->findById($customerId) === null) {
            $errors['customer_id'][] = 'Selected customer does not exist.';
        }

        $status = strtolower(trim((string) ($data['status'] ?? '')));
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'][] = 'Selected status is invalid.';
        }

        $quoteDate = $this->normalizeDate($data['quote_date'] ?? null, 'quote_date', true, $errors);
        $expiredAt = $this->normalizeDate($data['expired_at'] ?? null, 'expired_at', false, $errors);
        $taxAmount = $this->normalizeDecimal($data['tax_amount'] ?? 0, 'tax_amount', true, $errors);
        $items = $this->normalizeItems($data['items'] ?? [], $errors);

        if ($taxAmount < 0) {
            $errors['tax_amount'][] = 'Tax amount must be zero or greater.';
        }

        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        if ($code === '') {
            $errors['code'][] = 'This field is required.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $subtotal = 0.0;
        $discountAmount = 0.0;

        foreach ($items as $item) {
            $lineGross = ((float) $item['quantity']) * ((float) $item['unit_price']);
            $subtotal += $lineGross;
            $discountAmount += (float) $item['discount_amount'];
        }

        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        return [
            'header' => [
                'code' => $code,
                'customer_id' => $customerId,
                'quote_date' => $quoteDate,
                'expired_at' => $expiredAt,
                'status' => $status,
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
            $errors['items'][] = 'Quotation items are invalid.';

            return [];
        }

        $items = [];
        $lineNo = 1;

        foreach ($rawItems as $index => $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $itemType = trim((string) ($rawItem['item_type'] ?? ''));
            $description = trim((string) ($rawItem['description'] ?? ''));
            $unit = trim((string) ($rawItem['unit'] ?? ''));
            $quantityRaw = $rawItem['quantity'] ?? '';
            $unitPriceRaw = $rawItem['unit_price'] ?? '';
            $discountRaw = $rawItem['discount_amount'] ?? 0;

            $isEmptyRow = $itemType === ''
                && $description === ''
                && $unit === ''
                && trim((string) $quantityRaw) === ''
                && trim((string) $unitPriceRaw) === ''
                && trim((string) $discountRaw) === '';

            if ($isEmptyRow) {
                continue;
            }

            if ($itemType === '') {
                $errors["items.{$index}.item_type"][] = 'Item type is required.';
            }

            if ($description === '') {
                $errors["items.{$index}.description"][] = 'Description is required.';
            }

            if ($unit === '') {
                $errors["items.{$index}.unit"][] = 'Unit is required.';
            }

            $quantity = $this->normalizeDecimal($quantityRaw, "items.{$index}.quantity", false, $errors);
            $unitPrice = $this->normalizeDecimal($unitPriceRaw, "items.{$index}.unit_price", false, $errors);
            $discountAmount = $this->normalizeDecimal($discountRaw, "items.{$index}.discount_amount", true, $errors);

            if ($quantity <= 0) {
                $errors["items.{$index}.quantity"][] = 'Quantity must be greater than zero.';
            }

            if ($unitPrice < 0) {
                $errors["items.{$index}.unit_price"][] = 'Unit price must be zero or greater.';
            }

            if ($discountAmount < 0) {
                $errors["items.{$index}.discount_amount"][] = 'Discount amount must be zero or greater.';
            }

            $grossAmount = round($quantity * $unitPrice, 2);
            if ($discountAmount > $grossAmount) {
                $errors["items.{$index}.discount_amount"][] = 'Discount amount cannot exceed line amount.';
            }

            $items[] = [
                'line_no' => $lineNo++,
                'item_type' => $itemType,
                'description' => $description,
                'unit' => $unit,
                'quantity' => $this->formatDecimal($quantity),
                'unit_price' => $this->formatDecimal($unitPrice),
                'discount_amount' => $this->formatDecimal($discountAmount),
                'total_amount' => $this->formatDecimal(max($grossAmount - $discountAmount, 0)),
            ];
        }

        if ($items === []) {
            $errors['items'][] = 'At least one quotation item is required.';
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
            throw new HttpException('Quotation code already exists.', 422, [
                'errors' => [
                    'code' => ['Quotation code already exists.'],
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

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}