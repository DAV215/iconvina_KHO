<?php

declare(strict_types=1);

namespace App\Modules\Bom\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class BomRepository extends Repository
{
    public function search(?int $componentId = null, ?string $version = null, int $page = 1, int $perPage = 25): array
    {
        $sql = 'SELECT bh.*, c.code AS component_code, c.name AS component_name, c.component_type
                FROM bom_headers bh
                INNER JOIN components c ON c.id = bh.component_id
                WHERE 1 = 1';
        $countSql = 'SELECT COUNT(*) AS aggregate
                     FROM bom_headers bh
                     INNER JOIN components c ON c.id = bh.component_id
                     WHERE 1 = 1';
        $params = [];
        $offset = max(0, ($page - 1) * $perPage);

        if ($componentId !== null && $componentId > 0) {
            $sql .= ' AND bh.component_id = :component_id';
            $countSql .= ' AND bh.component_id = :component_id';
            $params['component_id'] = $componentId;
        }

        if ($version !== null && trim($version) !== '') {
            $sql .= ' AND bh.version LIKE :version';
            $countSql .= ' AND bh.version LIKE :version';
            $params['version'] = '%' . trim($version) . '%';
        }

        $sql .= sprintf(' ORDER BY c.name ASC, bh.id DESC LIMIT %d OFFSET %d', $perPage, $offset);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT bh.*, c.code AS component_code, c.name AS component_name, c.component_type, c.image_path AS component_image_path
             FROM bom_headers bh
             INNER JOIN components c ON c.id = bh.component_id
             WHERE bh.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findItemsByBomId(int $bomId): array
    {
        return $this->fetchAll(
            'SELECT bi.*,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit,
                    c.code AS child_component_code,
                    c.name AS child_component_name,
                    c.component_type AS child_component_type,
                    c.image_path AS child_component_image_path
             FROM bom_items bi
             LEFT JOIN materials m ON m.id = bi.material_id
             LEFT JOIN components c ON c.id = bi.component_id
             WHERE bi.bom_id = :bom_id
             ORDER BY bi.id ASC',
            ['bom_id' => $bomId]
        );
    }

    public function findActiveByComponentId(int $componentId): ?array
    {
        return $this->fetchOne(
            'SELECT bh.*, c.code AS component_code, c.name AS component_name, c.component_type, c.image_path AS component_image_path
             FROM bom_headers bh
             INNER JOIN components c ON c.id = bh.component_id
             WHERE bh.component_id = :component_id
               AND bh.is_active = 1
             ORDER BY bh.id DESC
             LIMIT 1',
            ['component_id' => $componentId]
        );
    }

    public function create(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $bomId = $this->insert('bom_headers', $header);
            $this->insertItems($bomId, $items);
            $pdo->commit();

            return $bomId;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function update(int $id, array $header, array $items): void
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $this->updateById('bom_headers', $id, $header);
            $this->execute('DELETE FROM bom_items WHERE bom_id = :bom_id', ['bom_id' => $id]);
            $this->insertItems($id, $items);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $this->execute('DELETE FROM bom_items WHERE bom_id = :bom_id', ['bom_id' => $id]);
            $this->deleteById('bom_headers', $id);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function insertItems(int $bomId, array $items): void
    {
        foreach ($items as $item) {
            $this->insert('bom_items', [
                'bom_id' => $bomId,
                'item_kind' => $item['item_kind'],
                'material_id' => $item['material_id'],
                'component_id' => $item['component_id'],
                'quantity' => $item['quantity'],
                'note' => $item['note'],
            ]);
        }
    }
}
