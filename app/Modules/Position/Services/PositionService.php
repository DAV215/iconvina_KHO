<?php

declare(strict_types=1);

namespace App\Modules\Position\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Department\Services\DepartmentService;
use App\Modules\Position\Repositories\PositionRepository;

final class PositionService
{
    public function __construct(
        private readonly PositionRepository $repository,
        private readonly DepartmentService $departmentService,
    ) {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($this->normalizeFilters($filters), $this->normalizeSort($sort), $page, $perPage);
    }

    public function find(int $id): array
    {
        $position = $this->repository->findById($id);
        if ($position === null) {
            throw new HttpException('Không tìm thấy chức danh.', 404);
        }

        return $position;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data, true);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $position = $this->find($id);
        $payload = $this->normalize($data, false);

        if ($position['code'] !== $payload['code']) {
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
            'updated_by' => $this->actorId(),
        ]);
    }

    public function delete(int $id): void
    {
        $this->find($id);
        if ($this->repository->hasUsers($id)) {
            throw new HttpException('Không thể xóa chức danh vì đang có người dùng sử dụng.', 409, [
                'errors' => [
                    'position' => ['Chức danh đang được gán cho người dùng khác.'],
                ],
            ]);
        }

        $timestamp = $this->timestamp();
        $this->repository->update($id, [
            'is_active' => 0,
            'deleted_at' => $timestamp,
            'deleted_by' => $this->actorId(),
            'updated_at' => $timestamp,
            'updated_by' => $this->actorId(),
        ]);
    }

    public function statuses(): array
    {
        return [
            'active' => 'Đang dùng',
            'inactive' => 'Ngưng dùng',
            'deleted' => 'Đã xóa',
        ];
    }

    public function sortOptions(): array
    {
        return [
            'updated_at' => 'Cập nhật',
            'code' => 'Mã',
            'name' => 'Tên chức danh',
            'department_name' => 'Phòng ban',
        ];
    }

    public function departmentOptions(): array
    {
        return $this->departmentService->parentOptions();
    }

    public function options(): array
    {
        return $this->repository->options();
    }

    private function normalize(array $data, bool $isCreate): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $departmentId = (int) ($data['department_id'] ?? 0);
        $errors = [];

        if ($code === '') {
            $errors['code'][] = 'Vui lòng nhập mã chức danh.';
        }
        if ($name === '') {
            $errors['name'][] = 'Vui lòng nhập tên chức danh.';
        }
        if ($departmentId > 0 && !$this->repository->departmentExists($departmentId)) {
            $errors['department_id'][] = 'Phòng ban được chọn không tồn tại.';
        }

        if ($errors !== []) {
            throw new HttpException('Dữ liệu chức danh không hợp lệ.', 422, ['errors' => $errors]);
        }

        $payload = [
            'department_id' => $departmentId > 0 ? $departmentId : null,
            'code' => $code,
            'name' => $name,
            'description' => $this->nullableString($data['description'] ?? null),
            'is_active' => (string) ($data['is_active'] ?? '1') === '0' ? 0 : 1,
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->actorId(),
        ];

        if ($isCreate) {
            $payload['created_at'] = $this->timestamp();
            $payload['created_by'] = $this->actorId();
            $payload['deleted_at'] = null;
            $payload['deleted_by'] = null;
        }

        return $payload;
    }

    private function normalizeFilters(array $filters): array
    {
        $status = (string) ($filters['status'] ?? '');

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => in_array($status, ['active', 'inactive', 'deleted'], true) ? $status : '',
            'department_id' => (int) ($filters['department_id'] ?? 0),
        ];
    }

    private function normalizeSort(array $sort): array
    {
        $allowed = array_keys($this->sortOptions());
        $by = (string) ($sort['by'] ?? 'updated_at');
        $dir = strtoupper((string) ($sort['dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        return [
            'by' => in_array($by, $allowed, true) ? $by : 'updated_at',
            'dir' => $dir,
        ];
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã chức danh đã tồn tại.', 422, ['errors' => ['code' => ['Mã chức danh đã tồn tại.']]]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function actorId(): ?int
    {
        $user = auth_user();
        $id = is_array($user) ? (int) ($user['id'] ?? 0) : 0;
        return $id > 0 ? $id : null;
    }

    private function timestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}
