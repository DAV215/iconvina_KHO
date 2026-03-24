<?php

declare(strict_types=1);

namespace App\Modules\Role\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Permission\Repositories\PermissionRepository;
use App\Modules\Role\Repositories\RoleRepository;

final class RoleService
{
    public function __construct(
        private readonly RoleRepository $repository,
        private readonly PermissionRepository $permissionRepository,
    ) {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($filters, $sort, $page, $perPage);
    }

    public function find(int $id): array
    {
        $role = $this->repository->findById($id);
        if ($role === null) {
            throw new HttpException('Không tìm thấy vai trò.', 404);
        }

        return $role;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data, true);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $role = $this->find($id);
        $payload = $this->normalize($data, false);

        if ($role['code'] !== $payload['code']) {
            $this->assertUniqueCode($payload['code']);
        }

        $this->repository->update($id, $payload);
    }

    public function disable(int $id): void
    {
        $this->find($id);
        $this->repository->update($id, [
            'is_active' => 0,
            'updated_at' => $this->timestamp(),
        ]);
    }

    public function delete(int $id): void
    {
        $this->find($id);
        if ($this->repository->userCount($id) > 0) {
            throw new HttpException('Không thể xóa vai trò đang được gán cho người dùng.', 409, [
                'errors' => [
                    'role' => ['Vai trò đang được gán cho người dùng khác.'],
                ],
            ]);
        }

        $this->permissionRepository->deleteByRole($id);
        $this->repository->delete($id);
    }

    public function statuses(): array
    {
        return [
            'active' => 'Đang dùng',
            'inactive' => 'Ngưng dùng',
        ];
    }

    public function sortOptions(): array
    {
        return [
            'updated_at' => 'Cập nhật',
            'code' => 'Mã',
            'name' => 'Tên vai trò',
        ];
    }

    private function normalize(array $data, bool $isCreate): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $errors = [];

        if ($code === '') {
            $errors['code'][] = 'Vui lòng nhập mã vai trò.';
        }
        if ($name === '') {
            $errors['name'][] = 'Vui lòng nhập tên vai trò.';
        }

        if ($errors !== []) {
            throw new HttpException('Dữ liệu vai trò không hợp lệ.', 422, ['errors' => $errors]);
        }

        $payload = [
            'code' => $code,
            'name' => $name,
            'description' => $this->nullableString($data['description'] ?? null),
            'is_active' => (string) ($data['is_active'] ?? '1') === '0' ? 0 : 1,
            'updated_at' => $this->timestamp(),
        ];

        if ($isCreate) {
            $payload['created_at'] = $this->timestamp();
        }

        return $payload;
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã vai trò đã tồn tại.', 422, ['errors' => ['code' => ['Mã vai trò đã tồn tại.']]]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}
