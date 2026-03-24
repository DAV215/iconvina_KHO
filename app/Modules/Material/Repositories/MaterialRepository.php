<?php

declare(strict_types=1);

namespace App\Modules\Material\Repositories;

use App\Core\Database\Repository;

final class MaterialRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'm.code',
            'name' => 'm.name',
            'category' => 'mc.name',
            'unit' => 'm.unit',
            'standard_cost' => 'm.standard_cost',
            'min_stock' => 'm.min_stock',
            'updated_at' => 'm.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'm.id';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $sql = 'SELECT m.*, mc.code AS category_code, mc.name AS category_name
                FROM materials m
                LEFT JOIN material_categories mc ON mc.id = m.category_id';
        $countSql = 'SELECT COUNT(*) AS aggregate
                     FROM materials m
                     LEFT JOIN material_categories mc ON mc.id = m.category_id';
        $params = [];
        $conditions = [];
        $offset = max(0, ($page - 1) * $perPage);

        $code = trim((string) ($filters['code'] ?? ''));
        if ($code !== '') {
            $conditions[] = 'm.code LIKE :code';
            $params['code'] = '%' . $code . '%';
        }

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $conditions[] = 'm.name LIKE :name';
            $params['name'] = '%' . $name . '%';
        }

        $categoryId = trim((string) ($filters['category_id'] ?? ''));
        if ($categoryId !== '' && ctype_digit($categoryId)) {
            $conditions[] = 'm.category_id = :category_id';
            $params['category_id'] = (int) $categoryId;
        }

        $color = trim((string) ($filters['color'] ?? ''));
        if ($color !== '') {
            $conditions[] = 'm.color LIKE :color';
            $params['color'] = '%' . $color . '%';
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === '1' || $status === '0') {
            $conditions[] = 'm.is_active = :is_active';
            $params['is_active'] = (int) $status;
        }

        if ($conditions !== []) {
            $whereSql = ' WHERE ' . implode(' AND ', $conditions);
            $sql .= $whereSql;
            $countSql .= $whereSql;
        }

        $sql .= sprintf(' ORDER BY %s %s, m.id DESC LIMIT %d OFFSET %d', $orderBy, $direction, $perPage, $offset);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
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
            'SELECT id, code, name, parent_id
             FROM material_categories
             WHERE is_active = 1
             ORDER BY COALESCE(parent_id, id) ASC, parent_id ASC, name ASC, id ASC'
        );
    }

    public function allCategoryOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, parent_id, is_active
             FROM material_categories
             ORDER BY COALESCE(parent_id, id) ASC, parent_id ASC, name ASC, id ASC'
        );
    }

    public function create(array $data): int
    {
        return $this->insert('materials', $data);
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, unit, standard_cost, is_active
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
