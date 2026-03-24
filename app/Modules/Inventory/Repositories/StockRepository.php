<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class StockRepository extends Repository
{
    public function search(?string $search = null, ?string $txnType = null, int $page = 1, int $perPage = 25): array
    {
        $sql = 'SELECT st.*,
                       COUNT(sti.id) AS item_count,
                       COALESCE(SUM(sti.line_total), 0) AS total_amount
                FROM stock_transactions st
                LEFT JOIN stock_transaction_items sti ON sti.stock_transaction_id = st.id
                WHERE 1 = 1';
        $countSql = 'SELECT COUNT(*) AS aggregate
                     FROM stock_transactions st
                     WHERE 1 = 1';
        $params = [];
        $offset = max(0, ($page - 1) * $perPage);

        if ($search !== null && trim($search) !== '') {
            $params['search'] = '%' . trim($search) . '%';
            $sql .= ' AND (st.txn_no LIKE :search OR st.txn_date LIKE :search)';
            $countSql .= ' AND (st.txn_no LIKE :search OR st.txn_date LIKE :search)';
        }

        if ($txnType !== null && trim($txnType) !== '') {
            $params['txn_type'] = trim($txnType);
            $sql .= ' AND st.txn_type = :txn_type';
            $countSql .= ' AND st.txn_type = :txn_type';
        }

        $sql .= sprintf(' GROUP BY st.id ORDER BY st.id DESC LIMIT %d OFFSET %d', $perPage, $offset);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT st.*, COUNT(sti.id) AS item_count, COALESCE(SUM(sti.line_total), 0) AS total_amount
             FROM stock_transactions st
             LEFT JOIN stock_transaction_items sti ON sti.stock_transaction_id = st.id
             WHERE st.id = :id
             GROUP BY st.id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByTxnNo(string $txnNo): ?array
    {
        return $this->fetchOne('SELECT * FROM stock_transactions WHERE txn_no = :txn_no LIMIT 1', ['txn_no' => $txnNo]);
    }

    public function findItemsByTransactionId(int $transactionId): array
    {
        return $this->fetchAll(
            'SELECT sti.*, m.code AS material_code, m.name AS material_name, m.unit AS material_unit,
                    c.code AS component_code, c.name AS component_name
             FROM stock_transaction_items sti
             LEFT JOIN materials m ON m.id = sti.material_id
             LEFT JOIN components c ON c.id = sti.component_id
             WHERE sti.stock_transaction_id = :stock_transaction_id
             ORDER BY sti.id ASC',
            ['stock_transaction_id' => $transactionId]
        );
    }

    public function materialOptions(): array
    {
        return $this->fetchAll(
            'SELECT m.id, m.code, m.name, m.unit, m.standard_cost, m.category_id,
                    mc.name AS category_name, mc.code AS category_code
             FROM materials m
             LEFT JOIN material_categories mc ON mc.id = m.category_id
             WHERE m.is_active = 1
             ORDER BY m.name ASC, m.id ASC
             LIMIT 500'
        );
    }

    public function componentOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, component_type, standard_cost
             FROM components
             WHERE is_active = 1
             ORDER BY name ASC, id ASC
             LIMIT 500'
        );
    }

    public function materialCategoryOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, code, name, parent_id
             FROM material_categories
             WHERE is_active = 1
             ORDER BY COALESCE(parent_id, id) ASC, parent_id ASC, name ASC, id ASC'
        );
    }

    public function findMaterialById(int $id): ?array
    {
        return $this->fetchOne('SELECT id, code, name, unit, standard_cost, is_active FROM materials WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findComponentById(int $id): ?array
    {
        return $this->fetchOne('SELECT id, code, name, component_type, standard_cost, is_active FROM components WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function create(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $transactionId = $this->insert('stock_transactions', $header);
            $this->insertItems($transactionId, $items);
            $pdo->commit();

            return $transactionId;
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
            $this->updateById('stock_transactions', $id, $header);
            $this->execute('DELETE FROM stock_transaction_items WHERE stock_transaction_id = :stock_transaction_id', ['stock_transaction_id' => $id]);
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
            $this->execute('DELETE FROM stock_transaction_items WHERE stock_transaction_id = :stock_transaction_id', ['stock_transaction_id' => $id]);
            $this->deleteById('stock_transactions', $id);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function insertItems(int $transactionId, array $items): void
    {
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
    }
}
