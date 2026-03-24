<?php

declare(strict_types=1);

namespace App\Modules\InventoryBalance\Repositories;

use App\Core\Database\Repository;

final class InventoryBalanceRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'balance.code',
            'name' => 'balance.name',
            'current_qty' => 'balance.current_qty',
            'standard_cost' => 'balance.standard_cost',
            'stock_value' => 'balance.stock_value',
            'min_stock' => 'balance.min_stock',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'balance.code';
        $direction = strtoupper((string) ($sort['dir'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $baseSql = $this->balanceSql();
        $sql = 'SELECT balance.* FROM (' . $baseSql . ') balance';
        $countSql = 'SELECT COUNT(*) AS aggregate FROM (' . $baseSql . ') balance';

        $itemType = trim((string) ($filters['item_type'] ?? ''));
        if ($itemType !== '') {
            $conditions[] = 'balance.item_type = :item_type';
            $params['item_type'] = $itemType;
        }

        $code = trim((string) ($filters['code'] ?? ''));
        if ($code !== '') {
            $conditions[] = 'balance.code LIKE :code';
            $params['code'] = '%' . $code . '%';
        }

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $conditions[] = 'balance.name LIKE :name';
            $params['name'] = '%' . $name . '%';
        }

        $categoryId = trim((string) ($filters['category_id'] ?? ''));
        if ($categoryId !== '' && ctype_digit($categoryId)) {
            $conditions[] = 'balance.category_id = :category_id';
            $params['category_id'] = (int) $categoryId;
        }

        $stockStatus = trim((string) ($filters['stock_status'] ?? ''));
        if ($stockStatus === 'out_of_stock') {
            $conditions[] = 'balance.current_qty <= 0';
        } elseif ($stockStatus === 'low_stock') {
            $conditions[] = 'balance.current_qty > 0 AND balance.current_qty <= balance.min_stock';
        } elseif ($stockStatus === 'in_stock') {
            $conditions[] = 'balance.current_qty > balance.min_stock';
        }

        $isActive = (string) ($filters['is_active'] ?? '');
        if ($isActive === '1' || $isActive === '0') {
            $conditions[] = 'balance.is_active = :is_active';
            $params['is_active'] = (int) $isActive;
        }

        if ($conditions !== []) {
            $whereSql = ' WHERE ' . implode(' AND ', $conditions);
            $sql .= $whereSql;
            $countSql .= $whereSql;
        }

        $sql .= sprintf(
            ' ORDER BY %s %s, balance.item_type ASC, balance.code ASC LIMIT %d OFFSET %d',
            $orderBy,
            $direction,
            $perPage,
            $offset
        );

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function materialCategoryOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, parent_id
             FROM material_categories
             WHERE is_active = 1
             ORDER BY COALESCE(parent_id, id) ASC, parent_id ASC, name ASC, id ASC'
        );
    }

    private function balanceSql(): string
    {
        return <<<SQL
SELECT
    'material' AS item_type,
    m.id AS item_id,
    m.code AS code,
    m.name AS name,
    m.category_id AS category_id,
    mc.name AS category_name,
    m.unit AS unit,
    m.standard_cost AS standard_cost,
    COALESCE(m.min_stock, 0) AS min_stock,
    m.is_active AS is_active,
    COALESCE(movement.current_qty, 0) AS current_qty,
    ROUND(COALESCE(movement.current_qty, 0) * m.standard_cost, 2) AS stock_value
FROM materials m
LEFT JOIN material_categories mc ON mc.id = m.category_id
LEFT JOIN (
    SELECT
        sti.material_id AS item_id,
        SUM(
            CASE
                WHEN st.txn_type IN ('import', 'receipt') THEN sti.quantity
                WHEN st.txn_type IN ('export', 'issue') THEN -sti.quantity
                WHEN st.txn_type = 'adjustment' THEN sti.quantity
                ELSE 0
            END
        ) AS current_qty
    FROM stock_transaction_items sti
    INNER JOIN stock_transactions st ON st.id = sti.stock_transaction_id
    WHERE sti.item_kind = 'material'
      AND sti.material_id IS NOT NULL
    GROUP BY sti.material_id
) movement ON movement.item_id = m.id
UNION ALL
SELECT
    'component' AS item_type,
    c.id AS item_id,
    c.code AS code,
    c.name AS name,
    NULL AS category_id,
    NULL AS category_name,
    c.unit AS unit,
    c.standard_cost AS standard_cost,
    0 AS min_stock,
    c.is_active AS is_active,
    COALESCE(movement.current_qty, 0) AS current_qty,
    ROUND(COALESCE(movement.current_qty, 0) * c.standard_cost, 2) AS stock_value
FROM components c
LEFT JOIN (
    SELECT
        sti.component_id AS item_id,
        SUM(
            CASE
                WHEN st.txn_type IN ('import', 'receipt') THEN sti.quantity
                WHEN st.txn_type IN ('export', 'issue') THEN -sti.quantity
                WHEN st.txn_type = 'adjustment' THEN sti.quantity
                ELSE 0
            END
        ) AS current_qty
    FROM stock_transaction_items sti
    INNER JOIN stock_transactions st ON st.id = sti.stock_transaction_id
    WHERE sti.item_kind = 'component'
      AND sti.component_id IS NOT NULL
    GROUP BY sti.component_id
) movement ON movement.item_id = c.id
SQL;
    }
}
