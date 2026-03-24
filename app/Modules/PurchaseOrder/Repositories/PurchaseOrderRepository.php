<?php

declare(strict_types=1);

namespace App\Modules\PurchaseOrder\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class PurchaseOrderRepository extends Repository
{
    public function search(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        $allowedOrderBy = [
            'code' => 'po.code',
            'supplier_name' => 'po.supplier_name',
            'order_date' => 'po.order_date',
            'expected_date' => 'po.expected_date',
            'status' => 'po.status',
            'total_amount' => 'po.total_amount',
            'updated_at' => 'po.updated_at',
        ];
        $orderBy = $allowedOrderBy[$sort['by'] ?? ''] ?? 'po.order_date';
        $direction = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $sql = 'SELECT po.*,
                       (
                           SELECT COUNT(*)
                           FROM stock_transactions st
                           WHERE st.ref_type = :stock_ref_type AND st.ref_id = po.id
                       ) AS stock_receipt_count
                FROM purchase_orders po';
        $countSql = 'SELECT COUNT(*) AS aggregate FROM purchase_orders po';
        $params['stock_ref_type'] = 'purchase_order';

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(po.code LIKE :search OR po.supplier_name LIKE :search OR po.status LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $conditions[] = 'po.status = :status';
            $params['status'] = $status;
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $conditions[] = 'po.order_date >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $conditions[] = 'po.order_date <= :date_to';
            $params['date_to'] = $dateTo;
        }

        if ($conditions !== []) {
            $whereSql = ' WHERE ' . implode(' AND ', $conditions);
            $sql .= $whereSql;
            $countSql .= $whereSql;
        }

        $sql .= sprintf(' ORDER BY %s %s, po.id DESC LIMIT %d OFFSET %d', $orderBy, $direction, $perPage, $offset);

        $countParams = $params;
        unset($countParams['stock_ref_type']);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $countParams)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT po.*,
                    (
                        SELECT COUNT(*)
                        FROM stock_transactions st
                        WHERE st.ref_type = :stock_ref_type AND st.ref_id = po.id
                    ) AS stock_receipt_count
             FROM purchase_orders po
             WHERE po.id = :id
             LIMIT 1',
            [
                'id' => $id,
                'stock_ref_type' => 'purchase_order',
            ]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM purchase_orders WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function dailyCodes(string $orderDate, ?int $ignoreId = null): array
    {
        $params = [
            'order_date' => $orderDate,
        ];
        $sql = 'SELECT code
                FROM purchase_orders
                WHERE order_date = :order_date';

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        $sql .= ' ORDER BY id ASC';

        return $this->fetchAll($sql, $params);
    }

    public function findItemsByPurchaseOrderId(int $purchaseOrderId): array
    {
        return $this->fetchAll(
            'SELECT poi.*, m.code AS material_code, m.name AS material_name
             FROM purchase_order_items poi
             LEFT JOIN materials m ON m.id = poi.material_id
             WHERE poi.purchase_order_id = :purchase_order_id
             ORDER BY poi.id ASC',
            ['purchase_order_id' => $purchaseOrderId]
        );
    }

    public function materialOptions(): array
    {
        return $this->fetchAll(
            'SELECT m.id, m.code, m.name, m.unit, m.standard_cost, m.is_active, m.category_id, m.color, m.specification, m.description,
                    mc.code AS category_code, mc.name AS category_name
             FROM materials m
             LEFT JOIN material_categories mc ON mc.id = m.category_id
             WHERE m.is_active = 1
             ORDER BY m.name ASC, m.id ASC
             LIMIT 500'
        );
    }

    public function findMaterialById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, code, name, unit, standard_cost, is_active
             FROM materials
             WHERE id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function receivingsByPurchaseOrderId(int $purchaseOrderId): array
    {
        return $this->fetchAll(
            'SELECT r.*, u.full_name AS acted_by_name, u.username AS acted_by_username
             FROM purchase_order_receivings r
             LEFT JOIN users u ON u.id = r.acted_by
             WHERE r.purchase_order_id = :purchase_order_id
             ORDER BY r.received_at DESC, r.id DESC',
            ['purchase_order_id' => $purchaseOrderId]
        );
    }

    public function extraCostsByPurchaseOrderId(int $purchaseOrderId): array
    {
        return $this->fetchAll(
            'SELECT *
             FROM purchase_order_extra_costs
             WHERE purchase_order_id = :purchase_order_id
             ORDER BY created_at DESC, id DESC',
            ['purchase_order_id' => $purchaseOrderId]
        );
    }

