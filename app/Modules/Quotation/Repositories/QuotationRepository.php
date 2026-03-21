<?php

declare(strict_types=1);

namespace App\Modules\Quotation\Repositories;

use App\Core\Database\Repository;
use Throwable;

final class QuotationRepository extends Repository
{
    public function search(?string $search = null, ?string $status = null): array
    {
        $sql = 'SELECT q.*, c.code AS customer_code, c.name AS customer_name
                FROM quotations q
                INNER JOIN customers c ON c.id = q.customer_id
                WHERE 1 = 1';
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $params['search'] = '%' . trim($search) . '%';
            $sql .= ' AND (q.code LIKE :search OR c.code LIKE :search OR c.name LIKE :search)';
        }

        if ($status !== null && trim($status) !== '') {
            $params['status'] = trim($status);
            $sql .= ' AND q.status = :status';
        }

        $sql .= ' ORDER BY q.id DESC LIMIT 100';

        return $this->fetchAll($sql, $params);
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

    public function findItemsByQuotationId(int $quotationId): array
    {
        return $this->fetchAll(
            'SELECT *
             FROM quotation_items
             WHERE quotation_id = :quotation_id
             ORDER BY line_no ASC, id ASC',
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
            'SELECT *
             FROM quotation_items
             WHERE quotation_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY quotation_id ASC, line_no ASC, id ASC',
            $params
        );
    }

    public function create(array $header, array $items): int
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $quotationId = $this->insert('quotations', $header);
            $this->insertItems($quotationId, $items);
            $pdo->commit();

            return $quotationId;
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
            $this->updateById('quotations', $id, $header);
            $this->execute('DELETE FROM quotation_items WHERE quotation_id = :quotation_id', ['quotation_id' => $id]);
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
            $this->execute('DELETE FROM quotation_items WHERE quotation_id = :quotation_id', ['quotation_id' => $id]);
            $this->deleteById('quotations', $id);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
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
                'item_type' => $item['item_type'],
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