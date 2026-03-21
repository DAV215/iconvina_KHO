<?php

declare(strict_types=1);

namespace App\Modules\Component\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Component\Repositories\ComponentRepository;
use PDOException;

final class ComponentService
{
    public function __construct(private readonly ComponentRepository $repository)
    {
    }

    public function list(?string $search = null): array
    {
        return $this->repository->search($search);
    }

    public function find(int $id): array
    {
        $component = $this->repository->findById($id);
        if ($component === null) {
            throw new HttpException('Không tìm thấy bán thành phẩm.', 404);
        }

        return $component;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $component = $this->find($id);
        $payload = $this->normalize($data);

        if ($component['code'] !== $payload['code']) {
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
                throw new HttpException('Không thể xóa bán thành phẩm vì đã có dữ liệu liên quan.', 409, [
                    'errors' => [
                        'component' => ['Bán thành phẩm đang được sử dụng trong hệ thống.'],
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
            'component_type' => trim((string) ($data['unit'] ?? '')),
            'standard_cost' => $this->formatDecimal((float) ($data['standard_cost'] ?? 0)),
            'is_active' => $this->normalizeBoolInt($data['is_active'] ?? 0),
        ];
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã bán thành phẩm đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã bán thành phẩm đã tồn tại.'],
                ],
            ]);
        }
    }

    private function normalizeBoolInt(mixed $value): int
    {
        return (string) $value === '1' ? 1 : 0;
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }
}