<?php

declare(strict_types=1);

namespace App\Modules\Material\Repositories;

use App\Core\Database\Repository;

final class MaterialRepository extends Repository
{
    public function search(?string $search = null): array
    {
        $sql = 'SELECT m.*, mc.code AS category_code, mc.name AS category_name
                FROM materials m
                LEFT JOIN material_categories mc ON mc.id = m.category_id';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' WHERE m.code LIKE :search
                       OR m.name LIKE :search
                       OR m.unit LIKE :search
                       OR m.specification LIKE :search
                       OR m.color LIKE :search
                       OR mc.code LIKE :search
                       OR mc.name LIKE :search';
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= ' ORDER BY m.id DESC LIMIT 100';

        return $this->fetchAll($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT m.*, mc.code AS category_code, mc.name AS category_name
             FROM materials m
             LEFT JOIN material_categories mc ON mc.id = m.category_id
             WHERE m.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM materials WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function findCategoryById(int $id): ?array
    {
        return $this->fetchOne('SELECT id, code, name, is_active FROM material_categories WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function categoryOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name
             FROM material_categories
             WHERE is_active = 1
             ORDER BY name ASC, id ASC'
        );
    }

    public function create(array $data): int
    {
        return $this->insert('materials', $data);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, unit, is_active
             FROM materials
             ORDER BY name ASC, id ASC
             LIMIT 500'
        );
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('materials', $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById('materials', $id);
    }
}
