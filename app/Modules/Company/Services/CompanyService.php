<?php

declare(strict_types=1);

namespace App\Modules\Company\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Company\Repositories\CompanyRepository;

final class CompanyService
{
    public function __construct(private readonly CompanyRepository $repository)
    {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($this->normalizeFilters($filters), $this->normalizeSort($sort), $page, $perPage);
    }

    public function find(int $id): array
    {
        $company = $this->repository->findById($id);
        if ($company === null) {
            throw new HttpException('Không tìm thấy công ty.', 404);
        }

        return $company;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data, true);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $company = $this->find($id);
        $payload = $this->normalize($data, false);

        if ($company['code'] !== $payload['code']) {
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
        if ($this->repository->hasBranches($id)) {
            throw new HttpException('Không thể xóa công ty vì còn chi nhánh đang sử dụng.', 409, [
                'errors' => [
                    'company' => ['Công ty đang có chi nhánh liên kết.'],
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
            'name' => 'Tên công ty',
        ];
    }

    public function options(): array
    {
        return $this->repository->options();
    }

    private function normalize(array $data, bool $isCreate): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $name = trim((string) ($data['name'] ?? ''));
        $errors = [];

        if ($code === '') {
            $errors['code'][] = 'Vui lòng nhập mã công ty.';
        }
        if ($name === '') {
            $errors['name'][] = 'Vui lòng nhập tên công ty.';
        }

        if ($errors !== []) {
            throw new HttpException('Dữ liệu công ty không hợp lệ.', 422, ['errors' => $errors]);
        }

        $payload = [
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
            throw new HttpException('Mã công ty đã tồn tại.', 422, ['errors' => ['code' => ['Mã công ty đã tồn tại.']]]);
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
