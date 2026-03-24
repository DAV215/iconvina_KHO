<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Exceptions\HttpException;
use App\Core\Validation\Validator;

abstract class Controller
{
    protected function paginationParams(Request $request, int $defaultPerPage = 25, array $allowedPerPage = [10, 25, 50, 100]): array
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', $defaultPerPage);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = $defaultPerPage;
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function view(string $viewPath, array $data = [], int $status = 200): Response
    {
        $responseStatus = $status;
        extract($data, EXTR_SKIP);

        ob_start();
        require base_path($viewPath);
        $content = (string) ob_get_clean();

        return Response::html($content, $responseStatus);
    }

    protected function redirect(string $location, int $status = 302): Response
    {
        return Response::redirect($location, $status);
    }

    protected function validate(array $input, array $rules): array
    {
        return Validator::validate($input, $rules);
    }

    protected function authorize(string $permission): void
    {
        require_permission($permission);
    }
}
