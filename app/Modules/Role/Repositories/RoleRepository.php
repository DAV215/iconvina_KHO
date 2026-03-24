<?php

declare(strict_types=1);

namespace App\Modules\Role\Repositories;

use App\Core\Database\Repository;

final class RoleRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'r.code',
            'name' => 'r.name',
            'updated_at' => 'r.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'r.updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(r.code LIKE :search OR r.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'active') {
            $conditions[] = 'r.is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'r.is_active = 0';
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT r.*, COUNT(DISTINCT rp.permission_id) AS permission_count, COUNT(DISTINCT u.id) AS user_count
             FROM roles r
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             LEFT JOIN users u ON u.role_id = r.id AND u.deleted_at IS NULL AND u.status <> :deleted_status%s
             GROUP BY r.id
             ORDER BY %s %s, r.id DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $params['deleted_status'] = 'deleted';
        $countSql = 'SELECT COUNT(*) AS aggregate FROM roles r' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) ($this->fetchOne($countSql, array_diff_key($params, ['deleted_status' => true]))['aggregate'] ?? 0),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM roles WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM roles WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('roles', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('roles', $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById('roles', $id);
    }

    public function options(): array
    {
        return $this->fetchAll('SELECT id, code, name FROM roles WHERE is_active = 1 ORDER BY name ASC, id ASC');
    }

    public function userCount(int $roleId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM users
             WHERE role_id = :role_id
               AND deleted_at IS NULL
               AND status <> :deleted_status',
            [
                'role_id' => $roleId,
                'deleted_status' => 'deleted',
            ]
        );

        return (int) ($row['aggregate'] ?? 0);
    }
}
