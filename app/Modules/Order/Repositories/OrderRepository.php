<?php

declare(strict_types=1);

namespace App\Modules\Order\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class OrderRepository extends Repository
{
    public function search(?string $search = null, ?string $status = null, int $page = 1, int $perPage = 25): array
    {
        $sql = 'SELECT so.*, c.code AS customer_code, c.name AS customer_name, q.code AS quotation_code
                FROM sales_orders so
                INNER JOIN customers c ON c.id = so.customer_id
                LEFT JOIN quotations q ON q.id = so.quotation_id
                WHERE 1 = 1';
        $countSql = 'SELECT COUNT(*) AS aggregate
                     FROM sales_orders so
                     INNER JOIN customers c ON c.id = so.customer_id
                     LEFT JOIN quotations q ON q.id = so.quotation_id
                     WHERE 1 = 1';
        $params = [];
        $offset = max(0, ($page - 1) * $perPage);

        if ($search !== null && trim($search) !== '') {
            $params['search'] = '%' . trim($search) . '%';
            $sql .= ' AND (so.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
            $countSql .= ' AND (so.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
        }

        if ($status !== null && trim($status) !== '') {
            $params['status'] = trim($status);
            $sql .= ' AND so.status = :status';
            $countSql .= ' AND so.status = :status';
        }

        $sql .= sprintf(' ORDER BY so.id DESC LIMIT %d OFFSET %d', $perPage, $offset);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT so.*, c.code AS customer_code, c.name AS customer_name, c.contact_name AS customer_contact_name,
                    c.phone AS customer_phone, c.email AS customer_email, c.tax_code AS customer_tax_code,
                    c.address AS customer_address, q.code AS quotation_code
             FROM sales_orders so
             INNER JOIN customers c ON c.id = so.customer_id
             LEFT JOIN quotations q ON q.id = so.quotation_id
             WHERE so.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM sales_orders WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function latestOrderCodeLike(string $prefix): ?array
    {
        return $this->fetchOne(
            'SELECT code
             FROM sales_orders
             WHERE code LIKE :prefix
             ORDER BY id DESC
             LIMIT 1',
            ['prefix' => $prefix . '%']
        );
    }

    public function findItemsByOrderId(int $salesOrderId): array
    {
        return $this->fetchAll(
            'SELECT soi.*,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit,
                    bh.id AS active_bom_id,
                    bh.version AS active_bom_version
             FROM sales_order_items soi
             LEFT JOIN components c ON c.id = soi.component_id
             LEFT JOIN materials m ON m.id = soi.material_id
             LEFT JOIN bom_headers bh ON bh.component_id = soi.component_id AND bh.is_active = 1
             WHERE soi.sales_order_id = :sales_order_id
             ORDER BY soi.line_no ASC, soi.id ASC',
            ['sales_order_id' => $salesOrderId]
        );
    }

    public function findItemById(int $salesOrderId, int $itemId): ?array
    {
        return $this->fetchOne(
            'SELECT soi.*,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit,
                    bh.id AS active_bom_id,
                    bh.version AS active_bom_version
             FROM sales_order_items soi
             LEFT JOIN components c ON c.id = soi.component_id
             LEFT JOIN materials m ON m.id = soi.material_id
             LEFT JOIN bom_headers bh ON bh.component_id = soi.component_id AND bh.is_active = 1
             WHERE soi.sales_order_id = :sales_order_id
               AND soi.id = :id
             LIMIT 1',
            [
                'sales_order_id' => $salesOrderId,
                'id' => $itemId,
            ]
        );
    }

    public function create(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $salesOrderId = $this->insert('sales_orders', $header);
            $this->insertItems($salesOrderId, $items);
            $pdo->commit();

            return $salesOrderId;
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
            $this->updateById('sales_orders', $id, $header);
            $this->execute('DELETE FROM sales_order_items WHERE sales_order_id = :sales_order_id', ['sales_order_id' => $id]);
            $this->insertItems($id, $items);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->updateById('sales_orders', $id, [
            'status' => $status,
        ]);
    }

    public function updatePaymentSummary(int $id, array $data): void
    {
        $this->updateById('sales_orders', $id, $data);
    }

    public function componentStockMap(array $componentIds): array
    {
        $componentIds = array_values(array_unique(array_filter(array_map(static fn (mixed $value): int => (int) $value, $componentIds), static fn (int $value): bool => $value > 0)));
        if ($componentIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($componentIds as $index => $componentId) {
            $key = 'component_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $componentId;
        }

        $rows = $this->fetchAll(
            'SELECT sti.component_id,
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
            $map[(int) $row['component_id']] = (float) ($row['current_qty'] ?? 0);
        }

        return $map;
    }

    public function materialStockMap(array $materialIds): array
    {
        $materialIds = array_values(array_unique(array_filter(array_map(static fn (mixed $value): int => (int) $value, $materialIds), static fn (int $value): bool => $value > 0)));
        if ($materialIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($materialIds as $index => $materialId) {
            $key = 'material_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $materialId;
        }

        $rows = $this->fetchAll(
            'SELECT sti.material_id,
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
            $map[(int) $row['material_id']] = (float) ($row['current_qty'] ?? 0);
        }

        return $map;
    }

    public function deliveryItemTotalsByOrderId(int $salesOrderId): array
    {
        $rows = $this->fetchAll(
            'SELECT sdi.sales_order_item_id,
                    SUM(sdi.delivery_qty) AS delivered_qty
             FROM sales_delivery_items sdi
             INNER JOIN sales_deliveries sd ON sd.id = sdi.sales_delivery_id
             WHERE sd.sales_order_id = :sales_order_id
               AND sd.status = "confirmed"
             GROUP BY sdi.sales_order_item_id',
            ['sales_order_id' => $salesOrderId]
        );

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['sales_order_item_id']] = (float) ($row['delivered_qty'] ?? 0);
        }

        return $map;
    }

    public function findDeliveriesByOrderId(int $salesOrderId): array
    {
        return $this->fetchAll(
            'SELECT sd.*,
                    st.txn_no AS stock_txn_no,
                    creator.username AS created_by_username,
                    creator.full_name AS created_by_full_name,
                    confirmer.username AS confirmed_by_username,
                    confirmer.full_name AS confirmed_by_full_name,
                    (
                        SELECT COUNT(*)
                        FROM sales_delivery_items sdi
                        WHERE sdi.sales_delivery_id = sd.id
                    ) AS item_count,
                    (
                        SELECT COALESCE(SUM(sdi.delivery_qty), 0)
                        FROM sales_delivery_items sdi
                        WHERE sdi.sales_delivery_id = sd.id
                    ) AS total_delivery_qty
             FROM sales_deliveries sd
             LEFT JOIN stock_transactions st ON st.id = sd.stock_transaction_id
             LEFT JOIN users creator ON creator.id = sd.created_by
             LEFT JOIN users confirmer ON confirmer.id = sd.confirmed_by
             WHERE sd.sales_order_id = :sales_order_id
             ORDER BY sd.id DESC',
            ['sales_order_id' => $salesOrderId]
        );
    }

    public function findDeliveryById(int $salesOrderId, int $deliveryId): ?array
    {
        return $this->fetchOne(
            'SELECT sd.*,
                    st.txn_no AS stock_txn_no
             FROM sales_deliveries sd
             LEFT JOIN stock_transactions st ON st.id = sd.stock_transaction_id
             WHERE sd.sales_order_id = :sales_order_id
               AND sd.id = :id
             LIMIT 1',
            [
                'sales_order_id' => $salesOrderId,
                'id' => $deliveryId,
            ]
        );
    }

    public function findDeliveryItemsByDeliveryId(int $deliveryId): array
    {
        return $this->fetchAll(
            'SELECT sdi.*,
                    soi.line_no,
                    soi.description,
                    soi.unit,
                    c.code AS component_code,
                    c.name AS component_name,
                    m.code AS material_code,
                    m.name AS material_name
             FROM sales_delivery_items sdi
             INNER JOIN sales_order_items soi ON soi.id = sdi.sales_order_item_id
             LEFT JOIN components c ON c.id = sdi.component_id
             LEFT JOIN materials m ON m.id = sdi.material_id
             WHERE sdi.sales_delivery_id = :sales_delivery_id
             ORDER BY soi.line_no ASC, sdi.id ASC',
            ['sales_delivery_id' => $deliveryId]
        );
    }

    public function latestDeliveryCodeLike(string $prefix): ?array
    {
        return $this->fetchOne(
            'SELECT code
             FROM sales_deliveries
             WHERE code LIKE :prefix
             ORDER BY id DESC
             LIMIT 1',
            ['prefix' => $prefix . '%']
        );
    }

    public function findDeliveryByCode(string $code): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM sales_deliveries
             WHERE code = :code
             LIMIT 1',
            ['code' => $code]
        );
    }

    public function createDelivery(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $startedTransaction = !$pdo->inTransaction();

        if ($startedTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $deliveryId = $this->insert('sales_deliveries', $header);
            foreach ($items as $item) {
                $this->insert('sales_delivery_items', [
                    'sales_delivery_id' => $deliveryId,
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'item_kind' => $item['item_kind'],
                    'component_id' => $item['component_id'],
                    'material_id' => $item['material_id'],
                    'ordered_qty' => $item['ordered_qty'],
                    'ready_qty' => $item['ready_qty'],
                    'delivery_qty' => $item['delivery_qty'],
                    'remaining_qty' => $item['remaining_qty'],
                ]);
            }

            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $deliveryId;
        } catch (Throwable $exception) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateDelivery(int $deliveryId, array $data): void
    {
        $this->updateById('sales_deliveries', $deliveryId, $data);
    }

    public function createStockIssue(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $startedTransaction = !$pdo->inTransaction();

        if ($startedTransaction) {
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

            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $transactionId;
        } catch (Throwable $exception) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function createLog(array $data): int
    {
        return $this->insert('sales_order_logs', $data);
    }

    public function logsByOrderId(int $salesOrderId): array
    {
        return $this->fetchAll(
            'SELECT sol.*,
                    u.username,
                    u.full_name
             FROM sales_order_logs sol
             LEFT JOIN users u ON u.id = sol.acted_by
             WHERE sol.entity_id = :entity_id
             ORDER BY sol.acted_at DESC, sol.id DESC',
            ['entity_id' => $salesOrderId]
        );
    }

    public function updateItemEngineering(int $itemId, array $data): void
    {
        $this->updateById('sales_order_items', $itemId, $data);
    }

    public function updateLinkedQuotationItemEngineering(int $quotationItemId, array $data): void
    {
        $this->updateById('quotation_items', $quotationItemId, $data);
    }

    public function delete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $this->execute('DELETE FROM sales_order_items WHERE sales_order_id = :sales_order_id', ['sales_order_id' => $id]);
            $this->deleteById('sales_orders', $id);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
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

    private function insertItems(int $salesOrderId, array $items): void
    {
        $lineNo = 1;
        foreach ($items as $item) {
            $this->insert('sales_order_items', [
                'sales_order_id' => $salesOrderId,
                'quotation_item_id' => $item['quotation_item_id'],
                'line_no' => $lineNo++,
                'item_mode' => $item['item_mode'],
                'item_type' => $item['item_type'],
                'component_id' => $item['component_id'],
                'material_id' => $item['material_id'],
                'temp_code' => $item['temp_code'],
                'spec_summary' => $item['spec_summary'],
                'description' => $item['description'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'total_amount' => $item['total_amount'],
                'fulfillment_status' => $item['fulfillment_status'] ?? 'pending',
            ]);
        }
    }
}
