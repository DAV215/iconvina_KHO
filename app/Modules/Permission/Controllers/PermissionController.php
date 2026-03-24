<?php

declare(strict_types=1);

namespace App\Modules\Permission\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Permission\Services\PermissionService;

final class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $service)
    {
    }

    public function edit(Request $request)
    {
        $this->authorize('role.view');
        $roleId = (int) $request->query('id', 0);
        $payload = $this->service->matrixForRole($roleId);

        return $this->view('app/Modules/Role/Views/permissions.php', [
            'pageTitle' => 'Phân quyền',
            'activeSidebar' => 'permissions',
            'role' => $payload['role'],
            'modules' => $payload['modules'],
            'actions' => $payload['actions'],
            'matrix' => $payload['matrix'],
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('role.update');
        $roleId = (int) $request->query('id', 0);

        try {
            $permissionIds = $request->input('permission_ids', []);
            $this->service->syncRolePermissions($roleId, is_array($permissionIds) ? $permissionIds : []);
            session_flash('success', 'Cập nhật phân quyền thành công.');

            return $this->redirect(app_url('/roles/permissions?id=' . $roleId));
        } catch (HttpException $exception) {
            $payload = $this->service->matrixForRole($roleId);

            return $this->view('app/Modules/Role/Views/permissions.php', [
                'pageTitle' => 'Phân quyền',
                'activeSidebar' => 'permissions',
                'role' => $payload['role'],
                'modules' => $payload['modules'],
                'actions' => $payload['actions'],
                'matrix' => $payload['matrix'],
                'errors' => $exception->context()['errors'] ?? [],
            ], 422);
        }
    }
}
