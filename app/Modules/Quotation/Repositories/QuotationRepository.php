<?php

declare(strict_types=1);

namespace App\Modules\Quotation\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class QuotationRepository extends Repository
{
    public function search(?string $search = null, ?string $status = null, int $page = 1, int $perPage = 25): array
    {
        $sql = 'SELECT q.*, c.code AS customer_code, c.name AS customer_name
                FROM quotations q
                INNER JOIN customers c ON c.id = q.customer_id
                WHERE 1 = 1';
        $countSql = 'SELECT COUNT(*) AS aggregate
                     FROM quotations q
                     INNER JOIN customers c ON c.id = q.customer_id
                     WHERE 1 = 1';
        $params = [];
        $offset = max(0, ($page - 1) * $perPage);

        if ($search !== null && trim($search) !== '') {
            $params['search'] = '%' . trim($search) . '%';
            $sql .= ' AND (q.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
            $countSql .= ' AND (q.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
        }

        if ($status !== null && trim($status) !== '') {
            $params['status'] = trim($status);
            $sql .= ' AND q.status = :status';
            $countSql .= ' AND q.status = :status';
        }

        $sql .= sprintf(' ORDER BY q.id DESC LIMIT %d OFFSET %d', $perPage, $offset);

        return [
            'items' => $this->fetchAll($sql, $params),
            'total' => (int) (($this->fetchOne($countSql, $params)['aggregate'] ?? 0)),
        ];
    }

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT q.id, q.code, q.customer_id, q.quote_date, q.status, q.subtotal, q.discount_amount, q.tax_amount, q.total_amount,
                    c.code AS customer_code, c.name AS customer_name
             FROM quotations q
             INNER JOIN customers c ON c.id = q.customer_id
             ORDER BY q.id DESC
             LIMIT 200'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT q.*, c.code AS customer_code, c.name AS customer_name, c.contact_name AS customer_contact_name,
                    c.phone AS customer_phone, c.email AS customer_email, c.tax_code AS customer_tax_code,
                    c.address AS customer_address
             FROM quotations q
             INNER JOIN customers c ON c.id = q.customer_id
             WHERE q.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM quotations WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function latestCodeLike(string $pattern): ?string
    {
        $row = $this->fetchOne(
            'SELECT code
             FROM quotations
             WHERE code LIKE :pattern
             ORDER BY code DESC
             LIMIT 1',
            ['pattern' => $pattern]
        );

        return $row !== null ? (string) ($row['code'] ?? '') : null;
    }

    public function findLatestByQuotationId(int $quotationId): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM quotation_logs
             WHERE module = :module
               AND entity_id = :entity_id
             ORDER BY id DESC
             LIMIT 1',
            [
                'module' => 'quotation',
                'entity_id' => $quotationId,
            ]
        );
    }

    public function findItemsByQuotationId(int $quotationId): array
    {
        return $this->fetchAll(
            'SELECT qi.*,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit
             FROM quotation_items qi
             LEFT JOIN components c ON c.id = qi.component_id
             LEFT JOIN materials m ON m.id = qi.material_id
             WHERE qi.quotation_id = :quotation_id
             ORDER BY qi.line_no ASC, qi.id ASC',
            ['quotation_id' => $quotationId]
        );
    }

    public function findItemsByQuotationIds(array $quotationIds): array
    {
        if ($quotationIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach (array_values($quotationIds) as $index => $quotationId) {
            $key = 'quotation_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = (int) $quotationId;
        }

        return $this->fetchAll(
            'SELECT qi.*,
                    c.code AS component_code,
                    c.name AS component_name,
                    c.unit AS component_unit,
                    m.code AS material_code,
                    m.name AS material_name,
                    m.unit AS material_unit
             FROM quotation_items qi
             LEFT JOIN components c ON c.id = qi.component_id
             LEFT JOIN materials m ON m.id = qi.material_id
             WHERE qi.quotation_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY qi.quotation_id ASC, qi.line_no ASC, qi.id ASC',
            $params
        );
    }

    public function updateItemEngineering(int $quotationItemId, array $data): void
    {
        $this->updateById('quotation_items', $quotationItemId, $data);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->updateById('quotations', $id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function logsByQuotationId(int $quotationId): array
    {
        return $this->fetchAll(
            'SELECT ql.*,
                    u.username AS acted_username,
                    u.full_name AS acted_full_name
             FROM quotation_logs ql
             LEFT JOIN users u ON u.id = ql.acted_by
             WHERE ql.module = :module
               AND ql.entity_id = :entity_id
             ORDER BY ql.id DESC',
            [
                'module' => 'quotation',
                'entity_id' => $quotationId,
            ]
        );
    }

    public function createLog(array $data): int
    {
        return $this->insert('quotation_logs', $data);
    }

    public function findLogByAction(int $quotationId, string $action): ?array
    {
        return $this->fetchOne(
            'SELECT ql.*,
                    u.username AS acted_username,
                    u.full_name AS acted_full_name
             FROM quotation_logs ql
             LEFT JOIN users u ON u.id = ql.acted_by
             WHERE ql.module = :module
               AND ql.entity_id = :entity_id
               AND ql.action = :action
             ORDER BY ql.id DESC
             LIMIT 1',
            [
                'module' => 'quotation',
                'entity_id' => $quotationId,
                'action' => $action,
            ]
        );
    }

    public function create(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $started = !$pdo->inTransaction();
        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $quotationId = $this->insert('quotations', $header);
            $this->insertItems($quotationId, $items);
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }

            return $quotationId;
        } catch (Throwable $exception) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function update(int $id, array $header, array $items): void
    {
        $pdo = $this->pdo();
        $started = !$pdo->inTransaction();
        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $this->updateById('quotations', $id, $header);
            $this->execute('DELETE FROM quotation_items WHERE quotation_id = :quotation_id', ['quotation_id' => $id]);
            $this->insertItems($id, $items);
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $pdo = $this->pdo();
        $started = !$pdo->inTransaction();
        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $this->execute('DELETE FROM quotation_items WHERE quotation_id = :quotation_id', ['quotation_id' => $id]);
            $this->deleteById('quotations', $id);
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
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

    private function insertItems(int $quotationId, array $items): void
    {
        foreach ($items as $item) {
            $this->insert('quotation_items', [
                'quotation_id' => $quotationId,
                'line_no' => $item['line_no'],
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
            ]);
        }
    }
}
