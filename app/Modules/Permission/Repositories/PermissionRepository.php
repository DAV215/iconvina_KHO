<?php

declare(strict_types=1);

namespace App\Modules\Permission\Repositories;

use App\Core\Database\Repository;

final class PermissionRepository extends Repository
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM permissions ORDER BY module ASC, action ASC, id ASC');
    }

    public function findByModuleAction(string $module, string $action): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM permissions WHERE module = :module AND action = :action LIMIT 1',
            ['module' => $module, 'action' => $action]
        );
    }

    public function create(array $data): int
    {
        return $this->insert('permissions', $data);
    }

    public function rolePermissionIds(int $roleId): array
    {
        $rows = $this->fetchAll('SELECT permission_id FROM role_permissions WHERE role_id = :role_id', ['role_id' => $roleId]);

        return array_map(static fn (array $row): int => (int) $row['permission_id'], $rows);
    }

    public function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        $this->execute('DELETE FROM role_permissions WHERE role_id = :role_id', ['role_id' => $roleId]);

        foreach ($permissionIds as $permissionId) {
            $this->execute(
                'INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)',
                ['role_id' => $roleId, 'permission_id' => (int) $permissionId]
            );
        }
    }

    public function permissionsForRole(int $roleId): array
    {
        return $this->fetchAll(
            'SELECT p.module, p.action
             FROM role_permissions rp
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id',
            ['role_id' => $roleId]
        );
    }

    public function deleteByRole(int $roleId): void
    {
        $this->execute('DELETE FROM role_permissions WHERE role_id = :role_id', ['role_id' => $roleId]);
    }
}
