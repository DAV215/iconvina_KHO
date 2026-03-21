<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;

abstract class Repository
{
    public function __construct(protected readonly Connection $connection)
    {
    }

    protected function pdo(): PDO
    {
        return $this->connection->pdo();
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $statement = $this->pdo()->prepare($sql);

        return $statement->execute($params);
    }

    protected function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->execute($sql, $data);

        return (int) $this->pdo()->lastInsertId();
    }

    protected function updateById(string $table, int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = $column . ' = :' . $column;
        }
        $data['id'] = $id;

        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $table, implode(', ', $sets));

        return $this->execute($sql, $data);
    }

    protected function deleteById(string $table, int $id): bool
    {
        return $this->execute(sprintf('DELETE FROM %s WHERE id = :id', $table), ['id' => $id]);
    }
}
