<?php

declare(strict_types=1);

namespace App\Modules\Component\Repositories;

use App\Core\Database\Repository;

final class ComponentRepository extends Repository
{
    public function search(?string $search = null): array
    {
        if ($search === null || trim($search) === '') {
            return $this->fetchAll('SELECT * FROM components ORDER BY id DESC LIMIT 100');
        }

        return $this->fetchAll(
            'SELECT * FROM components
             WHERE code LIKE :search
                OR name LIKE :search
                OR component_type LIKE :search
             ORDER BY id DESC
             LIMIT 100',
            ['search' => '%' . trim($search) . '%']
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM components WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM components WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('components', $data);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, component_type, is_active
             FROM components
             ORDER BY name ASC, id ASC
             LIMIT 500'
        );
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('components', $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById('components', $id);
    }
}
