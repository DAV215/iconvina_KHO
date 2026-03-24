<?php

declare(strict_types=1);

namespace App\Modules\Company\Repositories;

use App\Core\Database\Repository;

final class CompanyRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'code',
            'name' => 'name',
            'updated_at' => 'updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(code LIKE :search OR name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'deleted') {
            $conditions[] = 'deleted_at IS NOT NULL';
        } else {
            $conditions[] = 'deleted_at IS NULL';
            if ($status === 'active') {
                $conditions[] = 'is_active = 1';
            } elseif ($status === 'inactive') {
                $conditions[] = 'is_active = 0';
            }
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT * FROM companies%s ORDER BY %s %s, id DESC LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM companies' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM companies WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM companies WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('companies', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('companies', $id, $data);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name
             FROM companies
             WHERE deleted_at IS NULL
             ORDER BY name ASC, id ASC
             LIMIT 500'
        );
    }

    public function hasBranches(int $companyId): bool
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM branches
             WHERE company_id = :company_id
               AND deleted_at IS NULL',
            ['company_id' => $companyId]
        );

        return (int) ($row['aggregate'] ?? 0) > 0;
    }
}
