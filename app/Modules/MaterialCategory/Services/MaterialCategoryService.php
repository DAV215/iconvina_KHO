<?php

declare(strict_types=1);

namespace App\Modules\MaterialCategory\Services;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;
use App\Modules\MaterialCategory\Repositories\MaterialCategoryRepository;

final class MaterialCategoryService
{
    public function __construct(private readonly MaterialCategoryRepository $repository)
    {
    }

    public function list(?string $search = null, int $page = 1, int $perPage = 25): array
    {
        $rows = $search !== null && trim($search) !== ''
            ? $this->repository->search($search)
            : $this->repository->all();

        $flattened = $this->flattenTree($rows);
        $offset = max(0, ($page - 1) * $perPage);

        return [
            'items' => array_slice($flattened, $offset, $perPage),
            'total' => count($flattened),
        ];
    }

    public function find(int $id): array
    {
        $category = $this->repository->findById($id);
        if ($category === null) {
            throw new HttpException('Không tìm thấy danh mục nguyên vật liệu.', 404);
        }

        return $category;
    }

    public function parentOptions(?int $excludeId = null): array
    {
        $rows = $this->repository->all();
        $excludedIds = $excludeId === null ? [] : $this->descendantIds($rows, $excludeId);
        if ($excludeId !== null) {
            $excludedIds[] = $excludeId;
        }

        $options = [];
        foreach ($this->flattenTree($rows) as $row) {
            if (in_array((int) $row['id'], $excludedIds, true)) {
                continue;
            }

            $options[] = [
                'id' => (int) $row['id'],
                'code' => (string) $row['code'],
                'name' => (string) $row['name'],
                'label' => $this->treeLabel((int) ($row['depth'] ?? 0), (string) $row['name']),
            ];
        }

        return $options;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data);
        $this->assertUniqueCode($payload['code']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $category = $this->find($id);
        $payload = $this->normalize($data, $id);

        if ($category['code'] !== $payload['code']) {
            $this->assertUniqueCode($payload['code']);
        }

        $this->repository->update($id, $payload);
    }

    public function delete(int $id): void
    {
        $this->find($id);

        if ($this->repository->countChildrenByParentId($id) > 0) {
            throw new HttpException('Không thể xóa danh mục vì đang có danh mục con.', 409, [
                'errors' => [
                    'category' => ['Danh mục đang có danh mục con.'],
                ],
            ]);
        }

        if ($this->repository->countMaterialsByCategoryId($id) > 0) {
            throw new HttpException('Không thể xóa danh mục vì đang có nguyên vật liệu tham chiếu.', 409, [
                'errors' => [
                    'category' => ['Danh mục đang được sử dụng bởi nguyên vật liệu.'],
                ],
            ]);
        }

        $this->repository->delete($id);
    }

    public function treeOptionsForMaterials(): array
    {
        $rows = array_values(array_filter(
            $this->repository->all(),
            static fn (array $row): bool => (int) ($row['is_active'] ?? 1) === 1
        ));

        $options = [];
        foreach ($this->flattenTree($rows) as $row) {
            $options[] = [
                'id' => (int) $row['id'],
                'code' => (string) $row['code'],
                'name' => (string) $row['name'],
                'label' => $this->treeLabel((int) ($row['depth'] ?? 0), (string) $row['name']),
            ];
        }

        return $options;
    }

    private function normalize(array $data, ?int $currentId = null): array
    {
        $parentId = $this->normalizeOptionalInt($data['parent_id'] ?? null);
        $isActive = (string) ($data['is_active'] ?? '1') === '0' ? 0 : 1;
        $errors = [];

        if ($parentId !== null) {
            $parent = $this->repository->findById($parentId);
            if ($parent === null) {
                $errors['parent_id'][] = 'Danh mục cha không tồn tại.';
            }

            if ($currentId !== null && $parentId === $currentId) {
                $errors['parent_id'][] = 'Danh mục cha không hợp lệ.';
            }

            if ($currentId !== null && in_array($parentId, $this->descendantIds($this->repository->all(), $currentId), true)) {
                $errors['parent_id'][] = 'Không thể chọn danh mục con làm danh mục cha.';
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'code' => strtoupper(trim((string) ($data['code'] ?? ''))),
            'name' => trim((string) ($data['name'] ?? '')),
            'parent_id' => $parentId,
            'is_active' => $isActive,
        ];
    }

    private function assertUniqueCode(string $code): void
    {
        if ($this->repository->findByCode($code) !== null) {
            throw new HttpException('Mã danh mục đã tồn tại.', 422, [
                'errors' => [
                    'code' => ['Mã danh mục đã tồn tại.'],
                ],
            ]);
        }
    }

    private function flattenTree(array $rows): array
    {
        $children = [];
        foreach ($rows as $row) {
            $parentId = $row['parent_id'] ?? null;
            $key = $parentId === null ? 'root' : (string) $parentId;
            $children[$key][] = $row;
        }

        foreach ($children as &$siblings) {
            usort($siblings, static function (array $left, array $right): int {
                return [$left['name'], $left['id']] <=> [$right['name'], $right['id']];
            });
        }
        unset($siblings);

        $flattened = [];
        $visited = [];
        $walker = function (?int $parentId, int $depth) use (&$walker, &$flattened, &$children, &$visited): void {
            $key = $parentId === null ? 'root' : (string) $parentId;
            foreach ($children[$key] ?? [] as $row) {
                $id = (int) $row['id'];
                if (isset($visited[$id])) {
                    continue;
                }

                $visited[$id] = true;
                $row['depth'] = $depth;
                $flattened[] = $row;
                $walker($id, $depth + 1);
            }
        };

        $walker(null, 0);

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if (isset($visited[$id])) {
                continue;
            }

            $row['depth'] = 0;
            $flattened[] = $row;
        }

        return $flattened;
    }

    private function descendantIds(array $rows, int $id): array
    {
        $childrenByParent = [];
        foreach ($rows as $row) {
            if ($row['parent_id'] === null) {
                continue;
            }

            $childrenByParent[(int) $row['parent_id']][] = (int) $row['id'];
        }

        $descendants = [];
        $stack = $childrenByParent[$id] ?? [];
        while ($stack !== []) {
            $childId = array_pop($stack);
            if (in_array($childId, $descendants, true)) {
                continue;
            }

            $descendants[] = $childId;
            foreach ($childrenByParent[$childId] ?? [] as $nextId) {
                $stack[] = $nextId;
            }
        }

        return $descendants;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        return ctype_digit($value) ? (int) $value : null;
    }

    private function treeLabel(int $depth, string $name): string
    {
        if ($depth <= 0) {
            return $name;
        }

        return str_repeat('-- ', $depth) . $name;
    }
}
