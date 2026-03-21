<?php

declare(strict_types=1);

namespace App\Modules\Material\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\Material\Repositories\MaterialRepository;
use PDOException;

final class MaterialService
{
    public function __construct(private readonly MaterialRepository $repository)
    {
    }

    public function list(?string $search = null): array
    {
        return $this->repository->search($search);
    }

    public function find(int $id): array
    {
        $material = $this->repository->findById($id);
        if ($material === null) {
            throw new HttpException('Không tìm thấy nguyên vật liệu.', 404);
        }

        return $material;
    }

    public function categoryOptions(): array
    {
        return $this->repository->categoryOptions();
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $material = $this->find($id);
        $payload = $this->normalize($data);

        if ($material['code'] !== $payload['code']) {
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
                throw new HttpException('Không thể xóa nguyên vật liệu vì đã có dữ liệu liên quan.', 409, [
                    'errors' => [
                        'material' => ['Nguyên vật liệu đang được sử dụng trong hệ thống.'],
                    ],
                ]);
            }

            throw $exception;
        }
    }

    private function normalize(array $data): array
    {
        $errors = [];
        $categoryId = $this->normalizeOptionalInt($data['category_id'] ?? null);
        if ($categoryId !== null) {
            $category = $this->repository->findCategoryById($categoryId);
            if ($category === null || (int) ($category['is_active'] ?? 0) !== 1) {
                $errors['category_id'][] = 'Danh mục đã chọn không tồn tại hoặc đã ngừng sử dụng.';
            }
        }

        $standardCost = $this->normalizeDecimal($data['standard_cost'] ?? 0, 'standard_cost', false, $errors);
        $minStock = $this->normalizeDecimal($data['min_stock'] ?? 0, 'min_stock', true, $errors);

        if ($standardCost < 0) {
            $errors['standard_cost'][] = 'Giá chuẩn phải lớn hơn hoặc bằng 0.';
        }

        if ($minStock < 0) {
            $errors['min_stock'][] = 'Tồn tối thiểu phải lớn hơn hoặc bằng 0.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'code' => strtoupper(trim((string) ($data['code'] ?? ''))),
            'name' => trim((string) ($data['name'] ?? '')),
            'category_id' => $categoryId,
            'unit' => trim((string) ($data['unit'] ?? '')),
            'specification' => $this->nullableString($data['specification'] ?? null),
            'color' => $this->nullableString($data['color'] ?? null),
            'image_path' => $this->nullableString($data['image_path'] ?? null),
            'description' => $this->nullableString($data['description'] ?? null),
            'standard_cost' => $this->formatDecimal($standardCost),
            'min_stock' => $this->formatDecimal($minStock),
            'is_active' => $this->normalizeBoolInt($data['is_active'] ?? 0),
        ];
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã nguyên vật liệu đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã nguyên vật liệu đã tồn tại.'],
                ],
            ]);
        }
    }

    private function normalizeBoolInt(mixed $value): int
    {
        return (string) $value === '1' ? 1 : 0;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function normalizeDecimal(mixed $value, string $field, bool $nullable, array &$errors): float
    {
        $stringValue = trim((string) ($value ?? ''));

        if ($stringValue === '') {
            if ($nullable) {
                return 0.0;
            }

            $errors[$field][] = 'Trường này là bắt buộc.';

            return 0.0;
        }

        if (!is_numeric($stringValue)) {
            $errors[$field][] = 'Trường này phải là số.';

            return 0.0;
        }

        return round((float) $stringValue, 2);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function formatDecimal(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }
}