<?php

declare(strict_types=1);

namespace App\Modules\Position\Repositories;

use App\Core\Database\Repository;

final class PositionRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'p.code',
            'name' => 'p.name',
            'department_name' => 'd.name',
            'updated_at' => 'p.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'p.updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(p.code LIKE :search OR p.name LIKE :search OR d.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'deleted') {
            $conditions[] = 'p.deleted_at IS NOT NULL';
        } else {
            $conditions[] = 'p.deleted_at IS NULL';
            if ($status === 'active') {
                $conditions[] = 'p.is_active = 1';
            } elseif ($status === 'inactive') {
                $conditions[] = 'p.is_active = 0';
            }
        }

        $departmentId = (int) ($filters['department_id'] ?? 0);
        if ($departmentId > 0) {
            $conditions[] = 'p.department_id = :department_id';
            $params['department_id'] = $departmentId;
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT p.*, d.name AS department_name, d.code AS department_code
             FROM positions p
             LEFT JOIN departments d ON d.id = p.department_id%s
             ORDER BY %s %s, p.id DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM positions p LEFT JOIN departments d ON d.id = p.department_id' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) ($this->fetchOne($countSql, $params)['aggregate'] ?? 0),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, d.name AS department_name, d.code AS department_code
             FROM positions p
             LEFT JOIN departments d ON d.id = p.department_id
             WHERE p.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM positions WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('positions', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('positions', $id, $data);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT p.id, p.code, p.name, p.department_id, d.name AS department_name
             FROM positions p
             LEFT JOIN departments d ON d.id = p.department_id
             WHERE p.deleted_at IS NULL
             ORDER BY p.name ASC, p.id ASC
             LIMIT 500'
        );
    }

    public function optionsByDepartment(int $departmentId): array
    {
        return $this->fetchAll(
            'SELECT p.id, p.code, p.name, p.department_id, d.name AS department_name
             FROM positions p
             LEFT JOIN departments d ON d.id = p.department_id
             WHERE p.deleted_at IS NULL
               AND p.department_id = :department_id
             ORDER BY p.name ASC, p.id ASC
             LIMIT 500',
            ['department_id' => $departmentId]
        );
    }

    public function departmentExists(int $departmentId): bool
    {
        return $this->fetchOne(
            'SELECT id
             FROM departments
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1',
            ['id' => $departmentId]
        ) !== null;
    }

    public function hasUsers(int $positionId): bool
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM users
             WHERE position_id = :position_id
               AND deleted_at IS NULL
               AND status <> :deleted_status',
            [
                'position_id' => $positionId,
                'deleted_status' => 'deleted',
            ]
        );

        return (int) ($row['aggregate'] ?? 0) > 0;
    }
}
