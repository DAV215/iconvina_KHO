<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Exceptions\HttpException;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Modules\Auth\Services\AuthService;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service)
    {
    }

    public function showLogin(Request $request)
    {
        unset($request);

        if (auth_user() !== null) {
            return $this->redirect(app_url('/'));
        }

        return $this->view('app/Modules/Auth/Views/login.php', [
            'pageTitle' => 'Đăng nhập',
        ]);
    }

    public function login(Request $request)
    {
        $input = $request->all();

        try {
            $user = $this->service->attempt((string) ($input['username'] ?? ''), (string) ($input['password'] ?? ''));
            $this->service->login($user);
            session_flash('success', 'Đăng nhập thành công.');

            return $this->redirect(app_url('/'));
        } catch (HttpException $exception) {
            return $this->view('app/Modules/Auth/Views/login.php', [
                'pageTitle' => 'Đăng nhập',
                'old' => $input,
                'errors' => $exception->context()['errors'] ?? [],
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        unset($request);

        $this->service->logout();
        session_flash('success', 'Đã đăng xuất.');

        return $this->redirect(app_url('/login'));
    }

    public function index(Request $request): array
    {
        unset($request);

        return [
            'module' => 'Auth',
            'login_url' => app_url('/login'),
            'logout_url' => app_url('/logout'),
        ];
    }
}
