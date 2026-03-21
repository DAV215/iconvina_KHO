<?php

declare(strict_types=1);

namespace App\Modules\Order\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class OrderRepository extends Repository
{
    public function search(?string $search = null, ?string $status = null): array
    {
        $sql = 'SELECT so.*, c.code AS customer_code, c.name AS customer_name, q.code AS quotation_code
                FROM sales_orders so
                INNER JOIN customers c ON c.id = so.customer_id
                LEFT JOIN quotations q ON q.id = so.quotation_id
                WHERE 1 = 1';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $params['search'] = '%' . trim($search) . '%';
            $sql .= ' AND (so.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
        }

        if ($status !== null && trim($status) !== '') {
            $params['status'] = trim($status);
            $sql .= ' AND so.status = :status';
        }

        $sql .= ' ORDER BY so.id DESC LIMIT 100';

        return $this->fetchAll($sql, $params);
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

    public function findItemsByOrderId(int $salesOrderId): array
    {
        return $this->fetchAll(
            'SELECT *
             FROM sales_order_items
             WHERE sales_order_id = :sales_order_id
             ORDER BY id ASC',
            ['sales_order_id' => $salesOrderId]
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

    private function insertItems(int $salesOrderId, array $items): void
    {
        foreach ($items as $item) {
            $this->insert('sales_order_items', [
                'sales_order_id' => $salesOrderId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_amount' => $item['total_amount'],
            ]);
        }
    }
}