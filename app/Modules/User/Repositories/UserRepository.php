<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories;

use App\Core\Database\Repository;

final class UserRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'u.code',
            'username' => 'u.username',
            'full_name' => 'u.full_name',
            'status' => 'u.status',
            'joined_at' => 'u.joined_at',
            'last_login_at' => 'u.last_login_at',
            'updated_at' => 'u.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'u.updated_at';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(u.code LIKE :search OR u.username LIKE :search OR u.full_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'deleted') {
            $conditions[] = 'u.deleted_at IS NOT NULL';
        } else {
            $conditions[] = 'u.deleted_at IS NULL';
            if ($status !== '') {
                $conditions[] = 'u.status = :status';
                $params['status'] = $status;
            }
        }

        $roleId = (int) ($filters['role_id'] ?? 0);
        if ($roleId > 0) {
            $conditions[] = 'u.role_id = :role_id';
            $params['role_id'] = $roleId;
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT u.*, r.name AS role_name, m.full_name AS manager_name, p.name AS position_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             LEFT JOIN users m ON m.id = u.manager_id
             LEFT JOIN positions p ON p.id = u.position_id%s
             ORDER BY %s %s, u.id DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $orderBy,
            $direction,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM users u' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT u.*, r.name AS role_name, m.full_name AS manager_name, p.name AS position_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             LEFT JOIN users m ON m.id = u.manager_id
             LEFT JOIN positions p ON p.id = u.position_id
             WHERE u.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE username = :username LIMIT 1', ['username' => $username]);
    }

    public function create(array $data): int
    {
        return $this->insert('users', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('users', $id, $data);
    }

    public function roleOptions(): array
    {
        if (!$this->tableExists('roles')) {
            return [];
        }

        return $this->fetchAll(
            'SELECT id, code, name
             FROM roles
             WHERE is_active = 1
             ORDER BY name ASC, id ASC'
        );
    }

    public function managerOptions(?int $excludeId = null): array
    {
        $params = [
            'deleted_status' => 'deleted',
        ];
        $sql = 'SELECT id, code, full_name
                FROM users
                WHERE deleted_at IS NULL
                  AND status <> :deleted_status';

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' ORDER BY full_name ASC, id ASC LIMIT 500';

        return $this->fetchAll($sql, $params);
    }

    public function companyOptions(): array
    {
        return $this->referenceOptions('companies');
    }

    public function branchOptions(): array
    {
        return $this->referenceOptions('branches');
    }

    public function departmentOptions(): array
    {
        return $this->referenceOptions('departments');
    }

    public function positionOptions(): array
    {
        return $this->referenceOptions('positions');
    }

    public function roleExists(int $id): bool
    {
        return $this->fetchOne('SELECT id FROM roles WHERE id = :id LIMIT 1', ['id' => $id]) !== null;
    }

    public function activeUserExists(int $id): bool
    {
        return $this->fetchOne(
            'SELECT id
             FROM users
             WHERE id = :id
               AND deleted_at IS NULL
               AND status <> :deleted_status
             LIMIT 1',
            [
                'id' => $id,
                'deleted_status' => 'deleted',
            ]
        ) !== null;
    }

    private function referenceOptions(string $table): array
    {
        if (!$this->tableExists($table)) {
            return [];
        }

        return $this->fetchAll(
            sprintf(
                'SELECT id, code, name
                 FROM %s
                 ORDER BY name ASC, id ASC
                 LIMIT 500',
                $table
            )
        );
    }

    private function tableExists(string $table): bool
    {
        return $this->fetchOne(
            'SELECT 1
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
             LIMIT 1',
            ['table_name' => $table]
        ) !== null;
    }
}