    public function logsByPurchaseOrderId(int $purchaseOrderId): array
    {
        return $this->fetchAll(
            'SELECT l.*, u.full_name AS acted_by_name, u.username AS acted_by_username
             FROM purchase_order_logs l
             LEFT JOIN users u ON u.id = l.acted_by
             WHERE l.entity_id = :entity_id
               AND l.module = :module
             ORDER BY l.acted_at DESC, l.id DESC',
            [
                'entity_id' => $purchaseOrderId,
                'module' => 'purchase_order',
            ]
        );
    }

    public function findStockTransactionByReference(string $refType, int $refId): ?array
    {
        return $this->fetchOne(
            'SELECT st.*,
                    COUNT(sti.id) AS item_count,
                    COALESCE(SUM(sti.line_total), 0) AS total_amount
             FROM stock_transactions st
             LEFT JOIN stock_transaction_items sti ON sti.stock_transaction_id = st.id
             WHERE st.ref_type = :ref_type AND st.ref_id = :ref_id
             GROUP BY st.id
             ORDER BY st.id DESC
             LIMIT 1',
            [
                'ref_type' => $refType,
                'ref_id' => $refId,
            ]
        );
    }

    public function hasLinkedStockTransactions(int $purchaseOrderId): bool
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS aggregate
             FROM stock_transactions
             WHERE ref_type = :ref_type AND ref_id = :ref_id',
            [
                'ref_type' => 'purchase_order',
                'ref_id' => $purchaseOrderId,
            ]
        );

        return (int) ($row['aggregate'] ?? 0) > 0;
    }

    public function create(array $header, array $items): int
    {
        return $this->transaction(function () use ($header, $items): int {
            $purchaseOrderId = $this->insert('purchase_orders', $header);
            $this->replaceItems($purchaseOrderId, $items);

            return $purchaseOrderId;
        });
    }

    public function update(int $id, array $header, array $items): void
    {
        $this->transaction(function () use ($id, $header, $items): void {
            $this->updateById('purchase_orders', $id, $header);
            $this->replaceItems($id, $items);
        });
    }

    public function updateStatus(int $id, string $status, string $updatedAt): void
    {
        $this->updateById('purchase_orders', $id, [
            'status' => $status,
            'updated_at' => $updatedAt,
        ]);
    }

    public function updatePaymentSummary(int $id, array $data): void
    {
        $this->updateById('purchase_orders', $id, $data);
    }

    public function createReceiving(array $data): int
    {
        return $this->insert('purchase_order_receivings', $data);
    }

    public function createExtraCost(array $data): int
    {
        return $this->insert('purchase_order_extra_costs', $data);
    }

    public function createLog(array $data): int
    {
        return $this->insert('purchase_order_logs', $data);
    }

    public function createStockTransactionInTransaction(array $header, array $items): int
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

    public function delete(int $id): void
    {
        $this->transaction(function () use ($id): void {
            $this->execute('DELETE FROM purchase_order_logs WHERE module = :module AND entity_id = :entity_id', [
                'module' => 'purchase_order',
                'entity_id' => $id,
            ]);
            $this->execute('DELETE FROM purchase_order_extra_costs WHERE purchase_order_id = :purchase_order_id', ['purchase_order_id' => $id]);
            $this->execute('DELETE FROM purchase_order_receivings WHERE purchase_order_id = :purchase_order_id', ['purchase_order_id' => $id]);
            $this->execute('DELETE FROM purchase_order_items WHERE purchase_order_id = :purchase_order_id', ['purchase_order_id' => $id]);
            $this->deleteById('purchase_orders', $id);
        });
    }

    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $startedTransaction = !$pdo->inTransaction();

        if ($startedTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback();

            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $result;
        } catch (Throwable $exception) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function replaceItems(int $purchaseOrderId, array $items): void
    {
        $this->execute('DELETE FROM purchase_order_items WHERE purchase_order_id = :purchase_order_id', ['purchase_order_id' => $purchaseOrderId]);

        foreach ($items as $item) {
            $this->insert('purchase_order_items', [
                'purchase_order_id' => $purchaseOrderId,
                'material_id' => $item['material_id'],
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'total_amount' => $item['total_amount'],
            ]);
        }
    }
}
