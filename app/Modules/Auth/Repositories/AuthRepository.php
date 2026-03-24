<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Core\Database\Repository;

final class AuthRepository extends Repository
{
    public function findActiveByUsername(string $username): ?array
    {
        return $this->fetchOne(
            'SELECT u.*, r.code AS role_code, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.username = :username
               AND u.is_active = 1
               AND u.deleted_at IS NULL
               AND u.status <> :deleted_status
             LIMIT 1',
            [
                'username' => $username,
                'deleted_status' => 'deleted',
            ]
        );
    }

    public function findAuthUserById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT u.*, r.code AS role_code, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id
               AND u.is_active = 1
               AND u.deleted_at IS NULL
               AND u.status <> :deleted_status
             LIMIT 1',
            [
                'id' => $id,
                'deleted_status' => 'deleted',
            ]
        );
    }
}
