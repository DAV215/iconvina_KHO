<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Repositories;

use App\Core\Database\Repository;

final class SupplierRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'code',
            'name' => 'name',
            'contact_name' => 'contact_name',
            'phone' => 'phone',
            'email' => 'email',
            'updated_at' => 'updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(code LIKE :search OR name LIKE :search OR contact_name LIKE :search OR phone LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === '1' || $status === '0') {
            $conditions[] = 'is_active = :is_active';
            $params['is_active'] = (int) $status;
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT * FROM suppliers%s ORDER BY %s %s, id DESC LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM suppliers' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM suppliers WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM suppliers WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT *
             FROM suppliers
             WHERE is_active = 1
             ORDER BY name ASC, id ASC
             LIMIT 500'
        );
    }

    public function create(array $data): int
    {
        return $this->insert('suppliers', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('suppliers', $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById('suppliers', $id);
    }

    public function hasLinkedPurchaseOrders(int $supplierId): bool
    {
        $columnExists = $this->fetchOne(
            'SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = :column_name
             LIMIT 1',
            [
                'table_name' => 'purchase_orders',
                'column_name' => 'supplier_id',
            ]
        );

        if ($columnExists === null) {
            return false;
        }

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM purchase_orders
             WHERE supplier_id = :supplier_id',
            ['supplier_id' => $supplierId]
        );

        return (int) ($row['aggregate'] ?? 0) > 0;
    }
}
