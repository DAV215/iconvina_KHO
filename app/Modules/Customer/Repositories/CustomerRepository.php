<?php

declare(strict_types=1);

namespace App\Modules\Customer\Repositories;

use App\Core\Database\Repository;

final class CustomerRepository extends Repository
{
    public function search(?string $search = null): array
    {
        if ($search === null || trim($search) === '') {
            return $this->fetchAll('SELECT * FROM customers ORDER BY id DESC LIMIT 100');
        }

        return $this->fetchAll(
            'SELECT * FROM customers
             WHERE code LIKE :search
                OR name LIKE :search
                OR contact_name LIKE :search
                OR phone LIKE :search
                OR email LIKE :search
             ORDER BY id DESC
             LIMIT 100',
            ['search' => '%' . trim($search) . '%']
        );
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