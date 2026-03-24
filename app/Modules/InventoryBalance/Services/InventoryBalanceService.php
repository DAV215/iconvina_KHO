<?php

declare(strict_types=1);

namespace App\Modules\InventoryBalance\Services;

use App\Modules\InventoryBalance\Repositories\InventoryBalanceRepository;

final class InventoryBalanceService
{
    private const ITEM_TYPES = [
        'material' => 'Vật tư',
        'component' => 'Bán thành phẩm',
    ];

    private const STOCK_STATUSES = [
        'out_of_stock' => 'Hết hàng',
        'low_stock' => 'Thấp hơn tồn tối thiểu',
        'in_stock' => 'Còn hàng',
    ];

    private const STOCK_STATUS_BADGES = [
        'out_of_stock' => 'is-inactive',
        'low_stock' => 'is-pending',
        'in_stock' => 'is-active',
    ];

    public function __construct(private readonly InventoryBalanceRepository $repository)
    {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $normalizedSort = $this->normalizeSort($sort);
        $list = $this->repository->search($normalizedFilters, $normalizedSort, $page, $perPage);

        foreach ($list['items'] as &$item) {
            $currentQty = round((float) ($item['current_qty'] ?? 0), 2);
            $minStock = round((float) ($item['min_stock'] ?? 0), 2);
            $status = $this->resolveStockStatus($currentQty, $minStock);
            $itemType = (string) ($item['item_type'] ?? 'material');
            $itemId = (int) ($item['item_id'] ?? 0);

            $item['current_qty'] = $currentQty;
            $item['stock_value'] = round((float) ($item['stock_value'] ?? 0), 2);
            $item['item_type_label'] = self::ITEM_TYPES[$itemType] ?? $itemType;
            $item['stock_status'] = $status;
            $item['stock_status_label'] = self::STOCK_STATUSES[$status] ?? $status;
            $item['stock_status_badge'] = self::STOCK_STATUS_BADGES[$status] ?? '';
            $item['category_display'] = $itemType === 'material'
                ? ((string) ($item['category_name'] ?? '') !== '' ? (string) $item['category_name'] : 'Chưa phân loại')
                : '-';
            $item['detail_url'] = $itemId > 0
                ? app_url($itemType === 'material' ? '/materials/show?id=' . $itemId : '/components/show?id=' . $itemId)
                : null;
        }
        unset($item);

        return [
            'items' => $list['items'],
            'total' => (int) ($list['total'] ?? 0),
            'filters' => $normalizedFilters,
            'sort' => $normalizedSort,
        ];
    }

    public function itemTypes(): array
    {
        return self::ITEM_TYPES;
    }

    public function stockStatusOptions(): array
    {
        return self::STOCK_STATUSES;
    }

    public function materialCategoryOptions(): array
    {
        return $this->flattenMaterialCategoryOptions($this->repository->materialCategoryOptions());
    }

    private function normalizeFilters(array $filters): array
    {
        $itemType = strtolower(trim((string) ($filters['item_type'] ?? '')));
        if (!array_key_exists($itemType, self::ITEM_TYPES)) {
            $itemType = '';
        }

        $stockStatus = strtolower(trim((string) ($filters['stock_status'] ?? '')));
        if (!array_key_exists($stockStatus, self::STOCK_STATUSES)) {
            $stockStatus = '';
        }

        $categoryId = trim((string) ($filters['category_id'] ?? ''));
        if ($categoryId !== '' && !ctype_digit($categoryId)) {
            $categoryId = '';
        }

        $isActive = (string) ($filters['is_active'] ?? '');
        if ($isActive !== '1' && $isActive !== '0') {
            $isActive = '';
        }

        return [
            'item_type' => $itemType,
            'code' => trim((string) ($filters['code'] ?? '')),
            'name' => trim((string) ($filters['name'] ?? '')),
            'category_id' => $categoryId,
            'stock_status' => $stockStatus,
            'is_active' => $isActive,
        ];
    }

    private function normalizeSort(array $sort): array
    {
        $allowed = ['code', 'name', 'current_qty', 'standard_cost', 'stock_value', 'min_stock'];
        $by = strtolower(trim((string) ($sort['by'] ?? 'code')));
        $dir = strtolower(trim((string) ($sort['dir'] ?? 'asc')));

        return [
            'by' => in_array($by, $allowed, true) ? $by : 'code',
            'dir' => $dir === 'desc' ? 'desc' : 'asc',
        ];
    }

    private function resolveStockStatus(float $currentQty, float $minStock): string
    {
        if ($currentQty <= 0) {
            return 'out_of_stock';
        }

        if ($currentQty <= $minStock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    private function flattenMaterialCategoryOptions(array $rows): array
    {
        $children = [];
        foreach ($rows as $row) {
            $parentId = $row['parent_id'] ?? null;
            $key = $parentId === null ? 'root' : (string) $parentId;
            $children[$key][] = $row;
        }

        foreach ($children as &$siblings) {
            usort($siblings, static function (array $left, array $right): int {
                return [(string) ($left['name'] ?? ''), (int) ($left['id'] ?? 0)] <=> [(string) ($right['name'] ?? ''), (int) ($right['id'] ?? 0)];
            });
        }
        unset($siblings);

        $flattened = [];
        $visited = [];
        $walker = function (?int $parentId, int $depth) use (&$walker, &$flattened, &$children, &$visited): void {
            $key = $parentId === null ? 'root' : (string) $parentId;
            foreach ($children[$key] ?? [] as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id <= 0 || isset($visited[$id])) {
                    continue;
                }

                $visited[$id] = true;
                $row['label'] = ($depth > 0 ? str_repeat('-- ', $depth) : '') . (string) ($row['name'] ?? '');
                $flattened[] = $row;
                $walker($id, $depth + 1);
            }
        };

        $walker(null, 0);

        return $flattened;
    }
}
