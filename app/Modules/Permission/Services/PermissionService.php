<?php

declare(strict_types=1);

namespace App\Modules\Permission\Services;

use App\Core\Exceptions\HttpException;
use App\Modules\Permission\Repositories\PermissionRepository;
use App\Modules\Role\Repositories\RoleRepository;

final class PermissionService
{
    public const MODULES = ['customer', 'supplier', 'quotation', 'material', 'component', 'bom', 'purchase_order', 'po', 'sales_order', 'stock', 'production', 'service_order', 'payment', 'user'];
    public const ACTIONS = ['view', 'create', 'update', 'delete', 'approve', 'confirm', 'deliver', 'cancel_delivery', 'assign', 'issue', 'start', 'complete', 'submit', 'reject', 'cancel', 'receive', 'receive_partial', 'receive_full', 'add_extra_cost', 'submit_stock_in', 'stock_in_approve', 'close', 'view_log'];
    private const MODULE_ACTIONS = [
        'customer' => ['view', 'create', 'update', 'delete'],
        'supplier' => ['view', 'create', 'update', 'delete'],
        'quotation' => ['view', 'create', 'update', 'delete', 'submit', 'approve', 'reject', 'cancel', 'view_log'],
        'material' => ['view', 'create', 'update', 'delete', 'approve'],
        'component' => ['view', 'create', 'update', 'delete'],
        'bom' => ['view', 'create', 'update', 'delete', 'approve'],
        'purchase_order' => ['view', 'create', 'update', 'delete', 'approve'],
        'po' => ['view', 'create', 'update', 'submit', 'approve', 'reject', 'cancel', 'receive', 'receive_partial', 'receive_full', 'add_extra_cost', 'submit_stock_in', 'stock_in_approve', 'close', 'view_log'],
        'sales_order' => ['view', 'create', 'update', 'delete', 'approve', 'confirm', 'deliver', 'cancel_delivery', 'view_log'],
        'stock' => ['view', 'create', 'update', 'delete', 'approve'],
        'production' => ['view', 'create', 'update', 'delete', 'approve', 'assign', 'issue', 'start', 'complete', 'view_log'],
        'service_order' => ['view', 'create', 'update', 'assign', 'start', 'complete', 'cancel', 'view_log'],
        'payment' => ['view', 'create', 'confirm'],
        'user' => ['view', 'create', 'update', 'delete', 'approve'],
    ];

    public function __construct(
        private readonly PermissionRepository $repository,
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public function ensureDefaults(): void
    {
        foreach (self::MODULE_ACTIONS as $module => $actions) {
            foreach ($actions as $action) {
                if ($this->repository->findByModuleAction($module, $action) === null) {
                    $this->repository->create([
                        'module' => $module,
                        'action' => $action,
                    ]);
                }
            }
        }
    }

    public function matrixForRole(int $roleId): array
    {
        $role = $this->roleRepository->findById($roleId);
        if ($role === null) {
            throw new HttpException('Không tìm thấy vai trò.', 404);
        }

        $this->ensureDefaults();
        $permissions = $this->repository->all();
        $assignedIds = $this->repository->rolePermissionIds($roleId);
        $map = [];

        foreach ($permissions as $permission) {
            $key = (string) $permission['module'];
            if (!isset($map[$key])) {
                $map[$key] = [];
            }

            $map[$key][(string) $permission['action']] = [
                'id' => (int) $permission['id'],
                'checked' => in_array((int) $permission['id'], $assignedIds, true),
            ];
        }

        return [
            'role' => $role,
            'modules' => self::MODULES,
            'actions' => self::ACTIONS,
            'matrix' => $map,
        ];
    }

    public function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->roleRepository->findById($roleId);
        if ($role === null) {
            throw new HttpException('Không tìm thấy vai trò.', 404);
        }

        $this->ensureDefaults();
        $validIds = array_map(static fn (array $row): int => (int) $row['id'], $this->repository->all());
        $permissionIds = array_values(array_unique(array_filter(array_map(static fn (mixed $value): int => (int) $value, $permissionIds), static fn (int $value): bool => in_array($value, $validIds, true))));
        $this->repository->syncRolePermissions($roleId, $permissionIds);
    }
}
