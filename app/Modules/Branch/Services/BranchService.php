<?php

declare(strict_types=1);

namespace App\Modules\Branch\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Branch\Repositories\BranchRepository;
use App\Modules\Company\Services\CompanyService;

final class BranchService
{
    public function __construct(
        private readonly BranchRepository $repository,
        private readonly CompanyService $companyService,
    ) {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($this->normalizeFilters($filters), $this->normalizeSort($sort), $page, $perPage);
    }

    public function find(int $id): array
    {
        $branch = $this->repository->findById($id);
        if ($branch === null) {
            throw new HttpException('Không tìm thấy chi nhánh.', 404);
        }

        return $branch;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data, true);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $branch = $this->find($id);
        $payload = $this->normalize($data, false);

        if ($branch['code'] !== $payload['code']) {
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
        if ($this->repository->hasDepartments($id)) {
            throw new HttpException('Không thể xóa chi nhánh vì còn phòng ban đang sử dụng.', 409, [
                'errors' => [
                    'branch' => ['Chi nhánh đang có phòng ban liên kết.'],
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
            'name' => 'Tên chi nhánh',
            'company_name' => 'Công ty',
        ];
    }

    public function companyOptions(): array
    {
        return $this->companyService->options();
    }

    public function options(): array
    {
        return $this->repository->options();
    }

    private function normalize(array $data, bool $isCreate): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $companyId = (int) ($data['company_id'] ?? 0);
        $errors = [];

        if ($code === '') {
            $errors['code'][] = 'Vui lòng nhập mã chi nhánh.';
        }
        if ($name === '') {
            $errors['name'][] = 'Vui lòng nhập tên chi nhánh.';
        }
        if ($companyId <= 0 || !$this->repository->companyExists($companyId)) {
            $errors['company_id'][] = 'Vui lòng chọn công ty hợp lệ.';
        }

        if ($errors !== []) {
            throw new HttpException('Dữ liệu chi nhánh không hợp lệ.', 422, ['errors' => $errors]);
        }

        $payload = [
            'company_id' => $companyId,
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
            'company_id' => (int) ($filters['company_id'] ?? 0),
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
            throw new HttpException('Mã chi nhánh đã tồn tại.', 422, ['errors' => ['code' => ['Mã chi nhánh đã tồn tại.']]]);
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
