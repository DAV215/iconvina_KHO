<?php

declare(strict_types=1);

namespace App\Modules\Bom\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Bom\Repositories\BomRepository;
use App\Modules\Component\Repositories\ComponentRepository;
use App\Modules\Material\Repositories\MaterialRepository;
use PDOException;

final class BomService
{
    private const ITEM_KINDS = ['material', 'component'];

    public function __construct(
        private readonly BomRepository $repository,
        private readonly ComponentRepository $componentRepository,
        private readonly MaterialRepository $materialRepository,
    ) {
    }

    public function list(?int $componentId = null, ?string $version = null, int $page = 1, int $perPage = 25): array
    {
        $result = $this->repository->search($componentId, $version, $page, $perPage);
        $result['items'] = array_map(fn (array $bom): array => $this->appendDisplayName($bom), $result['items']);

        return $result;
    }

    public function find(int $id): array
    {
        $bom = $this->repository->findById($id);
        if ($bom === null) {
            throw new HttpException('Không tìm thấy BOM.', 404);
        }

        $bom = $this->appendDisplayName($bom);
        $bom['items'] = $this->repository->findItemsByBomId($id);

        return $bom;
    }

    public function tree(int $id): array
    {
        $bom = $this->find($id);

        return [
            'bom' => $bom,
            'tree' => $this->buildComponentNode($bom, []),
        ];
    }

    public function create(array $data): int
    {
        $payload = $this->normalizePayload($data);

        return $this->repository->create($payload['header'], $payload['items']);
    }

    public function update(int $id, array $data): void
    {
        $this->find($id);
        $payload = $this->normalizePayload($data);

        $this->repository->update($id, $payload['header'], $payload['items']);
    }

