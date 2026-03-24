<?php

declare(strict_types=1);

namespace App\Modules\Customer\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Customer\Repositories\CustomerRepository;
use PDOException;

final class CustomerService
{
    public function __construct(private readonly CustomerRepository $repository)
    {
    }

    public function list(?string $search = null, int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($search, $page, $perPage);
    }

    public function find(int $id): array
    {
        $customer = $this->repository->findById($id);
        if ($customer === null) {
            throw new HttpException('Không tìm thấy khách hàng.', 404);
        }

        return $customer;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $customer = $this->find($id);
        $payload = $this->normalize($data);

        if ($customer['code'] !== $payload['code']) {
            $this->assertUniqueCode($payload['code']);
        }

        $this->repository->update($id, $payload);
    }

    public function delete(int $id): void
    {
        $this->find($id);

        try {
            $this->repository->delete($id);
        } catch (PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'foreign key')) {
                throw new HttpException('Không thể xóa khách hàng vì đã có dữ liệu nghiệp vụ liên quan.', 409, [
                    'errors' => [
                        'customer' => ['Khách hàng đang được tham chiếu trong hệ thống ERP. Hãy xóa hoặc cập nhật dữ liệu liên quan trước.'],
                    ],
                ]);
            }

            throw $exception;
        }
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
        ];
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã khách hàng đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã khách hàng đã tồn tại.'],
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
