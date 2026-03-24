<?php

declare(strict_types=1);

namespace App\Modules\ServiceOrder\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class ServiceOrderRepository extends Repository
{
    public function search(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $conditions = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(svo.code LIKE :search OR svo.title LIKE :search OR svo.service_name LIKE :search OR so.code LIKE :search OR c.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $conditions[] = 'svo.status = :status';
            $params['status'] = $status;
        }

        $assignedTo = (int) ($filters['assigned_to'] ?? 0);
        if ($assignedTo > 0) {
            $conditions[] = 'svo.assigned_to = :assigned_to';
            $params['assigned_to'] = $assignedTo;
        }

        $whereSql = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sql = sprintf(
            'SELECT svo.*,
                    so.code AS sales_order_code,
                    so.status AS sales_order_status,
                    c.name AS customer_name,
                    soi.line_no AS sales_order_line_no,
                    soi.description AS sales_order_item_description,
                    u.username AS assigned_username,
                    u.full_name AS assigned_full_name
             FROM service_orders svo
             INNER JOIN sales_orders so ON so.id = svo.sales_order_id
             LEFT JOIN customers c ON c.id = so.customer_id
             LEFT JOIN sales_order_items soi ON soi.id = svo.sales_order_item_id
             LEFT JOIN users u ON u.id = svo.assigned_to%s
             ORDER BY COALESCE(svo.planned_end_at, svo.created_at) DESC, svo.id DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $perPage,
            $offset
        );
        $countSql = 'SELECT COUNT(*) AS aggregate FROM service_orders svo INNER JOIN sales_orders so ON so.id = svo.sales_order_id LEFT JOIN customers c ON c.id = so.customer_id LEFT JOIN sales_order_items soi ON soi.id = svo.sales_order_item_id' . $whereSql;

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT svo.*,
                    so.code AS sales_order_code,
                    so.status AS sales_order_status,
                    so.order_date AS sales_order_date,
                    so.due_date AS sales_order_due_date,
                    c.name AS customer_name,
                    c.code AS customer_code,
                    soi.line_no AS sales_order_line_no,
                    soi.description AS sales_order_item_description,
                    soi.item_mode AS sales_order_item_mode,
                    soi.quantity AS sales_order_item_qty,
                    soi.fulfillment_status AS sales_order_item_status,
                    u.username AS assigned_username,
                    u.full_name AS assigned_full_name,
                    cb.username AS created_by_username,
                    cb.full_name AS created_by_full_name
             FROM service_orders svo
             INNER JOIN sales_orders so ON so.id = svo.sales_order_id
             LEFT JOIN customers c ON c.id = so.customer_id
             LEFT JOIN sales_order_items soi ON soi.id = svo.sales_order_item_id
             LEFT JOIN users u ON u.id = svo.assigned_to
             LEFT JOIN users cb ON cb.id = svo.created_by
             WHERE svo.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM service_orders WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function findActiveBySalesOrderItemId(int $salesOrderItemId): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM service_orders
             WHERE sales_order_item_id = :sales_order_item_id
               AND status NOT IN ("completed", "closed", "cancelled")
             ORDER BY id DESC
             LIMIT 1',
            ['sales_order_item_id' => $salesOrderItemId]
        );
    }

    public function findLatestBySalesOrderItemIds(array $salesOrderItemIds): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $salesOrderItemIds)));
        if ($itemIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($itemIds as $index => $itemId) {
            $key = 'item_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $itemId;
        }

        return $this->fetchAll(
            'SELECT svo.*,
                    u.username AS assigned_username,
                    u.full_name AS assigned_full_name
             FROM service_orders svo
             LEFT JOIN users u ON u.id = svo.assigned_to
             INNER JOIN (
                 SELECT sales_order_item_id, MAX(id) AS latest_id
                 FROM service_orders
                 WHERE sales_order_item_id IN (' . implode(', ', $placeholders) . ')
                 GROUP BY sales_order_item_id
             ) latest ON latest.latest_id = svo.id
             ORDER BY svo.id DESC',
            $params
        );
    }

    public function create(array $data): int
    {
        return $this->insert('service_orders', $data);
    }

    public function updateOrder(int $id, array $data): void
    {
        $this->updateById('service_orders', $id, $data);
    }

    public function logsByServiceOrderId(int $serviceOrderId): array
    {
        return $this->fetchAll(
            'SELECT l.*, u.full_name AS acted_by_name, u.username AS acted_by_username
             FROM service_order_logs l
             LEFT JOIN users u ON u.id = l.acted_by
             WHERE l.service_order_id = :service_order_id
             ORDER BY l.acted_at DESC, l.id DESC',
            ['service_order_id' => $serviceOrderId]
        );
    }

    public function createLog(array $data): int
    {
        return $this->insert('service_order_logs', $data);
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
}
