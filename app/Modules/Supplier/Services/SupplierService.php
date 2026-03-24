<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Supplier\Repositories\SupplierRepository;
use PDOException;

final class SupplierService
{
    public function __construct(private readonly SupplierRepository $repository)
    {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($this->normalizeFilters($filters), $this->normalizeSort($sort), $page, $perPage);
    }

    public function find(int $id): array
    {
        $supplier = $this->repository->findById($id);
        if ($supplier === null) {
            throw new HttpException('Không tìm thấy nhà cung cấp.', 404);
        }

        return $supplier;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $supplier = $this->find($id);
        $payload = $this->normalize($data);

        if ($supplier['code'] !== $payload['code']) {
            $this->assertUniqueCode($payload['code']);
        }

        $this->repository->update($id, $payload);
    }

    public function delete(int $id): void
    {
        $this->find($id);

        if ($this->repository->hasLinkedPurchaseOrders($id)) {
            throw new HttpException('Không thể xóa nhà cung cấp vì đã được tham chiếu trong đơn mua hàng.', 409, [
                'errors' => [
                    'supplier' => ['Nhà cung cấp đang được sử dụng trong đơn mua hàng.'],
                ],
            ]);
        }

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Không thể xóa nhà cung cấp vì đã có dữ liệu nghiệp vụ liên quan.', 409, [
                    'errors' => [
                        'supplier' => ['Nhà cung cấp đang được tham chiếu trong hệ thống ERP.'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    public function statuses(): array
    {
        return [
            '1' => 'Đang dùng',
            '0' => 'Ngưng dùng',
        ];
    }

    public function options(): array
    {
        return $this->repository->options();
    }

    public function sortOptions(): array
    {
        return [
            'updated_at' => 'Cập nhật',
            'code' => 'Mã',
            'name' => 'Tên nhà cung cấp',
            'contact_name' => 'Người liên hệ',
            'phone' => 'Điện thoại',
            'email' => 'Email',
        ];
    }

    private function normalize(array $data): array
    {
        return [
            'code' => strtoupper(trim((string) ($data['code'] ?? ''))),
            'name' => trim((string) ($data['name'] ?? '')),
            'contact_name' => $this->nullableString($data['contact_name'] ?? null),
            'phone' => $this->nullableString($data['phone'] ?? null),
            'email' => $this->nullableString($data['email'] ?? null),
            'tax_code' => $this->nullableString($data['tax_code'] ?? null),
            'address' => $this->nullableString($data['address'] ?? null),
            'note' => $this->nullableString($data['note'] ?? null),
            'is_active' => (string) ($data['is_active'] ?? '1') === '0' ? 0 : 1,
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        $status = (string) ($filters['status'] ?? '');

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => ($status === '1' || $status === '0') ? $status : '',
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
            throw new HttpException('Mã nhà cung cấp đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã nhà cung cấp đã tồn tại.'],
                ],
            ]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
