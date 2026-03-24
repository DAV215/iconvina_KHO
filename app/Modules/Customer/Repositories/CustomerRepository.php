<?php

declare(strict_types=1);

namespace App\Modules\Customer\Repositories;

use App\Core\Database\Repository;

final class CustomerRepository extends Repository
{
    public function search(?string $search = null, int $page = 1, int $perPage = 25): array
    {
        $offset = max(0, ($page - 1) * $perPage);

        if ($search === null || trim($search) === '') {
            $total = (int) (($this->fetchOne('SELECT COUNT(*) AS aggregate FROM customers')['aggregate'] ?? 0));
            $items = $this->fetchAll(sprintf('SELECT * FROM customers ORDER BY id DESC LIMIT %d OFFSET %d', $perPage, $offset));

            return ['items' => $items, 'total' => $total];
        }

        $params = ['search' => '%' . trim($search) . '%'];
        $where = ' WHERE code LIKE :search
                    OR name LIKE :search
                    OR contact_name LIKE :search
                    OR phone LIKE :search
                    OR email LIKE :search';
        $total = (int) (($this->fetchOne('SELECT COUNT(*) AS aggregate FROM customers' . $where, $params)['aggregate'] ?? 0));
        $items = $this->fetchAll(
            sprintf(
                'SELECT * FROM customers%s ORDER BY id DESC LIMIT %d OFFSET %d',
                $where,
                $perPage,
                $offset
            ),
            $params
        );

        return ['items' => $items, 'total' => $total];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM customers WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM customers WHERE code = :code LIMIT 1', ['code' => $code]);
    }

    public function create(array $data): int
    {
        return $this->insert('customers', $data);
    }

    public function options(): array
    {
        return $this->fetchAll('SELECT id, code, name FROM customers ORDER BY name ASC, id ASC LIMIT 500');
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById('customers', $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById('customers', $id);
    }
}
