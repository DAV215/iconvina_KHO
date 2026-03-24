<?php

declare(strict_types=1);

namespace App\Modules\Production\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class ProductionRepository extends Repository
{
    public function search(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $params = [];
        $conditions = [];
        $offset = max(0, ($page - 1) * $perPage);

        $sql = 'SELECT po.*,
                       so.code AS sales_order_code,
                       c.code AS component_code,
                       c.name AS component_name,
                       c.unit AS component_unit,
                       (SELECT COUNT(*) FROM production_tasks pt WHERE pt.production_order_id = po.id) AS task_count,
                       (SELECT COUNT(*) FROM production_tasks pt WHERE pt.production_order_id = po.id AND pt.status = "done") AS task_done_count
                FROM production_orders po
                INNER JOIN components c ON c.id = po.component_id
                LEFT JOIN sales_orders so ON so.id = po.sales_order_id';
        $countSql = 'SELECT COUNT(*) AS aggregate
                     FROM production_orders po
                     INNER JOIN components c ON c.id = po.component_id
                     LEFT JOIN sales_orders so ON so.id = po.sales_order_id';

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(po.code LIKE :search OR po.title LIKE :search OR so.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $conditions[] = 'po.status = :status';
            $params['status'] = $status;
        }

        $mineUserId = (int) ($filters['assigned_to'] ?? 0);
        if ($mineUserId > 0) {
            $conditions[] = 'EXISTS (
                SELECT 1
                FROM production_tasks mine_task
                WHERE mine_task.production_order_id = po.id
                  AND mine_task.assigned_to = :assigned_to
            )';
            $params['assigned_to'] = $mineUserId;
        }

        if ($conditions !== []) {
            $whereSql = ' WHERE ' . implode(' AND ', $conditions);
            $sql .= $whereSql;
            $countSql .= $whereSql;
        }

