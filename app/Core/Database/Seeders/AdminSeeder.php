<?php

declare(strict_types=1);

namespace App\Core\Database\Seeders;

use App\Core\Config\Repository as ConfigRepository;
use App\Core\Database\Connection;
use PDO;

final class AdminSeeder
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ConfigRepository $config,
    ) {
    }

    public function run(): void
    {
        $username = (string) $this->config->get('app.default_admin.username', 'admin');
        $password = (string) $this->config->get('app.default_admin.password', 'ChangeMe123!');
        $fullName = (string) $this->config->get('app.default_admin.full_name', 'System Administrator');
        $email = (string) $this->config->get('app.default_admin.email', 'admin@iconvina.local');
        $phone = (string) $this->config->get('app.default_admin.phone', '');

        $pdo = $this->connection->pdo();
        $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $existingId = $statement->fetchColumn();

        $payload = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
            'role_code' => 'super_admin',
            'status' => 'active',
        ];

        if ($existingId !== false) {
            $update = $pdo->prepare('UPDATE users SET password_hash = :password_hash, full_name = :full_name, email = :email, phone = :phone, role_code = :role_code, status = :status WHERE username = :username');
            $update->execute($payload);
            fwrite(STDOUT, "Admin user updated: {$username}`n");
            return;
        }

        $insert = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, email, phone, role_code, status) VALUES (:username, :password_hash, :full_name, :email, :phone, :role_code, :status)');
        $insert->execute($payload);
        fwrite(STDOUT, "Admin user created: {$username}`n");
    }
}
