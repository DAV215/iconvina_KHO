<?php

declare(strict_types=1);

namespace App\Modules\Department\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Branch\Services\BranchService;
use App\Modules\Department\Repositories\DepartmentRepository;

final class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepository $repository,
        private readonly BranchService $branchService,
    ) {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($this->normalizeFilters($filters), $this->normalizeSort($sort), $page, $perPage);
    }

    public function find(int $id): array
    {
        $department = $this->repository->findById($id);
        if ($department === null) {
            throw new HttpException('Không tìm thấy phòng ban.', 404);
        }

        return $department;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data, true);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $department = $this->find($id);
        $payload = $this->normalize($data, false, $id);

        if ($department['code'] !== $payload['code']) {
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
        if ($this->repository->hasChildren($id)) {
            throw new HttpException('Không thể xóa phòng ban vì còn phòng ban con.', 409, [
                'errors' => [
                    'department' => ['Phòng ban đang có phòng ban con liên kết.'],
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
            'name' => 'Tên phòng ban',
            'branch_name' => 'Chi nhánh',
        ];
    }

    public function branchOptions(): array
    {
        return $this->branchService->options();
    }

    public function parentOptions(?int $excludeId = null): array
    {
        return $this->repository->options($excludeId);
    }

    private function normalize(array $data, bool $isCreate, ?int $departmentId = null): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $branchId = (int) ($data['branch_id'] ?? 0);
        $parentId = (int) ($data['parent_id'] ?? 0);
        $errors = [];

        if ($code === '') {
            $errors['code'][] = 'Vui lòng nhập mã phòng ban.';
        }
        if ($name === '') {
            $errors['name'][] = 'Vui lòng nhập tên phòng ban.';
        }
        if ($branchId <= 0 || !$this->repository->branchExists($branchId)) {
            $errors['branch_id'][] = 'Vui lòng chọn chi nhánh hợp lệ.';
        }
        if ($parentId > 0) {
            if ($departmentId !== null && $parentId === $departmentId) {
                $errors['parent_id'][] = 'Phòng ban cha không thể là chính nó.';
            } elseif (!$this->repository->parentExists($parentId)) {
                $errors['parent_id'][] = 'Phòng ban cha không tồn tại.';
            }
        }

        if ($errors !== []) {
            throw new HttpException('Dữ liệu phòng ban không hợp lệ.', 422, ['errors' => $errors]);
        }

        $payload = [
            'branch_id' => $branchId,
            'parent_id' => $parentId > 0 ? $parentId : null,
            'code' => $code,
            'name' => $name,
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
            'branch_id' => (int) ($filters['branch_id'] ?? 0),
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
            throw new HttpException('Mã phòng ban đã tồn tại.', 422, ['errors' => ['code' => ['Mã phòng ban đã tồn tại.']]]);
        }
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
