<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\User\Repositories\UserRepository;

final class UserService
{
    private const STATUSES = ['draft', 'active', 'suspended', 'resigned', 'deleted'];

    public function __construct(private readonly UserRepository $repository)
    {
    }

    public function list(array $filters = [], array $sort = [], int $page = 1, int $perPage = 25): array
    {
        return $this->repository->search($this->normalizeFilters($filters), $this->normalizeSort($sort), $page, $perPage);
    }

    public function find(int $id): array
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new HttpException('Không tìm thấy người dùng.', 404);
        }

        return $user;
    }

    public function create(array $data): int
    {
        $payload = $this->normalize($data, true);
        $this->assertUniqueCode($payload['code']);
        $this->assertUniqueUsername($payload['username']);

        return $this->repository->create($payload);
    }

    public function update(int $id, array $data): void
    {
        $currentUser = $this->find($id);
        $payload = $this->normalize($data, false, $id, $currentUser);

        if ($currentUser['code'] !== $payload['code']) {
            $this->assertUniqueCode($payload['code']);
        }
        if ($currentUser['username'] !== $payload['username']) {
            $this->assertUniqueUsername($payload['username']);
        }

        $this->repository->update($id, $payload);
    }

    public function disable(int $id): void
    {
        $user = $this->find($id);
        if ($user['deleted_at'] !== null || $user['status'] === 'deleted') {
            throw new HttpException('Không thể khóa tài khoản đã xóa.', 409);
        }

        $this->repository->update($id, [
            'status' => 'suspended',
            'is_active' => 0,
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->actorId(),
        ]);
    }

    public function delete(int $id): void
    {
        $user = $this->find($id);
        if ($user['deleted_at'] !== null || $user['status'] === 'deleted') {
            return;
        }

        $actorId = $this->actorId();
        $timestamp = $this->timestamp();
        $this->repository->update($id, [
            'status' => 'deleted',
            'is_active' => 0,
            'deleted_at' => $timestamp,
            'deleted_by' => $actorId,
            'updated_at' => $timestamp,
            'updated_by' => $actorId,
        ]);
    }

    public function statuses(): array
    {
        return [
            'draft' => 'Nháp',
            'active' => 'Đang hoạt động',
            'suspended' => 'Tạm khóa',
            'resigned' => 'Đã nghỉ việc',
            'deleted' => 'Đã xóa',
        ];
    }

    public function sortOptions(): array
    {
        return [
            'updated_at' => 'Cập nhật',
            'code' => 'Mã',
            'username' => 'Tài khoản',
            'full_name' => 'Họ tên',
            'status' => 'Trạng thái',
            'joined_at' => 'Ngày vào làm',
            'last_login_at' => 'Đăng nhập gần nhất',
        ];
    }

    public function roleOptions(): array
    {
        return $this->repository->roleOptions();
    }

    public function companyOptions(): array
    {
        return $this->repository->companyOptions();
    }

    public function branchOptions(): array
    {
        return $this->repository->branchOptions();
    }

    public function departmentOptions(): array
    {
        return $this->repository->departmentOptions();
    }

    public function positionOptions(): array
    {
        return $this->repository->positionOptions();
    }

    public function managerOptions(?int $excludeId = null): array
    {
        return $this->repository->managerOptions($excludeId);
    }

    private function normalize(array $data, bool $isCreate, ?int $userId = null, array $currentUser = []): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $username = strtolower(trim((string) ($data['username'] ?? '')));
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $status = strtolower(trim((string) ($data['status'] ?? ($isCreate ? 'draft' : ($currentUser['status'] ?? 'draft')))));
        $roleId = $this->normalizeOptionalInt($data['role_id'] ?? null);
        $managerId = $this->normalizeOptionalInt($data['manager_id'] ?? null);
        $joinedAt = $this->nullableDateString($data['joined_at'] ?? null);
        $terminatedAt = $this->nullableDateString($data['terminated_at'] ?? null);
        $language = trim((string) ($data['language'] ?? 'vi')) ?: 'vi';
        $timezone = trim((string) ($data['timezone'] ?? 'Asia/Saigon')) ?: 'Asia/Saigon';
        $theme = trim((string) ($data['theme'] ?? 'light')) ?: 'light';
        $email = $this->nullableString($data['email'] ?? null);
        $phone = $this->nullableString($data['phone'] ?? null);

        $errors = [];
        if ($code === '') {
            $errors['code'][] = 'Vui lòng nhập mã người dùng.';
        }
        if ($username === '') {
            $errors['username'][] = 'Vui lòng nhập tên đăng nhập.';
        }
        if ($fullName === '') {
            $errors['full_name'][] = 'Vui lòng nhập họ tên.';
        }
        if ($isCreate && $password === '') {
            $errors['password'][] = 'Vui lòng nhập mật khẩu.';
        }
        if ($password !== '' && strlen($password) < 6) {
            $errors['password'][] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }
        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'][] = 'Trạng thái không hợp lệ.';
        }
        if ($roleId === null || !$this->repository->roleExists($roleId)) {
            $errors['role_id'][] = 'Vui lòng chọn vai trò hợp lệ.';
        }
        if ($managerId !== null) {
            if ($userId !== null && $managerId === $userId) {
                $errors['manager_id'][] = 'Người quản lý không thể là chính tài khoản này.';
            } elseif (!$this->repository->activeUserExists($managerId)) {
                $errors['manager_id'][] = 'Người quản lý được chọn không tồn tại.';
            }
        }
        if ($terminatedAt !== null && $joinedAt !== null && $terminatedAt < $joinedAt) {
            $errors['terminated_at'][] = 'Ngày nghỉ việc phải sau hoặc bằng ngày vào làm.';
        }
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Email không hợp lệ.';
        }

        if ($errors !== []) {
            throw new HttpException('Dữ liệu người dùng không hợp lệ.', 422, ['errors' => $errors]);
        }

        $payload = [
            'code' => $code,
            'username' => $username,
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'avatar_url' => $this->nullableString($data['avatar_url'] ?? null),
            'company_id' => $this->normalizeOptionalInt($data['company_id'] ?? null),
            'branch_id' => $this->normalizeOptionalInt($data['branch_id'] ?? null),
            'department_id' => $this->normalizeOptionalInt($data['department_id'] ?? null),
            'position_id' => $this->normalizeOptionalInt($data['position_id'] ?? null),
            'manager_id' => $managerId,
            'role_id' => $roleId,
            'status' => $status,
            'is_active' => in_array($status, ['active', 'draft'], true) ? 1 : 0,
            'is_verified' => (string) ($data['is_verified'] ?? '0') === '1' ? 1 : 0,
            'joined_at' => $joinedAt,
            'terminated_at' => $terminatedAt,
            'language' => $language,
            'timezone' => $timezone,
            'theme' => $theme,
            'email_verified_at' => (string) ($data['is_verified'] ?? '0') === '1'
                ? ($currentUser['email_verified_at'] ?? $this->timestamp())
                : null,
            'note' => $this->nullableString($data['note'] ?? null),
            'meta_json' => $this->normalizeMeta($data['meta_json'] ?? null),
            'updated_at' => $this->timestamp(),
            'updated_by' => $this->actorId(),
        ];

        if ($password !== '') {
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($isCreate) {
            $payload['created_at'] = $this->timestamp();
            $payload['created_by'] = $this->actorId();
            $payload['failed_login_count'] = 0;
            $payload['locked_until'] = null;
            $payload['last_login_at'] = null;
            if (!isset($payload['password_hash'])) {
                $payload['password_hash'] = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            }
        }

        return $payload;
    }

    private function normalizeFilters(array $filters): array
    {
        $status = strtolower(trim((string) ($filters['status'] ?? '')));

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => in_array($status, self::STATUSES, true) ? $status : '',
            'role_id' => $this->normalizeOptionalInt($filters['role_id'] ?? null),
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
            throw new HttpException('Mã người dùng đã tồn tại.', 422, ['errors' => ['code' => ['Mã người dùng đã tồn tại.']]]);
        }
    }

    private function assertUniqueUsername(string $username): void
    {
        if ($this->repository->findByUsername($username) !== null) {
            throw new HttpException('Tên đăng nhập đã tồn tại.', 422, ['errors' => ['username' => ['Tên đăng nhập đã tồn tại.']]]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function nullableDateString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        $parsed = (int) $value;
        return $parsed > 0 ? $parsed : null;
    }

    private function normalizeMeta(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }

        json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException('meta_json phải là JSON hợp lệ.', 422, ['errors' => ['meta_json' => ['meta_json phải là JSON hợp lệ.']]]);
        }

        return $value;
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
