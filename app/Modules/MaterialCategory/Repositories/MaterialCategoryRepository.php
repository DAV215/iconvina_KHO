<?php

declare(strict_types=1);

namespace App\Modules\MaterialCategory\Repositories;

use App\Core\Database\Repository;

final class MaterialCategoryRepository extends Repository
{
    public function search(?string $search = null): array
    {
        $sql = 'SELECT mc.*,
                       parent.code AS parent_code,
                       parent.name AS parent_name,
                       (
                           SELECT COUNT(*)
                           FROM materials m
                           WHERE m.category_id = mc.id
                       ) AS material_count,
                       (
                           SELECT COUNT(*)
                           FROM material_categories child
                           WHERE child.parent_id = mc.id
                       ) AS child_count
                FROM material_categories mc
                LEFT JOIN material_categories parent ON parent.id = mc.parent_id';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $sql .= ' WHERE mc.code LIKE :search
                       OR mc.name LIKE :search
                       OR parent.code LIKE :search
                       OR parent.name LIKE :search';
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= ' ORDER BY COALESCE(mc.parent_id, mc.id) ASC, mc.parent_id ASC, mc.name ASC, mc.id ASC';

        return $this->fetchAll($sql, $params);
    }

    public function all(): array
    {
        return $this->fetchAll(
            'SELECT mc.*,
                    parent.code AS parent_code,
                    parent.name AS parent_name,
                    (
                        SELECT COUNT(*)
                        FROM materials m
                        WHERE m.category_id = mc.id
                    ) AS material_count,
                    (
                        SELECT COUNT(*)
                        FROM material_categories child
                        WHERE child.parent_id = mc.id
                    ) AS child_count
             FROM material_categories mc
             LEFT JOIN material_categories parent ON parent.id = mc.parent_id
             ORDER BY COALESCE(mc.parent_id, mc.id) ASC, mc.parent_id ASC, mc.name ASC, mc.id ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT mc.*,
                    parent.code AS parent_code,
                    parent.name AS parent_name,
                    (
                        SELECT COUNT(*)
                        FROM materials m
                        WHERE m.category_id = mc.id
                    ) AS material_count,
                    (
                        SELECT COUNT(*)
                        FROM material_categories child
                        WHERE child.parent_id = mc.id
                    ) AS child_count
             FROM material_categories mc
             LEFT JOIN material_categories parent ON parent.id = mc.parent_id
             WHERE mc.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM material_categories
             WHERE code = :code
             LIMIT 1',
            ['code' => $code]
        );
    }

    public function countMaterialsByCategoryId(int $id): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM materials
             WHERE category_id = :id',
            ['id' => $id]
        );

        return (int) ($row['aggregate'] ?? 0);
    }

    public function countChildrenByParentId(int $id): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM material_categories
             WHERE parent_id = :id',
            ['id' => $id]
        );

        return (int) ($row['aggregate'] ?? 0);
    }

    public function create(array $data): int
    {
        return $this->insert('material_categories', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('material_categories', $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById('material_categories', $id);
    }
}
