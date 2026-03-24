<?php

declare(strict_types=1);

namespace App\Modules\Payment\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class PaymentRepository extends Repository
{
    public function create(array $data): int
    {
        return $this->insert('payments', $data);
    }

    public function update(int $id, array $data): void
    {
        $this->updateById('payments', $id, $data);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT p.*
             FROM payments p
             WHERE p.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function listBySalesOrderId(int $salesOrderId): array
    {
        return $this->fetchAll(
            'SELECT p.*, u.full_name AS confirmed_by_name, u.username AS confirmed_by_username
             FROM payments p
             LEFT JOIN users u ON u.id = p.confirmed_by
             WHERE p.sales_order_id = :sales_order_id
             ORDER BY p.payment_date DESC, p.id DESC',
            ['sales_order_id' => $salesOrderId]
        );
    }

    public function listByPurchaseOrderId(int $purchaseOrderId): array
    {
        return $this->fetchAll(
            'SELECT p.*, u.full_name AS confirmed_by_name, u.username AS confirmed_by_username
             FROM payments p
             LEFT JOIN users u ON u.id = p.confirmed_by
             WHERE p.purchase_order_id = :purchase_order_id
             ORDER BY p.payment_date DESC, p.id DESC',
            ['purchase_order_id' => $purchaseOrderId]
        );
    }

    public function confirmedTotalBySalesOrderId(int $salesOrderId): float
    {
        $row = $this->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) AS aggregate
             FROM payments
             WHERE sales_order_id = :sales_order_id
               AND status = "confirmed"',
            ['sales_order_id' => $salesOrderId]
        );

        return (float) ($row['aggregate'] ?? 0);
    }

    public function confirmedTotalByPurchaseOrderId(int $purchaseOrderId): float
    {
        $row = $this->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) AS aggregate
             FROM payments
             WHERE purchase_order_id = :purchase_order_id
               AND status = "confirmed"',
            ['purchase_order_id' => $purchaseOrderId]
        );

        return (float) ($row['aggregate'] ?? 0);
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
}
