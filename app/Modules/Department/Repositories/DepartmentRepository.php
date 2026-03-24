<?php

declare(strict_types=1);

namespace App\Modules\Department\Repositories;

use App\Core\Database\Repository;

final class DepartmentRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'd.code',
            'name' => 'd.name',
            'branch_name' => 'b.name',
            'updated_at' => 'd.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'd.updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(d.code LIKE :search OR d.name LIKE :search OR b.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'deleted') {
            $conditions[] = 'd.deleted_at IS NOT NULL';
        } else {
            $conditions[] = 'd.deleted_at IS NULL';
            if ($status === 'active') {
                $conditions[] = 'd.is_active = 1';
            } elseif ($status === 'inactive') {
                $conditions[] = 'd.is_active = 0';
            }
        }

        $branchId = (int) ($filters['branch_id'] ?? 0);
        if ($branchId > 0) {
            $conditions[] = 'd.branch_id = :branch_id';
            $params['branch_id'] = $branchId;
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT d.*, b.name AS branch_name, b.code AS branch_code, p.name AS parent_name
             FROM departments d
             INNER JOIN branches b ON b.id = d.branch_id
             LEFT JOIN departments p ON p.id = d.parent_id%s
             ORDER BY %s %s, d.id DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM departments d INNER JOIN branches b ON b.id = d.branch_id LEFT JOIN departments p ON p.id = d.parent_id' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT d.*, b.name AS branch_name, b.code AS branch_code, p.name AS parent_name
             FROM departments d
             INNER JOIN branches b ON b.id = d.branch_id
             LEFT JOIN departments p ON p.id = d.parent_id
             WHERE d.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM departments WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('departments', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('departments', $id, $data);
    }

    public function options(?int $excludeId = null): array
    {
        $params = [];
        $sql = 'SELECT d.id, d.code, d.name, d.branch_id, b.name AS branch_name
                FROM departments d
                INNER JOIN branches b ON b.id = d.branch_id
                WHERE d.deleted_at IS NULL';

        if ($excludeId !== null) {
            $sql .= ' AND d.id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' ORDER BY b.name ASC, d.name ASC, d.id ASC LIMIT 500';

        return $this->fetchAll($sql, $params);
    }

    public function optionsByBranch(int $branchId): array
    {
        return $this->fetchAll(
            'SELECT d.id, d.code, d.name, d.branch_id, b.name AS branch_name
             FROM departments d
             INNER JOIN branches b ON b.id = d.branch_id
             WHERE d.deleted_at IS NULL
               AND d.branch_id = :branch_id
             ORDER BY d.name ASC, d.id ASC
             LIMIT 500',
            ['branch_id' => $branchId]
        );
    }

    public function branchExists(int $branchId): bool
    {
        return $this->fetchOne(
            'SELECT id
             FROM branches
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1',
            ['id' => $branchId]
        ) !== null;
    }

    public function parentExists(int $parentId): bool
    {
        return $this->fetchOne(
            'SELECT id
             FROM departments
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1',
            ['id' => $parentId]
        ) !== null;
    }

    public function hasChildren(int $departmentId): bool
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM departments
             WHERE parent_id = :parent_id
               AND deleted_at IS NULL',
            ['parent_id' => $departmentId]
        );

        return (int) ($row['aggregate'] ?? 0) > 0;
    }
}
