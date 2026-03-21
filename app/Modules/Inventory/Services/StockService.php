<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Inventory\Repositories\StockRepository;
use DateTimeImmutable;
use PDOException;

final class StockService
{
    private const TXN_TYPES = ['import', 'export', 'adjustment'];
    private const ITEM_KINDS = ['material', 'component'];

    public function __construct(private readonly StockRepository $repository)
    {
    }

    public function list(?string $search = null, ?string $txnType = null): array
    {
        return $this->repository->search($search, $this->normalizeTxnTypeFilter($txnType));
    }

    public function find(int $id): array
    {
        $transaction = $this->repository->findById($id);
        if ($transaction === null) {
            throw new HttpException('Stock transaction not found.', 404);
        }

        $transaction['items'] = $this->repository->findItemsByTransactionId($id);

        return $transaction;
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data);
        $this->assertUniqueTxnNo($payload['header']['txn_no']);

        return $this->repository->create($payload['header'], $payload['items']);
    }

    public function update(int $id, array $data): void
    {
        $transaction = $this->find($id);
        $payload = $this->normalizePayload($data);

        if ($transaction['txn_no'] !== $payload['header']['txn_no']) {
            $this->assertUniqueTxnNo($payload['header']['txn_no']);
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
                throw new HttpException('Stock transaction cannot be deleted because related records already exist.', 409, [
                    'errors' => [
                        'stock' => ['Stock transaction has related ERP records. Delete or unlink them first.'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    public function materialOptions(): array
    {
        return $this->repository->materialOptions();
    }

    public function componentOptions(): array
    {
        return $this->repository->componentOptions();
    }

    public function itemPayload(): array
    {
        $materials = [];
        foreach ($this->repository->materialOptions() as $material) {
            $materials[(int) $material['id']] = [
                'id' => (int) $material['id'],
                'code' => (string) $material['code'],
                'name' => (string) $material['name'],
                'unit' => (string) $material['unit'],
                'standard_cost' => number_format((float) $material['standard_cost'], 2, '.', ''),
            ];
        }

        $components = [];
        foreach ($this->repository->componentOptions() as $component) {
            $components[(int) $component['id']] = [
                'id' => (int) $component['id'],
                'code' => (string) $component['code'],
                'name' => (string) $component['name'],
                'component_type' => (string) $component['component_type'],
                'standard_cost' => number_format((float) $component['standard_cost'], 2, '.', ''),
            ];
        }

        return [
            'materials' => $materials,
            'components' => $components,
        ];
    }

    public function txnTypes(): array
    {
        return self::TXN_TYPES;
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];
        $txnNo = strtoupper(trim((string) ($data['txn_no'] ?? '')));
        if ($txnNo === '') {
            $errors['txn_no'][] = 'This field is required.';
        }

        $txnType = strtolower(trim((string) ($data['txn_type'] ?? '')));
        if (!in_array($txnType, self::TXN_TYPES, true)) {
            $errors['txn_type'][] = 'Selected transaction type is invalid.';
        }

        $txnDate = $this->normalizeDate($data['txn_date'] ?? null, 'txn_date', true, $errors);
        $refType = $this->nullableString($data['ref_type'] ?? null);
        $refId = $this->normalizeOptionalInt($data['ref_id'] ?? null, 'ref_id', $errors);
        $items = $this->normalizeItems($data['items'] ?? [], $errors);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'header' => [
                'txn_no' => $txnNo,
                'txn_type' => $txnType,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'txn_date' => $txnDate,
                'note' => $this->nullableString($data['note'] ?? null),
            ],
            'items' => $items,
        ];
    }

    private function normalizeItems(mixed $rawItems, array &$errors): array
    {
        if (!is_array($rawItems)) {
            $errors['items'][] = 'Stock items are invalid.';

            return [];
        }

        $items = [];

        foreach ($rawItems as $index => $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $itemKind = strtolower(trim((string) ($rawItem['item_kind'] ?? '')));
            $materialId = $this->normalizeOptionalInt($rawItem['material_id'] ?? null);
            $componentId = $this->normalizeOptionalInt($rawItem['component_id'] ?? null);
            $quantityRaw = $rawItem['quantity'] ?? '';
            $unitCostRaw = $rawItem['unit_cost'] ?? '';

            $isEmptyRow = $itemKind === ''
                && $materialId === null
                && $componentId === null
                && trim((string) $quantityRaw) === ''
                && trim((string) $unitCostRaw) === '';

            if ($isEmptyRow) {
                continue;
            }

            if (!in_array($itemKind, self::ITEM_KINDS, true)) {
                $errors["items.{$index}.item_kind"][] = 'Item kind is invalid.';
            }

            $quantity = $this->normalizeDecimal($quantityRaw, "items.{$index}.quantity", false, $errors);
            $unitCost = $this->normalizeDecimal($unitCostRaw, "items.{$index}.unit_cost", false, $errors);

            if ($quantity <= 0) {
                $errors["items.{$index}.quantity"][] = 'Quantity must be greater than zero.';
            }

            if ($unitCost < 0) {
                $errors["items.{$index}.unit_cost"][] = 'Unit cost must be zero or greater.';
            }

            if ($itemKind === 'material') {
                if ($materialId === null) {
                    $errors["items.{$index}.material_id"][] = 'Material is required.';
                }

                if ($componentId !== null) {
                    $errors["items.{$index}.component_id"][] = 'Component must be empty when item kind is material.';
                }

                if ($materialId !== null) {
                    $material = $this->repository->findMaterialById($materialId);
                    if ($material === null || (int) ($material['is_active'] ?? 0) !== 1) {
                        $errors["items.{$index}.material_id"][] = 'Selected material does not exist.';
                    }
                }
            }

            if ($itemKind === 'component') {
                if ($componentId === null) {
                    $errors["items.{$index}.component_id"][] = 'Component is required.';
                }

                if ($materialId !== null) {
                    $errors["items.{$index}.material_id"][] = 'Material must be empty when item kind is component.';
                }

                if ($componentId !== null) {
                    $component = $this->repository->findComponentById($componentId);
                    if ($component === null || (int) ($component['is_active'] ?? 0) !== 1) {
                        $errors["items.{$index}.component_id"][] = 'Selected component does not exist.';
                    }
                }
            }

            $items[] = [
                'item_kind' => $itemKind,
                'material_id' => $itemKind === 'material' ? $materialId : null,
                'component_id' => $itemKind === 'component' ? $componentId : null,
                'quantity' => $this->formatDecimal($quantity),
                'unit_cost' => $this->formatDecimal($unitCost),
                'line_total' => $this->formatDecimal(round($quantity * $unitCost, 2)),
            ];
        }

        if ($items === []) {
            $errors['items'][] = 'At least one stock item is required.';
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

    private function normalizeOptionalInt(mixed $value, ?string $field = null, array &$errors = []): ?int
    {
        $stringValue = trim((string) ($value ?? ''));

        if ($stringValue === '') {
            return null;
        }

        if (!is_numeric($stringValue)) {
            if ($field !== null) {
                $errors[$field][] = 'This field must be numeric.';
            }

            return null;
        }

        return (int) $stringValue;
    }

    private function assertUniqueTxnNo(string $txnNo): void
    {
        if ($this->repository->findByTxnNo($txnNo) !== null) {
            throw new HttpException('Transaction number already exists.', 422, [
                'errors' => [
                    'txn_no' => ['Transaction number already exists.'],
                ],
            ]);
        }
    }

    private function normalizeTxnTypeFilter(?string $txnType): ?string
    {
        if ($txnType === null || trim($txnType) === '') {
            return null;
        }

        $txnType = strtolower(trim($txnType));

        return in_array($txnType, self::TXN_TYPES, true) ? $txnType : null;
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