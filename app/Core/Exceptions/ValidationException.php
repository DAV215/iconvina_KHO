<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class ValidationException extends HttpException
{
    public function __construct(array $errors)
    {
        parent::__construct('The given data is invalid.', 422, [
            'errors' => $errors,
        ]);
    }
}
