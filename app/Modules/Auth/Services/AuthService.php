<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Auth\Repositories\AuthRepository;

final class AuthService
{
    public function __construct(private readonly AuthRepository $repository)
    {
    }

    public function attempt(string $username, string $password): array
    {
        $username = strtolower(trim($username));
        if ($username === '' || trim($password) === '') {
            throw new HttpException('Vui lòng nhập tên đăng nhập và mật khẩu.', 422, [
                'errors' => [
                    'username' => ['Vui lòng nhập tên đăng nhập.'],
                    'password' => ['Vui lòng nhập mật khẩu.'],
                ],
            ]);
        }

        $user = $this->repository->findActiveByUsername($username);
        if ($user === null || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            throw new HttpException('Tên đăng nhập hoặc mật khẩu không đúng.', 422, [
                'errors' => [
                    'auth' => ['Tên đăng nhập hoặc mật khẩu không đúng.'],
                ],
            ]);
        }

        return $this->sanitizeUser($user);
    }

    public function authUserById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $user = $this->repository->findAuthUserById($id);

        return $user === null ? null : $this->sanitizeUser($user);
    }

    public function login(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user_id'] = (int) ($user['id'] ?? 0);
        $_SESSION['auth_user'] = $user;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['user_id'], $_SESSION['auth_user'], $_SESSION['_auth_user']);
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password_hash']);

        return $user;
    }
}
