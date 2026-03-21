<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Validation\Validator;

abstract class Controller
{
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function view(string $viewPath, array $data = [], int $status = 200): Response
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require base_path($viewPath);
        $content = (string) ob_get_clean();

        return Response::html($content, $status);
    }

    protected function redirect(string $location, int $status = 302): Response
    {
        return Response::redirect($location, $status);
    }

    protected function validate(array $input, array $rules): array
    {
        return Validator::validate($input, $rules);
    }
}
