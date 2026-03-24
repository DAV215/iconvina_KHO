<?php

declare(strict_types=1);

namespace App\Modules\Branch\Repositories;

use App\Core\Database\Repository;

final class BranchRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'b.code',
            'name' => 'b.name',
            'company_name' => 'c.name',
            'updated_at' => 'b.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'b.updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(b.code LIKE :search OR b.name LIKE :search OR c.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'deleted') {
            $conditions[] = 'b.deleted_at IS NOT NULL';
        } else {
            $conditions[] = 'b.deleted_at IS NULL';
            if ($status === 'active') {
                $conditions[] = 'b.is_active = 1';
            } elseif ($status === 'inactive') {
                $conditions[] = 'b.is_active = 0';
            }
        }

        $companyId = (int) ($filters['company_id'] ?? 0);
        if ($companyId > 0) {
            $conditions[] = 'b.company_id = :company_id';
            $params['company_id'] = $companyId;
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT b.*, c.name AS company_name, c.code AS company_code
             FROM branches b
             INNER JOIN companies c ON c.id = b.company_id%s
             ORDER BY %s %s, b.id DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM branches b INNER JOIN companies c ON c.id = b.company_id' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT b.*, c.name AS company_name, c.code AS company_code
             FROM branches b
             INNER JOIN companies c ON c.id = b.company_id
             WHERE b.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM branches WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('branches', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('branches', $id, $data);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT b.id, b.code, b.name, b.company_id, c.name AS company_name
             FROM branches b
             INNER JOIN companies c ON c.id = b.company_id
             WHERE b.deleted_at IS NULL
             ORDER BY b.name ASC, b.id ASC
             LIMIT 500'
        );
    }

    public function optionsByCompany(int $companyId): array
    {
        return $this->fetchAll(
            'SELECT b.id, b.code, b.name, b.company_id, c.name AS company_name
             FROM branches b
             INNER JOIN companies c ON c.id = b.company_id
             WHERE b.deleted_at IS NULL
               AND b.company_id = :company_id
             ORDER BY b.name ASC, b.id ASC
             LIMIT 500',
            ['company_id' => $companyId]
        );
    }

    public function companyExists(int $companyId): bool
    {
        return $this->fetchOne(
            'SELECT id
             FROM companies
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1',
            ['id' => $companyId]
        ) !== null;
    }

    public function hasDepartments(int $branchId): bool
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM departments
             WHERE branch_id = :branch_id
               AND deleted_at IS NULL',
            ['branch_id' => $branchId]
        );

        return (int) ($row['aggregate'] ?? 0) > 0;
    }
}