    public function delete(int $id): void
    {
        $this->find($id);

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Không thể xóa BOM vì đã có dữ liệu liên quan.', 409, [
                    'errors' => [
                        'bom' => ['BOM đang được tham chiếu trong hệ thống.'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    public function componentOptions(): array
    {
        return $this->componentRepository->options();
    }

    public function materialOptions(): array
    {
        return $this->materialRepository->options();
    }

    private function normalizePayload(array $data): array
    {
        $errors = [];

        $componentId = (int) ($data['component_id'] ?? 0);
        $component = $componentId > 0 ? $this->componentRepository->findById($componentId) : null;
        if ($component === null) {
            $errors['component_id'][] = 'Bán thành phẩm đã chọn không tồn tại.';
        }

        $version = trim((string) ($data['version'] ?? ''));
        if ($version === '') {
            $errors['version'][] = 'Version là bắt buộc.';
        }

        $items = $this->normalizeItems($data['items'] ?? [], $componentId, $errors);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'header' => [
                'component_id' => $componentId,
                'version' => $version,
                'is_active' => $this->normalizeBoolInt($data['is_active'] ?? 0),
            ],
            'items' => $items,
        ];
    }

    private function normalizeItems(mixed $rawItems, int $parentComponentId, array &$errors): array
    {
        if (!is_array($rawItems)) {
            $errors['items'][] = 'Danh sách BOM items không hợp lệ.';

            return [];
        }

        $items = [];

        foreach ($rawItems as $index => $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            $itemKind = strtolower(trim((string) ($rawItem['item_kind'] ?? 'material')));
            $materialIdRaw = trim((string) ($rawItem['material_id'] ?? ''));
            $componentIdRaw = trim((string) ($rawItem['component_id'] ?? ''));
            $quantityRaw = trim((string) ($rawItem['quantity'] ?? ''));
            $note = $this->nullableString($rawItem['note'] ?? null);

            $isEmptyRow = $materialIdRaw === ''
                && $componentIdRaw === ''
                && $quantityRaw === ''
                && $note === null;

            if ($isEmptyRow) {
                continue;
            }

            if (!in_array($itemKind, self::ITEM_KINDS, true)) {
                $errors["items.{$index}.item_kind"][] = 'Loại item không hợp lệ.';
                continue;
            }

            $materialId = $this->normalizeOptionalInt($materialIdRaw);
            $componentId = $this->normalizeOptionalInt($componentIdRaw);
            $quantity = $this->normalizeDecimal($quantityRaw, "items.{$index}.quantity", $errors);

            if ($quantity <= 0) {
                $errors["items.{$index}.quantity"][] = 'Số lượng phải lớn hơn 0.';
            }

            if ($itemKind === 'material') {
                if ($materialId === null || $this->materialRepository->findById($materialId) === null) {
                    $errors["items.{$index}.material_id"][] = 'Nguyên vật liệu đã chọn không tồn tại.';
                }

                if ($componentId !== null) {
                    $errors["items.{$index}.component_id"][] = 'Không được chọn bán thành phẩm khi loại là material.';
                }

                $componentId = null;
            }

            if ($itemKind === 'component') {
                if ($componentId === null || $this->componentRepository->findById($componentId) === null) {
                    $errors["items.{$index}.component_id"][] = 'Bán thành phẩm đã chọn không tồn tại.';
                } elseif ($componentId === $parentComponentId) {
                    $errors["items.{$index}.component_id"][] = 'Không được chọn chính bán thành phẩm cha.';
                }

                if ($materialId !== null) {
                    $errors["items.{$index}.material_id"][] = 'Không được chọn nguyên vật liệu khi loại là component.';
                }

                $materialId = null;
            }

            $items[] = [
                'item_kind' => $itemKind,
                'material_id' => $materialId,
                'component_id' => $componentId,
                'quantity' => $this->formatDecimal($quantity),
                'note' => $note,
            ];
        }

        if ($items === []) {
            $errors['items'][] = 'Cần ít nhất 1 dòng BOM hợp lệ.';
        }

        return $items;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function normalizeDecimal(mixed $value, string $field, array &$errors): float
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            $errors[$field][] = 'Số lượng là bắt buộc.';

            return 0.0;
        }

        if (!is_numeric($value)) {
            $errors[$field][] = 'Số lượng phải là số.';

            return 0.0;
        }

        return round((float) $value, 2);
    }

    private function normalizeBoolInt(mixed $value): int
    {
        return (string) $value === '1' ? 1 : 0;
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

    private function appendDisplayName(array $bom): array
    {
        $componentName = trim((string) ($bom['component_name'] ?? ''));
        $version = trim((string) ($bom['version'] ?? ''));
        $bom['bom_name'] = trim($componentName . ($version !== '' ? ' - ' . $version : ''));

        return $bom;
    }

    private function buildComponentNode(array $bom, array $visitedBomIds): array
    {
        $bomId = (int) $bom['id'];
        $visitedBomIds[$bomId] = true;
        $items = $this->repository->findItemsByBomId($bomId);
        $children = [];

        foreach ($items as $item) {
            $children[] = $this->buildItemNode($item, $visitedBomIds);
        }

        return [
            'node_type' => 'component',
            'id' => (int) $bom['component_id'],
            'bom_id' => $bomId,
            'name' => (string) $bom['component_name'],
            'code' => (string) $bom['component_code'],
            'quantity' => '1.00',
            'note' => null,
            'image_path' => $bom['component_image_path'] ?? null,
            'version' => (string) $bom['version'],
            'children' => $children,
        ];
    }

    private function buildItemNode(array $item, array $visitedBomIds): array
    {
        if ((string) $item['item_kind'] === 'component') {
            $activeBom = $this->repository->findActiveByComponentId((int) $item['component_id']);
            $children = [];

            if ($activeBom !== null && !isset($visitedBomIds[(int) $activeBom['id']])) {
                $children = $this->buildComponentNode($this->appendDisplayName($activeBom), $visitedBomIds)['children'];
            }

            return [
                'node_type' => 'component',
                'id' => (int) $item['component_id'],
                'bom_id' => $activeBom['id'] ?? null,
                'name' => (string) ($item['child_component_name'] ?? ''),
                'code' => (string) ($item['child_component_code'] ?? ''),
                'quantity' => $this->formatDecimal((float) $item['quantity']),
                'note' => $item['note'] ?? null,
                'image_path' => $item['child_component_image_path'] ?? null,
                'version' => $activeBom['version'] ?? null,
                'children' => $children,
            ];
        }

        return [
            'node_type' => 'material',
            'id' => (int) $item['material_id'],
            'bom_id' => null,
            'name' => (string) ($item['material_name'] ?? ''),
            'code' => (string) ($item['material_code'] ?? ''),
            'quantity' => $this->formatDecimal((float) $item['quantity']),
            'note' => $item['note'] ?? null,
            'image_path' => null,
            'version' => null,
            'children' => [],
        ];
    }
}
