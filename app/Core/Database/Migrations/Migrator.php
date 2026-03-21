<?php

declare(strict_types=1);

namespace App\Core\Database\Migrations;

use App\Core\Database\Connection;
use PDO;

final class Migrator
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $migrationPath,
    ) {
    }

    public function run(): void
    {
        $pdo = $this->connection->pdo();
        $this->ensureMigrationTable($pdo);

        $executed = $this->executedMigrations($pdo);
        $files = glob($this->migrationPath . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $executed, true)) {
                continue;
            }

            $migration = require $file;

            $pdo->beginTransaction();
            $migration->up($pdo);
            $statement = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
            $statement->execute(['migration' => $name]);
            $pdo->commit();

            fwrite(STDOUT, "Migrated: {$name}`n");
        }
    }

    private function ensureMigrationTable(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function executedMigrations(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT migration FROM migrations ORDER BY id ASC');

        return $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