        $sql .= sprintf(' ORDER BY po.id DESC LIMIT %d OFFSET %d', $perPage, $offset);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT po.*,
                    so.code AS sales_order_code,
                    so.status AS sales_order_status,
                    soi.line_no AS sales_order_line_no,
                    soi.description AS sales_order_item_description,
                    soi.quantity AS sales_order_item_qty,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit,
                    c.standard_cost AS component_standard_cost,
                    bh.version AS bom_version,
                    bh.is_active AS bom_is_active
             FROM production_orders po
             INNER JOIN components c ON c.id = po.component_id
             LEFT JOIN sales_orders so ON so.id = po.sales_order_id
             LEFT JOIN sales_order_items soi ON soi.id = po.sales_order_item_id
             LEFT JOIN bom_headers bh ON bh.id = po.bom_id
             WHERE po.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM production_orders WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function findActiveBySalesOrderItemId(int $salesOrderItemId): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM production_orders
             WHERE sales_order_item_id = :sales_order_item_id
               AND status NOT IN ("completed", "cancelled")
             ORDER BY id DESC
             LIMIT 1',
            ['sales_order_item_id' => $salesOrderItemId]
        );
    }

    public function findLatestBySalesOrderItemIds(array $salesOrderItemIds): array
    {
        if ($salesOrderItemIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($salesOrderItemIds) as $index => $itemId) {
            $key = 'item_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = (int) $itemId;
        }

        return $this->fetchAll(
            'SELECT latest.*
             FROM production_orders latest
             INNER JOIN (
                SELECT sales_order_item_id, MAX(id) AS latest_id
                FROM production_orders
                WHERE sales_order_item_id IN (' . implode(', ', $placeholders) . ')
                GROUP BY sales_order_item_id
             ) grouped ON grouped.latest_id = latest.id',
            $params
        );
    }

    public function findTasksByProductionOrderId(int $productionOrderId): array
    {
        return $this->fetchAll(
            'SELECT pt.*,
                    u.username AS assigned_username,
                    u.full_name AS assigned_full_name
             FROM production_tasks pt
             LEFT JOIN users u ON u.id = pt.assigned_to
             WHERE pt.production_order_id = :production_order_id
             ORDER BY pt.id ASC',
            ['production_order_id' => $productionOrderId]
        );
    }

    public function findBomItemsByBomId(int $bomId): array
    {
        return $this->fetchAll(
            'SELECT bi.*,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit,
                    m.standard_cost AS material_standard_cost,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit,
                    c.standard_cost AS component_standard_cost
             FROM bom_items bi
             LEFT JOIN materials m ON m.id = bi.material_id
             LEFT JOIN components c ON c.id = bi.component_id
             WHERE bi.bom_id = :bom_id
             ORDER BY bi.id ASC',
            ['bom_id' => $bomId]
        );
    }

    public function findTaskById(int $taskId): ?array
    {
        return $this->fetchOne(
            'SELECT pt.*, po.status AS production_status
             FROM production_tasks pt
             INNER JOIN production_orders po ON po.id = pt.production_order_id
             WHERE pt.id = :id
             LIMIT 1',
            ['id' => $taskId]
        );
    }

    public function create(array $header, array $tasks): int
    {
        $pdo = $this->pdo();
        $started = !$pdo->inTransaction();

        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $id = $this->insert('production_orders', $header);
            $this->insertTasks($id, $tasks);
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $id;
        } catch (Throwable $exception) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateOrder(int $id, array $data): void
    {
        $this->updateById('production_orders', $id, $data);
    }

    public function updateTask(int $id, array $data): void
    {
        $this->updateById('production_tasks', $id, $data);
    }

    public function userOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, code, username, full_name
             FROM users
             WHERE deleted_at IS NULL
               AND status IN ("draft", "active")
             ORDER BY COALESCE(full_name, username) ASC, id ASC
             LIMIT 300'
        );
    }

    public function materialStockMap(array $materialIds): array
    {
        if ($materialIds === []) {
            return [];
        }

        $params = [];
        $placeholders = [];
        foreach (array_values(array_unique(array_map('intval', $materialIds))) as $index => $materialId) {
            $key = 'material_' . $index;
            $params[$key] = $materialId;
            $placeholders[] = ':' . $key;
        }

        $rows = $this->fetchAll(
            'SELECT sti.material_id AS item_id,
                    SUM(
                        CASE
                            WHEN st.txn_type IN ("import", "receipt") THEN sti.quantity
                            WHEN st.txn_type IN ("export", "issue") THEN -sti.quantity
                            WHEN st.txn_type = "adjustment" THEN sti.quantity
                            ELSE 0
                        END
                    ) AS current_qty
             FROM stock_transaction_items sti
             INNER JOIN stock_transactions st ON st.id = sti.stock_transaction_id
             WHERE sti.item_kind = "material"
               AND sti.material_id IN (' . implode(', ', $placeholders) . ')
             GROUP BY sti.material_id',
            $params
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['item_id']] = (float) ($row['current_qty'] ?? 0);
        }

        return $map;
    }

    public function componentStockMap(array $componentIds): array
    {
        if ($componentIds === []) {
            return [];
        }

        $params = [];
        $placeholders = [];
        foreach (array_values(array_unique(array_map('intval', $componentIds))) as $index => $componentId) {
            $key = 'component_' . $index;
            $params[$key] = $componentId;
            $placeholders[] = ':' . $key;
        }

        $rows = $this->fetchAll(
            'SELECT sti.component_id AS item_id,
                    SUM(
                        CASE
                            WHEN st.txn_type IN ("import", "receipt") THEN sti.quantity
                            WHEN st.txn_type IN ("export", "issue") THEN -sti.quantity
                            WHEN st.txn_type = "adjustment" THEN sti.quantity
                            ELSE 0
                        END
                    ) AS current_qty
             FROM stock_transaction_items sti
             INNER JOIN stock_transactions st ON st.id = sti.stock_transaction_id
             WHERE sti.item_kind = "component"
               AND sti.component_id IN (' . implode(', ', $placeholders) . ')
             GROUP BY sti.component_id',
            $params
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['item_id']] = (float) ($row['current_qty'] ?? 0);
        }

        return $map;
    }

    public function findStockReceiptByProductionOrderId(int $productionOrderId): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM stock_transactions
             WHERE ref_type = :ref_type
               AND ref_id = :ref_id
             LIMIT 1',
            [
                'ref_type' => 'production_order',
                'ref_id' => $productionOrderId,
            ]
        );
    }

    public function findIssueTransactionsByProductionOrderId(int $productionOrderId): array
    {
        return $this->fetchAll(
            'SELECT st.*,
                    COUNT(sti.id) AS item_count,
                    COALESCE(SUM(sti.quantity), 0) AS total_quantity
             FROM stock_transactions st
             LEFT JOIN stock_transaction_items sti ON sti.stock_transaction_id = st.id
             WHERE st.ref_type = :ref_type
               AND st.ref_id = :ref_id
               AND st.txn_type = :txn_type
             GROUP BY st.id
             ORDER BY st.id DESC',
            [
                'ref_type' => 'production_order_issue',
                'ref_id' => $productionOrderId,
                'txn_type' => 'issue',
            ]
        );
    }

    public function findIssueTransactionItemsByProductionOrderId(int $productionOrderId): array
    {
        return $this->fetchAll(
            'SELECT sti.*,
                    st.id AS stock_transaction_id,
                    st.txn_no,
                    st.txn_date,
                    st.note AS transaction_note,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit
             FROM stock_transaction_items sti
             INNER JOIN stock_transactions st ON st.id = sti.stock_transaction_id
             LEFT JOIN materials m ON m.id = sti.material_id
             LEFT JOIN components c ON c.id = sti.component_id
             WHERE st.ref_type = :ref_type
               AND st.ref_id = :ref_id
               AND st.txn_type = :txn_type
             ORDER BY st.id ASC, sti.id ASC',
            [
                'ref_type' => 'production_order_issue',
                'ref_id' => $productionOrderId,
                'txn_type' => 'issue',
            ]
        );
    }

    public function createStockReceipt(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $started = !$pdo->inTransaction();

        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $transactionId = $this->insert('stock_transactions', $header);
            foreach ($items as $item) {
                $this->insert('stock_transaction_items', [
                    'stock_transaction_id' => $transactionId,
                    'item_kind' => $item['item_kind'],
                    'material_id' => $item['material_id'],
                    'component_id' => $item['component_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total'],
                ]);
            }
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $transactionId;
        } catch (Throwable $exception) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function createStockIssueInTransaction(array $header, array $items): int
    {
        $transactionId = $this->insert('stock_transactions', $header);
        foreach ($items as $item) {
            $this->insert('stock_transaction_items', [
                'stock_transaction_id' => $transactionId,
                'item_kind' => $item['item_kind'],
                'material_id' => $item['material_id'],
                'component_id' => $item['component_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'line_total' => $item['line_total'],
            ]);
        }

        return $transactionId;
    }

    public function logsByProductionOrderId(int $productionOrderId): array
    {
        return $this->fetchAll(
            'SELECT l.*, u.full_name AS acted_by_name, u.username AS acted_by_username
             FROM production_order_logs l
             LEFT JOIN users u ON u.id = l.acted_by
             WHERE l.production_order_id = :production_order_id
             ORDER BY l.acted_at DESC, l.id DESC',
            ['production_order_id' => $productionOrderId]
        );
    }

    public function createLog(array $data): int
    {
        return $this->insert('production_order_logs', $data);
    }

    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $started = !$pdo->inTransaction();

        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback();
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $result;
        } catch (Throwable $exception) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function insertTasks(int $productionOrderId, array $tasks): void
    {
        foreach ($tasks as $task) {
            $this->insert('production_tasks', [
                'production_order_id' => $productionOrderId,
                'name' => $task['name'],
                'assigned_to' => $task['assigned_to'],
                'status' => $task['status'],
                'planned_start_at' => $task['planned_start_at'],
                'planned_end_at' => $task['planned_end_at'],
                'actual_start_at' => $task['actual_start_at'],
                'actual_end_at' => $task['actual_end_at'],
                'weight_percent' => $task['weight_percent'],
                'progress_percent' => $task['progress_percent'],
                'note' => $task['note'],
            ]);
        }
    }
}
